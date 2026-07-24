<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/service-worker.js').catch(function(error) {
            console.error('Service Worker registration failed:', error);
        });
    });
}
</script>
