// File: js/notifications.js
$(document).ready(function() {
    // Mark notification as read when clicked
    $('.notification-item').on('click', function(e) {
        e.preventDefault();
        var notificationId = $(this).data('notification-id');
        var link = $(this).attr('href');
        
        $.post('/mark_notification_read.php', { notification_id: notificationId }, function(response) {
            if (response.success) {
                window.location.href = link;
            }
        });
    });

    // WebSocket connection for real-time notifications
    var socket = new WebSocket('ws://your-websocket-server-url');

    socket.onmessage = function(event) {
        var data = JSON.parse(event.data);
        if (data.type === 'notification' && data.user_id == <?php echo $_SESSION['user_id'] ?? 'null'; ?>) {
            // Add new notification to the dropdown
            var newNotification = '<li><a class="dropdown-item notification-item" href="' + data.link + '" data-notification-id="' + data.id + '">' +
                                  data.message +
                                  '<small class="text-muted d-block">Just now</small></a></li>';
            $('#notificationsDropdown').next('.dropdown-menu').prepend(newNotification);
            
            // Update notification count
            var $badge = $('#notificationsDropdown .badge');
            var count = parseInt($badge.text()) || 0;
            $badge.text(count + 1).show();
        }
    };
});