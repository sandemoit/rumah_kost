$(function() {
    $.datepicker.regional['id'] = {
        closeText: "Tutup",
        prevText: "Mundur",
        nextText: "Maju",
        currentText: "hari ini",
        monthNames: [ "Januari","Pebruari","Maret","April","Mei","Juni",
        "Juli","Agustus","September","Oktober","November","Desember" ],
        monthNamesShort: [ "Jan","Peb","Mar","Apr","Mei","Jun",
        "Jul","Agus","Sep","Okt","Nov","Des" ],
        dayNames: [ "Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu" ],
        dayNamesShort: [ "Min","Sen","Sel","Rab","kam","Jum","Sab" ],
        dayNamesMin: [ "Mg","Sn","Sl","Rb","Km","jm","Sb" ],
        weekHeader: "Mg",
        dateFormat: "d MM yy",
        firstDay: 0,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: ""
    };
    $.datepicker.setDefaults($.datepicker.regional['id']);
    $(".datepicker").datepicker();
});

