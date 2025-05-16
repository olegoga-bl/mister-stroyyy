<?php
// footer.php
?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-content">
            <!-- Контакты -->
            <div class="footer-section">
                <h3><i class="fas fa-address-book"></i> Контакты</h3>
                <p><i class="fas fa-phone-alt"></i> +7 (495) 123-45-67</p>
                <p><i class="fas fa-envelope"></i> support@mister-stroy.ru</p>
                <p><i class="fas fa-map-marker-alt"></i> Москва, ул. Строителей, д. 10</p>
            </div>

            <!-- Социальные сети -->
            <div class="footer-section">
                <h3><i class="fas fa-share-alt"></i> Мы в соцсетях</h3>
                <div class="social-links">
                    <a href="https://vk.com/misterstroy" target="_blank"><i class="fab fa-vk"></i> ВКонтакте</a>
                    <a href="https://t.me/misterstroy" target="_blank"><i class="fab fa-telegram-plane"></i> Telegram</a>
                    <a href="https://instagram.com/misterstroy" target="_blank"><i class="fab fa-instagram"></i> Instagram</a>
                </div>
            </div>

            <!-- Полезные ссылки -->
            <div class="footer-section">
                <h3><i class="fas fa-link"></i> Полезные ссылки</h3>
                <p><a href="faq.php"><i class="fas fa-question-circle"></i> FAQ</a></p>
                <p><a href="about.php"><i class="fas fa-info-circle"></i> О нас</a></p>
                <p><a href="contacts.php"><i class="fas fa-address-card"></i> Контакты</a></p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2025 Мистер Строй. Все права защищены.</p>
        </div>
    </div>
</footer>

<style>
.site-footer {
    background: #333;
    color: #fff;
    padding: 40px 0;
    font-family: 'Montserrat', sans-serif;
}

.site-footer .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.footer-content {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px;
}

.footer-section {
    flex: 1;
    min-width: 200px;
}

.footer-section h3 {
    font-size: 1.2em;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: #fff; /* Белый цвет для заголовков */
}

.footer-section p {
    margin: 10px 0;
    display: flex;
    align-items: center;
    gap: 10px;
    color: #fff; /* Белый цвет для текста контактов */
}

.footer-section a {
    color: #fff; /* Белый цвет для ссылок */
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: color 0.3s;
}

.footer-section a:hover {
    color: #007BFF;
}

.social-links a {
    display: inline-flex;
    margin-right: 15px;
    color: #fff; /* Белый цвет для ссылок соцсетей */
}

.footer-bottom {
    text-align: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #555;
    font-size: 0.9em;
    color: #fff; /* Белый цвет для текста внизу */
}

@media (max-width: 768px) {
    .footer-content {
        flex-direction: column;
        text-align: center;
    }

    .social-links a {
        margin: 0 10px;
    }
}
</style>