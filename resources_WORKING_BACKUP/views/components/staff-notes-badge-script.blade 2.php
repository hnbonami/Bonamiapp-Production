<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('/api/staff-notes/unread-count', { credentials: 'same-origin' })
        .then(r => r.json())
        .then(data => {
            if (data.unread > 0) {
                document.getElementById('staff-notes-badge').style.display = '';
                document.getElementById('staff-notes-badge').textContent = 'Nieuw';
            }
        });
    // Optioneel: badge verbergen bij bezoek
    if (window.location.pathname.startsWith('/staff-notes')) {
        fetch('/staff-notes/mark-all-read', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
        document.getElementById('staff-notes-badge').style.display = 'none';
    }
});
</script>
