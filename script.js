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
                    // Обновляем количество товаров в корзине
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) {
                        cartCount.textContent = data.total;
                    }
                    
                    // Показываем уведомление
                    showNotification(data.message, 'success');
                } else {
                    // Если пользователь не авторизован, перенаправляем на страницу входа
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

// Функция для показа уведомлений
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Удаляем уведомление через 3 секунды
    setTimeout(() => {
        notification.remove();
    }, 3000);
} 