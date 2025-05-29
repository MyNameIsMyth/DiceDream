document.addEventListener('DOMContentLoaded', function() {
    // Обработка добавления в корзину
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.getAttribute('data-item-id');
            
            fetch('Page/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'item_id=' + itemId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) {
                        cartCount.textContent = data.total;
                    }
                    
                    
                    showNotification(data.message, 'success');
                } else {
                    
                    if (data.message === 'Необходимо войти в систему') {
                        window.location.href = 'Page/vhod.php';
                    } else {
                        showNotification(data.message, 'error');
                    }
                }
            })
            .catch(error => {
                showNotification('Произошла ошибка при добавлении товара', 'error');
            });
        });
    });
});


function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
} 