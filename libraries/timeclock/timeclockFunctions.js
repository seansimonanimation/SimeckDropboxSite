
function clockArtistOut(shiftId) {
    fetch('libraries/timeclock/clockout.php?shift_id=' + shiftId)
        .then(response => response.json())
        .then(data => {
            if (data.success) location.reload();
        });
}

$(document).ready(function () {
    var table = $('#ShiftList').DataTable({
        "paging": false,
        "info": false,
        "searching": true,
        "order": [[0, "asc"]]
    });

    // Link the custom "Filter by artist" input to the User column
    $('#artistFilter').on('keyup', function () {
        table.search(this.value).draw();
    });
});

// Inline editing for time_in / time_out cells
$(document).on('click', '.atc-editable', function(){
    var $td = $(this);
    if($td.hasClass('editing')) return;
    $td.addClass('editing');
    
    var $display = $td.find('.atc-display');
    var currentText = $display.text().trim();
    
    // Convert MySQL datetime to datetime-local format
    var inputValue = currentText.replace(' ', 'T');
    if(!inputValue) inputValue = '';
    
    $display.hide();
    $td.append('<input type="datetime-local" class="atc-input" value="' + inputValue + '" step="1">');
    
    var $input = $td.find('.atc-input');
    $input.focus().select();
    
    var originalValue = currentText;
    
    function saveValue(){
        var newVal = $input.val();
        $input.remove();
        $display.show();
        
        if(newVal === ''){
            $display.text('');
            $td.removeClass('editing');
            return;
        }
        
        // Convert datetime-local back to MySQL format
        var mysqlVal = newVal.replace('T', ' ');
        
        // Show saving indicator
        $display.text('Saving...');
        
        $.post('libraries/timeclock/updateShiftField.php', {
            shift_id: $td.data('shift-id'),
            field: $td.data('field'),
            value: mysqlVal
        }, function(data){
            if(data.success){
                $display.text(mysqlVal);
            } else {
                $display.text(originalValue || '');
                alert('Failed to save: ' + (data.error || 'unknown error'));
            }
            $td.removeClass('editing');
        }, 'json').fail(function(){
            $display.text(originalValue || '');
            alert('Failed to save. Check your connection.');
            $td.removeClass('editing');
        });
    }
    
    $input.on('change', saveValue);
    $input.on('blur', saveValue);
    $input.on('keydown', function(e){
        if(e.key === 'Enter'){
            e.preventDefault();
            saveValue();
        }
        if(e.key === 'Escape'){
            $input.remove();
            $display.show();
            $td.removeClass('editing');
        }
    });
})