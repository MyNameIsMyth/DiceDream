    <!-- Footer -->
    <footer class="footer">
        <div class="footer-logo">
            <img src="/Media/logo.png" alt="логотип"/>
        </div>
        <div class="footer-content">
            <div class="footer-section">
                <h4>Страницы</h4>
                <ul>
                    <li><a href="/">Главная</a></li>
                    <li><a href="/Page/catalog.php">Каталог</a></li>
                    <li><a href="/Page/busket.php">Корзина</a></li>
                    <li><a href="/Page/fav.php">Избранное</a></li>
                    <li><a href="/Page/personal.php">Профиль</a></li>
                    <li><a href="/Page/delivery.php">Доставка</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Услуги</h4>
                <ul>
                    <li><a href="/Page/delivery.php">Доставка</a></li>
                    <li><a href="/Page/support.php">Служба поддержки</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Документация</h4>
                <ul>
                    <li><a href="/Page/delivery-terms.php">Условия доставки</a></li>
                    <li><a href="/Page/storage-terms.php">Условия хранения</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-qr">
            <div class="qr-code">
                <img src="/Media/qr.png" alt="QR Код"/>
            </div>
        </div>
    </footer>

    <script src="/script.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('.search-input');
        const searchResults = document.querySelector('.search-results');
        let searchTimeout;

        // Функция для экранирования HTML
        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Обработка ввода в поле поиска
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            if (query.length < 2) {
                searchResults.innerHTML = '';
                searchResults.classList.remove('active');
                return;
            }

            // Показываем индикатор загрузки
            searchResults.innerHTML = '<div class="no-results">Поиск...</div>';
            searchResults.classList.add('active');

            // Задержка перед отправкой запроса для оптимизации
            searchTimeout = setTimeout(() => {
                fetch(`/Page/search.php?query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (data.results && data.results.length > 0) {
                                searchResults.innerHTML = data.results.map(item => `
                                    <div class="search-item" onclick="window.location.href='/Page/product.php?id=${escapeHtml(item.idItem)}'">
                                        ${item.img ? 
                                            `<img src="data:image/jpeg;base64,${item.img}" 
                                                  alt="${escapeHtml(item.ItemName)}" 
                                                  class="search-item-image">` : 
                                            '<div class="search-item-image-placeholder"></div>'}
                                        <div class="search-item-details">
                                            <div class="search-item-name">${escapeHtml(item.ItemName)}</div>
                                            ${item.nameCategory ? 
                                                `<div class="search-item-category">${escapeHtml(item.nameCategory)}</div>` : 
                                                ''}
                                            <div class="search-item-price">${new Intl.NumberFormat('ru-RU').format(item.Price)}₽</div>
                                        </div>
                                    </div>
                                `).join('');
                            } else {
                                searchResults.innerHTML = '<div class="no-results">Ничего не найдено</div>';
                            }
                        } else {
                            searchResults.innerHTML = `<div class="no-results">Произошла ошибка при поиске${data.message ? ': ' + escapeHtml(data.message) : ''}</div>`;
                        }
                    })
                    .catch(error => {
                        searchResults.innerHTML = '<div class="no-results">Произошла ошибка при поиске</div>';
                    });
            }, 300);
        });

        // Закрытие результатов поиска при клике вне
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.remove('active');
            }
        });

        // Открытие результатов поиска при фокусе, если есть текст
        searchInput.addEventListener('focus', function() {
            if (this.value.trim().length >= 2) {
                searchResults.classList.add('active');
            }
        });
    });
    </script>
</body>
</html> 