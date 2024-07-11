// File: js/main.js

document.addEventListener('DOMContentLoaded', function() {
    // Translation support
    const translations = JSON.parse(document.getElementById('translations').textContent);

    window._ = function(key) {
        return translations[key] || key;
    };

    // Notification handling
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationItems = document.querySelectorAll('.notification-item');

    if (notificationDropdown) {
        notificationDropdown.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.nextElementSibling.classList.toggle('show');
        });

        document.addEventListener('click', function(e) {
            if (!notificationDropdown.contains(e.target)) {
                notificationDropdown.nextElementSibling.classList.remove('show');
            }
        });
    }

    notificationItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const notificationId = this.dataset.notificationId;
            markNotificationAsRead(notificationId, this.href);
        });
    });

    function markNotificationAsRead(notificationId, redirectUrl) {
        fetch('/mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'notification_id=' + notificationId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = redirectUrl;
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Tooltip initialization
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Popover initialization
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl)
    });

    // File input custom styling
    document.querySelectorAll('.custom-file-input').forEach(function(input) {
        input.addEventListener('change', function(e) {
            var fileName = e.target.files[0].name;
            var nextSibling = e.target.nextElementSibling;
            nextSibling.innerText = fileName;
        });
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

    // Back to top button
    var backToTop = document.getElementById('back-to-top');
    if (backToTop) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 100) {
                backToTop.style.display = 'block';
            } else {
                backToTop.style.display = 'none';
            }
        });

        backToTop.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({top: 0, behavior: 'smooth'});
        });
    }

    // Countdown timer for promotions or events
    function startCountdown(endDate, display) {
        var timer = setInterval(function () {
            var now = new Date().getTime();
            var distance = endDate - now;
            
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            display.textContent = days + "d " + hours + "h " + minutes + "m " + seconds + "s ";
            
            if (distance < 0) {
                clearInterval(timer);
                display.textContent = "EXPIRED";
            }
        }, 1000);
    }

    var countdownElement = document.getElementById('countdown');
    if (countdownElement) {
        var endDate = new Date(countdownElement.dataset.enddate).getTime();
        startCountdown(endDate, countdownElement);
    }

    // Lazy loading images
    if ('loading' in HTMLImageElement.prototype) {
        const images = document.querySelectorAll('img[loading="lazy"]');
        images.forEach(img => {
            img.src = img.dataset.src;
        });
    } else {
        // Fallback for browsers that don't support lazy loading
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lozad.js/1.16.0/lozad.min.js';
        script.onload = function() {
            const observer = lozad('.lozad', {
                loaded: function(el) {
                    el.classList.add('fade');
                }
            });
            observer.observe();
        }
        document.body.appendChild(script);
    }

    // WebSocket connection for real-time notifications
    const socket = new WebSocket('ws://localhost:8080');

    socket.addEventListener('open', function (event) {
        console.log('Connected to WebSocket server');
    });

    socket.addEventListener('message', function (event) {
        const data = JSON.parse(event.data);
        if (data.type === 'notification') {
            showNotification(data.message);
        }
    });

    function showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'toast';
        notification.setAttribute('role', 'alert');
        notification.setAttribute('aria-live', 'assertive');
        notification.setAttribute('aria-atomic', 'true');
        notification.innerHTML = `
            <div class="toast-header">
                <strong class="me-auto">${_('New Notification')}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `;
        document.body.appendChild(notification);
        const toast = new bootstrap.Toast(notification);
        toast.show();
    }

    // Example of using translations
    console.log(_('Welcome to Africhama'));

    // Add more custom JavaScript as needed for your application
});