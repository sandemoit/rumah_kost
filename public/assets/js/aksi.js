document.addEventListener('click', function(event) {
    // Jika user melakukan klik di luar action-toggle, sembunyikan action-toggle
    const actionToggle = document.querySelector('.action-toggle.active');
    if (actionToggle && !actionToggle.contains(event.target)) {
        actionToggle.classList.remove('active');
    }
});

function toggleActions(id) {
    var actionButtons = document.querySelector('#action-buttons-' + id).parentNode;

    // Toggle kelas 'active' pada action-buttons
    actionButtons.classList.toggle('active');

    // Ambil semua action-toggle yang aktif kecuali yang sedang diklik
    var activeToggles = document.querySelectorAll('.action-toggle.active');
    activeToggles.forEach(function(toggle) {
        if (toggle !== actionButtons) {
            toggle.classList.remove('active');
        }
    });
}