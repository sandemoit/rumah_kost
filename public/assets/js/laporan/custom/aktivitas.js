function slideOpen(a) {
    $("#trsub_" + a).fadeToggle(300),
    $("#divsub_" + a).slideToggle(300)
}

function slidedetail(a) {
    if ($.trim($("#divsub_" + a).html()).length)
        $("#trsub_" + a).fadeToggle(300);
    else {
        $("#loader_" + a).fadeIn(100);
        var e = $("#detail_report_book").val()
            , t = $("#detail_from").val()
            , n = $("#detail_to").val();
        $.post("laporan/laporan-kirim.php", {
            report_book: e,
            detail_from: t,
            detail_to: n,
            cat_id: a,
            report_detail: global_var
        }, function (e, t) {
            $("#loader_" + a).fadeOut(100);
            var n = e.split("|||");
            $("#divsub_" + a).html(n[1]),
                $("#trsub_" + a).fadeIn()
        })
    }
}

document.addEventListener('DOMContentLoaded', function() {
    
    async function drawPage() {
        let endpount = $('#endpoint').val();
        let postData = {
            '_token': $('meta[name="csrf-token"]').attr('content'),
            'date': $('#daterange').val(),
            'book': $('#selectReportActivity').val(),
        }
        
        response = await fetch(`${endpount}/api/aktivitas/custom`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(postData),
        })
        .then(response => response.json())
        .then(data => {
            $('.tab-content').html(data.html);
        });
    }

    drawPage();
    $('#daterange').daterangepicker();
    $('#daterange').on('apply.daterangepicker', function(ev, picker) {
        //do something, like clearing an input
        drawPage();
    });
    
    $('#selectReportActivity').on('change', function() {
        drawPage();
    });
});