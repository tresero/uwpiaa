jQuery(document).ready(function($) {
    // Search functionality
    let searchTimeout = null;
    $('#attendee-search').on('input', function() {
        clearTimeout(searchTimeout);
        const $input = $(this);
        const searchTerm = $input.val();
        
        // Only search if empty or 3+ characters
        if (searchTerm.length === 0 || searchTerm.length >= 3) {
            searchTimeout = setTimeout(function() {
                window.location.href = updateUrlParams({
                    search: searchTerm || null,
                    aidloom_page: '1'
                });
            }, 500);
        }
    });

    // Sorting functionality
    $('.sortable').click(function() {
        const column = $(this).data('sort');
        const currentSort = new URLSearchParams(window.location.search).get('sort');
        const currentOrder = new URLSearchParams(window.location.search).get('order');
        
        let direction = 'asc';
        if (column === currentSort) {
            direction = currentOrder === 'asc' ? 'desc' : 'asc';
        }

        window.location.href = updateUrlParams({
            sort: column,
            order: direction,
            aidloom_page: '1'
        });
    });

    // Helper function to update URL parameters
    function updateUrlParams(updates) {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Update or remove parameters
        Object.entries(updates).forEach(([key, value]) => {
            if (value === null) {
                urlParams.delete(key);
            } else {
                urlParams.set(key, value);
            }
        });

        return `${window.location.pathname}?${urlParams.toString()}`;
    }

    // Set initial search value from URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('search')) {
        $('#attendee-search').val(urlParams.get('search'));
    }
});