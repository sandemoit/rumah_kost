// datepicker
$(function() {
    $.datepicker.regional['id'] = {
        closeText: 'Tutup',
        prevText: 'Sebelumnya',
        nextText: 'Selanjutnya',
        currentText: 'Hari Ini',
        monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
        monthNamesShort: ['Jan', 'Peb', 'Mar', 'Apr', 'Mei', 'Jun',
            'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
        dayNames: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
        dayNamesShort: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
        dayNamesMin: ['Mg', 'Sn', 'Sl', 'Rb', 'Km', 'jm', 'Sb'],
        weekHeader: 'Mg',
        dateFormat: 'dd/mm/yy',
        firstDay: 0,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: ''
    };
    $.datepicker.setDefaults($.datepicker.regional['id']);
    $(".datepicker").datepicker({
        showOn: "both",
        buttonImage: "images/calendar.gif",
        buttonImageOnly: true,
        buttonText: "Tampilkan kalender"
    });
});
