jQuery(document).ready(function($) {
	// Restore the last active tab from localStorage
	var activeTab = localStorage.getItem('so_qmp_active_tab');
	if (activeTab) {
		$('.nav-tab').removeClass('nav-tab-active');
		$('.so_qmp-tab-content').hide();
		$('a[href="' + activeTab + '"]').addClass('nav-tab-active');
		$(activeTab).show();
	} else {
		$('.nav-tab').first().click(); // Activate the first tab by default if no active tab is found.
	}

	// Handle tab clicks
	$('.nav-tab').click(function(e) {
		e.preventDefault();
		$('.nav-tab').removeClass('nav-tab-active');
		$(this).addClass('nav-tab-active');
		$('.so_qmp-tab-content').hide();
		$($(this).attr('href')).show();
		// Save the active tab in localStorage
		localStorage.setItem('so_qmp_active_tab', $(this).attr('href'));
	});

	// Handle change in number of pages
	$('#so_qmp_number_of_pages').change(function() {
		var numPages = parseInt($(this).val(), 10);
		var $table = $('#page-translations-table');
		var $rows = $table.find('.page-mapping-row');

		// Remove extra rows
		$rows.slice(numPages).remove();

		// Add new rows if necessary
		for (var i = $rows.length; i < numPages; i++) {
			$table.append(`
				<tr valign="top" class="page-mapping-row">
					<td>Page ${i + 1}</td>
					<td>
						<select name="so_qmp_page_mapping_${i + 1}[primary]">
							<option value="0">— Select —</option>
						</select>
					</td>
					<td>
						<select name="so_qmp_page_mapping_${i + 1}[secondary]">
							<option value="0">— Select —</option>
						</select>
					</td>
				</tr>
			`);
		}
	});
});
