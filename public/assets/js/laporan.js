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
        }, function(e, t) {
            $("#loader_" + a).fadeOut(100);
            var n = e.split("|||");
            $("#divsub_" + a).html(n[1]),
            $("#trsub_" + a).fadeIn()
        })
    }
}