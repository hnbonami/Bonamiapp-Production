// Sjablonen Editor Button Functionality Enhancement

document.addEventListener('DOMContentLoaded', function() {
    
    // Enhanced Add New Page functionality
    window.addNewPage = function() {
        console.log('Adding new page...');
        
        // Get current sjabloon ID from URL
        const urlParts = window.location.pathname.split('/');
        const sjabloonId = urlParts[urlParts.indexOf('sjablonen') + 1];
        
        // Show loading state
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Toevoegen...';
        button.disabled = true;
        
        fetch(`/sjablonen/${sjabloonId}/pages`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                is_url_page: false
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Pagina wordt toegevoegd!', 'success');
                if (data.reload) {
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                }
            } else {
                showNotification('Er is een fout opgetreden', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Er is een fout opgetreden', 'error');
        })
        .finally(() => {
            // Restore button state
            button.textContent = originalText;
            button.disabled = false;
        });
    };
    
    // Enhanced Add URL Page functionality
    window.addUrlPage = function() {
        const url = prompt('Voer URL in:');
        if (url) {
            console.log('Adding URL page...');
            
            const urlParts = window.location.pathname.split('/');
            const sjabloonId = urlParts[urlParts.indexOf('sjablonen') + 1];
            
            fetch(`/sjablonen/${sjabloonId}/pages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    is_url_page: true,
                    url: url
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('URL pagina wordt toegevoegd!', 'success');
                    if (data.reload) {
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Er is een fout opgetreden', 'error');
            });
        }
    };
    
    // Enhanced notification system
    if (typeof window.showNotification === 'undefined') {
        window.showNotification = function(message, type) {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg text-white z-50 font-semibold shadow-lg ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            notification.textContent = message;
            notification.style.animation = 'slideInRight 0.3s ease-out';
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease-in';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 2700);
        };
    }
    
    console.log('Sjablonen editor button enhancements loaded!');
});

// CSS animations for notifications
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);