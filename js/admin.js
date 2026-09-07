jQuery(document).ready(function($) {
	'use strict';

	/* -------------------------------------------------------
	   Tab switching (preserved from original)
	------------------------------------------------------- */
	// Restore the last active tab from localStorage
	var activeTab = localStorage.getItem('so_qmp_active_tab');
	if (activeTab) {
		$('.nav-tab').removeClass('nav-tab-active');
		$('.so_qmp-tab-content').hide();
		$('a[href="' + activeTab + '"]').addClass('nav-tab-active');
		$(activeTab).show();
	} else {
		$('.nav-tab').first().click();
	}

	// Handle tab clicks
	$('.nav-tab').click(function(e) {
		e.preventDefault();
		$('.nav-tab').removeClass('nav-tab-active');
		$(this).addClass('nav-tab-active');
		$('.so_qmp-tab-content').hide();
		$($(this).attr('href')).show();
		localStorage.setItem('so_qmp_active_tab', $(this).attr('href'));
	});

	/* -------------------------------------------------------
	   AJAX page picker
	------------------------------------------------------- */
	var pickerUID = 1000; // Counter for dynamically created pickers
	var debounceTimer = null;

	function initPicker($picker) {
		var $search = $picker.find('.so_qmp-page-search');
		var $hidden = $picker.find('input[type="hidden"]');
		var $results = $picker.find('.so_qmp-page-results');
		var scope = $picker.data('scope');
		var folderID = $picker.data('folder-id') || 0;
		var selectedID = $hidden.val();
		var lastQuery = '';

		// Focus: show results or fetch if empty
		$search.on('focus', function() {
			if ($results.children().length === 0) {
				fetchPages('', $results, scope, folderID);
			} else {
				$results.attr('hidden', false);
			}
		});

		// Input: debounced search
		$search.on('input', function() {
			var query = $(this).val();
			clearTimeout(debounceTimer);
			debounceTimer = setTimeout(function() {
				fetchPages(query, $results, scope, folderID);
			}, 300);
		});

		// Click result: select page
		$results.on('click', 'li[data-id]', function() {
			var pageID = $(this).data('id');
			var pageTitle = $(this).text();
			$hidden.val(pageID);
			$search.val(pageTitle);
			$results.attr('hidden', true);
			$search.blur();
		});

		// Escape: close results
		$search.on('keydown', function(e) {
			if (e.key === 'Escape') {
				$results.attr('hidden', true);
			}
		});

		// Click outside: close results
		$(document).on('click', function(e) {
			if (!$(e.target).closest($picker).length) {
				$results.attr('hidden', true);
			}
		});

		// Arrow keys: navigate results
		$search.on('keydown', function(e) {
			var $items = $results.find('li[data-id]');
			var $current = $results.find('li[aria-selected="true"]');
			var currentIndex = $items.index($current);

			if (e.key === 'ArrowDown') {
				e.preventDefault();
				if (currentIndex < $items.length - 1) {
					$current.attr('aria-selected', 'false');
					$items.eq(currentIndex + 1).attr('aria-selected', 'true');
				}
			} else if (e.key === 'ArrowUp') {
				e.preventDefault();
				if (currentIndex > 0) {
					$current.attr('aria-selected', 'false');
					$items.eq(currentIndex - 1).attr('aria-selected', 'true');
				}
			} else if (e.key === 'Enter') {
				e.preventDefault();
				if ($current.length) {
					$current.click();
				}
			}
		});
	}

	function fetchPages(query, $results, scope, folderID) {
		var url, isCustomEndpoint;

		if (scope === 'secondary' && folderID) {
			url = so_qmp_vars.secondary_pages_url + '?ancestor=' + encodeURIComponent(folderID);
			if (query) {
				url += '&search=' + encodeURIComponent(query);
			}
			isCustomEndpoint = true;
		} else {
			url = so_qmp_vars.rest_pages_url + '?status=publish&per_page=20&orderby=title&order=asc';
			if (query) {
				url += '&search=' + encodeURIComponent(query);
			}
			isCustomEndpoint = false;
		}

		wp.apiFetch({
			url: url,
			method: 'GET',
			headers: {
				'X-WP-Nonce': so_qmp_vars.nonce
			}
		}).then(function(pages) {
			$results.empty();
			if (pages.length === 0) {
				$results.append('<li class="so_qmp-no-results">' + so_qmp_vars.no_results + '</li>');
			} else {
				pages.forEach(function(page) {
					var title = isCustomEndpoint
						? $('<div>').text(page.title).html()
						: $('<div>').text(page.title.rendered).html();
					$results.append('<li role="option" data-id="' + page.id + '">' + title + '</li>');
				});
			}
			$results.attr('hidden', false);
		}).catch(function(error) {
			console.error('Page fetch error:', error);
			$results.append('<li class="so_qmp-no-results">' + so_qmp_vars.no_results + '</li>');
			$results.attr('hidden', false);
		});
	}

	// Initialize all existing pickers
	$('.so_qmp-page-picker').each(function() {
		initPicker($(this));
	});

	/* -------------------------------------------------------
	   Add mapping row
	------------------------------------------------------- */
	$('#so_qmp-add-mapping').on('click', function() {
		var $table = $('#page-translations-table tbody');
		var $template = $('#so_qmp-row-template');
		var $countInput = $('#so_qmp-row-count');
		var currentCount = parseInt($countInput.val(), 10);
		var newIndex = currentCount + 1;
		var maxMappings = parseInt(so_qmp_vars.max_mappings, 10);

		// Clone template
		var $newRow = $template.clone();
		$newRow.attr('id', '');
		$newRow.attr('data-row', newIndex);
		$newRow.removeAttr('hidden aria-hidden');

		// Update row number cell
		$newRow.find('td:first').text(newIndex);

		// Update name attributes
		$newRow.find('input[name*="so_qmp_page_mapping_0"]').each(function() {
			var oldName = $(this).attr('name');
			var newName = oldName.replace('so_qmp_page_mapping_0', 'so_qmp_page_mapping_' + newIndex);
			$(this).attr('name', newName);
		});

		// Generate unique IDs for results lists
		$newRow.find('.so_qmp-page-results').each(function() {
			var newUID = pickerUID++;
			$(this).attr('id', 'so_qmp-results-' + newUID);
			$(this).prev('.so_qmp-page-search').attr('aria-controls', 'so_qmp-results-' + newUID);
		});

		// Insert before template
		$newRow.insertBefore($template);

		// Initialize pickers in new row
		$newRow.find('.so_qmp-page-picker').each(function() {
			initPicker($(this));
		});

		// Update count
		$countInput.val(newIndex);

		// Disable Add button and show CTA if at limit
		if (newIndex >= maxMappings) {
			$('#so_qmp-add-mapping').prop('disabled', true);
			$('#so_qmp-add-cta').removeAttr('hidden');
		}
	});

	/* -------------------------------------------------------
	   Remove mapping row
	------------------------------------------------------- */
	$(document).on('click', '.so_qmp-remove-row', function() {
		var $row = $(this).closest('tr');
		var $countInput = $('#so_qmp-row-count');
		var maxMappings = parseInt(so_qmp_vars.max_mappings, 10);

		// Remove row
		$row.remove();

		// Re-number all remaining rows
		var newIndex = 1;
		$('#page-translations-table tbody .page-mapping-row').not('#so_qmp-row-template').each(function() {
			var $thisRow = $(this);
			$thisRow.attr('data-row', newIndex);
			$thisRow.find('td:first').text(newIndex);

			// Update name attributes
			$thisRow.find('input[type="hidden"]').each(function() {
				var oldName = $(this).attr('name');
				var newName = oldName.replace(/so_qmp_page_mapping_\d+/, 'so_qmp_page_mapping_' + newIndex);
				$(this).attr('name', newName);
			});

			newIndex++;
		});

		// Update count
		var newCount = newIndex - 1;
		$countInput.val(newCount);

		// Enable Add button and hide CTA if below limit
		if (newCount < maxMappings) {
			$('#so_qmp-add-mapping').prop('disabled', false);
			$('#so_qmp-add-cta').attr('hidden', true);
		}
	});

	/* -------------------------------------------------------
	   Language switcher (Premium — conditional)
	------------------------------------------------------- */
	$('#so_qmp-lang-switcher-select').on('change', function() {
		var newIndex = parseInt($(this).val(), 10);
		if (!window.so_qmp_lang_data || !so_qmp_lang_data[newIndex]) {
			return;
		}

		var langData = so_qmp_lang_data[newIndex];
		var folderID = langData.folder_id;
		var langName = langData.name;
		var rows = langData.rows || {};

		// Update column header
		$('#so_qmp-secondary-col-header').text(langName);

		// Update each mapping row
		$('#page-translations-table tbody .page-mapping-row').not('#so_qmp-row-template').each(function() {
			var rowIndex = parseInt($(this).attr('data-row'), 10);
			var $secondaryPicker = $(this).find('.so_qmp-page-picker[data-scope="secondary"]');

			// Update folder ID
			$secondaryPicker.attr('data-folder-id', folderID);

			// Update hidden input name
			var $hidden = $secondaryPicker.find('input[type="hidden"]');
			var keyName = (newIndex === 2) ? 'secondary' : ('lang_' + newIndex);
			var newName = 'so_qmp_page_mapping_' + rowIndex + '[' + keyName + ']';
			$hidden.attr('name', newName);

			// Update value from pre-loaded data
			var pageID = rows[rowIndex] || 0;
			$hidden.val(pageID);

			// Update text input (would need a title lookup for full implementation)
			var $search = $secondaryPicker.find('.so_qmp-page-search');
			if (pageID > 0) {
				$search.val('[Page ' + pageID + ']'); // Placeholder; Premium would pre-load titles
			} else {
				$search.val('');
			}
		});
	});
});
