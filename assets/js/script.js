jQuery(document).ready(function($) {
    $('#attendee-search').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('.attendee-item').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    $('.sortable').click(function() {
        var table = $(this).parents('table').eq(0);
        var rows = table.find('tr:gt(0)').toArray().sort(comparer($(this).index()));
        
        // Toggle sort direction
        if (!$(this).hasClass('asc') && !$(this).hasClass('desc')) {
            $(this).addClass('asc');
        } else if ($(this).hasClass('asc')) {
            $(this).removeClass('asc').addClass('desc');
            rows = rows.reverse();
        } else {
            $(this).removeClass('desc').addClass('asc');
        }
        
        // Remove classes from other headers
        $(this).siblings().removeClass('asc desc');
        
        for (var i = 0; i < rows.length; i++) {
            table.append(rows[i]);
        }
    });

    function comparer(index) {
        return function(a, b) {
            var valA = getCellValue(a, index);
            var valB = getCellValue(b, index);
            return $.isNumeric(valA) && $.isNumeric(valB) ? 
                valA - valB : valA.toString().localeCompare(valB);
        }
    }

    function getCellValue(row, index) {
        return $(row).children('td').eq(index).text();
    }
});