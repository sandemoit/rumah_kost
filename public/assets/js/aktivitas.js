document.addEventListener('DOMContentLoaded', function() {
    
    async function drawPage() {
        let endpount = $('#endpoint').val();
        let postData = {
            '_token': $('meta[name="csrf-token"]').attr('content'),
            'date': $('#lap_tgl_activity').val(),
            'book': $('#selectReportActivity').val(),
        }
        
        response = await fetch(`${endpount}/api/aktivitas/harian`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(postData),
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            $('.tab-content').html(data.html);
        });
    }

    drawPage();
    
    $('#lap_tgl_activity').datepicker({
        dateFormat: 'dd-mm-yy', // Set the date format
        onSelect: function(dateText) {
            console.log("Tanggal dipilih: ", dateText); // Log tanggal yang dipilih
            // Update the input value with the selected date
            $(this).val(dateText);
            // Trigger the onchange event
            drawPage();
        }
    });

    $('#selectReportActivity').on('change', function() {
        drawPage();
    });
});