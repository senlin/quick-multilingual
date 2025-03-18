(function($) {
    'use strict';

    $(document).ready(function() {
        // Tab functionality
        $('.nav-tab').on('click', function(e) {
            e.preventDefault();
            
            // Hide all tabs
            $('.so_qmp-tab-content').hide();
            
            // Remove active class from all tabs
            $('.nav-tab').removeClass('nav-tab-active');
            
            // Add active class to clicked tab
            $(this).addClass('nav-tab-active');
            
            // Show the corresponding tab content
            $($(this).attr('href')).show();
        });
        
        // Show the first tab by default
        $('.nav-tab:first').click();
        
        // Handle dynamic rows for page mappings
        $('#so_qmp_number_of_pages').on('change', function() {
            var numberOfPages = $(this).val();
            
            // Disable the form submission if the number is not valid
            if (numberOfPages < 1 || numberOfPages > 4) {
                $('#page-translations').find('input[type="submit"]').prop('disabled', true);
                return;
            } else {
                $('#page-translations').find('input[type="submit"]').prop('disabled', false);
            }
            
            // Show or hide rows based on the selected number
            $('.page-mapping-row').each(function(index) {
                if (index < numberOfPages) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
        
        // Trigger change event to initialize the rows visibility
        $('#so_qmp_number_of_pages').trigger('change');
    });
})(jQuery);