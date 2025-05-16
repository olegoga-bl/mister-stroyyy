<?php
include 'components/core.php';

// Подсчёт количества товаров в корзине
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мистер-строй</title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <h1><a href="index.php">Мистер Строй</a></h1>
                <span class="logo-tagline">Строительные материалы</span>
            </div>
            <nav>
                <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Главная
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user"></i> Профиль
                    </a>
                    <a href="cart.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'active' : ''; ?>">
                        <i class="fas fa-shopping-cart"></i> Корзина
                    </a>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                        <a href="admin.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''; ?>">
                            <i class="fas fa-user-shield"></i> Админ-панель
                        </a>
                    <?php endif; ?>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Выйти
                    </a>
                <?php else: ?>
                    <a href="login.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : ''; ?>">
                        <i class="fas fa-sign-in-alt"></i> Войти
                    </a>
                    <a href="reg.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reg.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user-plus"></i> Регистрация
                    </a>
                <?php endif; ?>
            </nav>
            <button class="burger-menu" onclick="toggleMenu()"><i class="fas fa-bars"></i></button>
        </div>
    </header>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const burger = document.querySelector('.burger-menu');
        const nav = document.querySelector('header nav');
        
        burger.addEventListener('click', () => {
            nav.classList.toggle('active');
            burger.innerHTML = nav.classList.contains('active') ? 
                '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
        });
    });
    </script>