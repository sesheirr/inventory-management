document.addEventListener('DOMContentLoaded', function () {
    // Poll every 15 seconds for unread count
    function fetchNotifications() {
        fetch('/api/notifications/unread-count')
            .then(res => res.json())
            .then(data => {
                const badge = document.getElementById('notifBadge');
                if (!badge && data.count > 0) {
                    // reload page to show badge if not present (simple fallback)
                    location.reload();
                    return;
                }

                if (badge) {
                    if (data.count > 0) {
                        badge.textContent = data.count > 99 ? '99+' : data.count;
                        badge.style.display = 'inline-block';
                    } else {
                        badge.remove();
                    }
                }
            }).catch(() => {});
    }

    setInterval(fetchNotifications, 15000);
});
