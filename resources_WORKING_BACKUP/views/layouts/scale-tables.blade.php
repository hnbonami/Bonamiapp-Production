<script>
// Scale down mobility tables on bikefit pages
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.href.includes('/bikefit/') && window.location.href.includes('/results')) {
        const tables = document.querySelectorAll('table');
        tables.forEach(table => {
            table.style.transform = 'scale(0.7)';
            table.style.transformOrigin = 'top left';
            table.style.fontSize = '12px';
            table.style.marginBottom = '-30%';
        });
    }
});
</script>