<?php
include 'components/core.php';
include 'components/header.php';


$is_logged_in = isset($_SESSION['user_id']);


$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;


if ($category_id > 0) {
    $stmt = $conn->prepare("SELECT id, name, description, price, image FROM products WHERE category_id = ? LIMIT 6");
    $stmt->bind_param("i", $category_id);
} else {
    $stmt = $conn->prepare("SELECT id, name, description, price, image FROM products ORDER BY created_at DESC LIMIT 4");
}
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
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
    <style>
        .category-list .category {
            cursor: pointer;
            transition: transform 0.2s;
        }
        .category-list .category:hover {
            transform: scale(1.05);
        }
        .product-list .product {
            position: relative;
        }
        .product-list .product button.add-to-cart {
            display: inline-block;
            margin-top: 10px;
            padding: 5px 10px;
            background: #f28c38;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.2s ease;
        }
        .product-list .product button.add-to-cart:hover {
            background: #e07b30;
        }
        .about, .advantages, .contacts {
            margin: 20px 0;
            padding: 20px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .about {
            display: flex;
            align-items: center;
        }
        .about img {
            max-width: 300px;
            margin-right: 20px;
        }
        .about-text {
            flex: 1;
        }
        .advantages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            text-align: center;
        }
        .advantage-item i {
            font-size: 2em;
            color: #f28c38;
            margin-bottom: 10px;
        }
        .contacts p {
            margin: 10px 0;
        }
        .contacts a {
            color: #f28c38;
            text-decoration: none;
        }
    </style>
</head>
<body>
    
    <main>
      
        <section class="hero-slider">
            <div class="slider-container">
                <div class="slide active">
                    <img src="images/slider/slide1.jpg" alt="Строительные материалы">
                    <div class="slide-content">
                        <h2>Качественные строительные материалы</h2>
                        <p>Широкий выбор для вашего ремонта</p>
                    </div>
                </div>
                <div class="slide">
                    <img src="images/slider/slide2.jpg" alt="Инструменты">
                    <div class="slide-content">
                        <h2>Профессиональные инструменты</h2>
                        <p>Всё для качественного ремонта</p>
                    </div>
                </div>
                <div class="slide">
                    <img src="images/slider/slide3.jpg" alt="Специальные предложения">
                    <div class="slide-content">
                        <h2>Специальные предложения</h2>
                        <p>Скидки до 30% на популярные товары</p>
                    </div>
                </div>
            </div>
            <div class="slider-controls">
                <button class="prev-slide"><i class="fas fa-chevron-left"></i></button>
                <div class="slider-dots"></div>
                <button class="next-slide"><i class="fas fa-chevron-right"></i></button>
            </div>
        </section>

       
        <section class="about">
            <img src="images/scastlivye-rabocie-na-stroitel-noi-plosadke-molodoi-inzener-stroitel-i-arhitektory-pozimaut-drug-drugu-ruki-na-stroitel-noi-plosadke-i-smotrat-na-koncepciu-sovmestnoi-raboty-na-sleduusem-etape-stroitel-stva.jpg" alt="О компании">
            <div class="about-text">
                <h2>О нашей компании</h2>
                <p>Мистер Строй — ваш надёжный партнёр в строительстве и ремонте с 2010 года. Мы предлагаем широкий ассортимент строительных материалов, инструментов и отделочных материалов по конкурентным ценам.</p>
                <p>Наша миссия — помогать клиентам создавать уютные и прочные дома с высоким качеством обслуживания.</p>
            </div>
        </section>

      
        <section class="categories">
            <h2>Категории</h2>
            <div class="category-list">
                <?php
                $sql = "SELECT * FROM categories";
                $result = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<div class="category" onclick="location.href=\'index.php?category_id=' . $row['id'] . '\';">';
                    echo '<img src="images/' . $row['image'] . '" alt="' . $row['name'] . '">';
                    echo '<p>' . $row['name'] . '</p>';
                    echo '</div>';
                }
                ?>
            </div>
        </section>

      
        <section class="best-sellers">
            <h2><?php echo $category_id > 0 ? 'Товары категории' : 'Хиты продаж'; ?></h2>
            <div class="product-list">
                <?php if (empty($products)): ?>
                    <p><i class="fas fa-info-circle"></i> Товары отсутствуют.</p>
                <?php else: ?>
                    <?php foreach ($products as $row): ?>
                        <div class="product">
                            <img src="images/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                            <h3><?php echo $row['name']; ?></h3>
                            <p><?php echo $row['price']; ?> руб.</p>
                            <a href="product.php?id=<?php echo $row['id']; ?>">Подробнее</a>
                            <?php if ($is_logged_in): ?>
                                <button class="add-to-cart" data-id="<?php echo $row['id']; ?>">В корзину</button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        
        <section class="advantages">
            <h2>Наши преимущества</h2>
            <div class="advantages-grid">
                <div class="advantage-item">
                    <i class="fas fa-truck"></i>
                    <h3>Быстрая доставка</h3>
                    <p>Доставка по всей стране в кратчайшие сроки.</p>
                </div>
                <div class="advantage-item">
                    <i class="fas fa-check-circle"></i>
                    <h3>Гарантия качества</h3>
                    <p>Сертифицированные товары и строгий контроль.</p>
                </div>
                <div class="advantage-item">
                    <i class="fas fa-users"></i>
                    <h3>Опытная команда</h3>
                    <p>Работаем с 2010 года для тысяч клиентов.</p>
                </div>
            </div>
        </section>

        
        <section class="contacts">
            <h2>Контакты</h2>
            <p><i class="fas fa-phone"></i> Телефон: <a href="tel:+79991234567">+7 (999) 123-45-67</a></p>
            <p><i class="fas fa-envelope"></i> Email: <a href="mailto:info@mister-stroy.ru">info@mister-stroy.ru</a></p>
            <p><i class="fas fa-map-marker-alt"></i> Адрес: г. Москва, ул. Строителей, д. 10</p>
        </section>
    </main>
    <?php include 'components/footer.php'; ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelector('.slider-dots');
        const prevBtn = document.querySelector('.prev-slide');
        const nextBtn = document.querySelector('.next-slide');
        let currentSlide = 0;
        let slideInterval;

        slides.forEach((_, index) => {
            const dot = document.createElement('div');
            dot.classList.add('dot');
            if (index === 0) dot.classList.add('active');
            dot.addEventListener('click', () => goToSlide(index));
            dots.appendChild(dot);
        });

        function goToSlide(n) {
            slides[currentSlide].classList.remove('active');
            dots.children[currentSlide].classList.remove('active');
            currentSlide = (n + slides.length) % slides.length;
            slides[currentSlide].classList.add('active');
            dots.children[currentSlide].classList.add('active');
        }

        function startSlideInterval() {
            slideInterval = setInterval(() => {
                goToSlide(currentSlide + 1);
            }, 5000);
        }

        function stopSlideInterval() {
            clearInterval(slideInterval);
        }

        prevBtn.addEventListener('click', () => {
            stopSlideInterval();
            goToSlide(currentSlide - 1);
            startSlideInterval();
        });

        nextBtn.addEventListener('click', () => {
            stopSlideInterval();
            goToSlide(currentSlide + 1);
            startSlideInterval();
        });

        const slider = document.querySelector('.hero-slider');
        slider.addEventListener('mouseenter', stopSlideInterval);
        slider.addEventListener('mouseleave', startSlideInterval);

        startSlideInterval();

        let touchStartX = 0;
        let touchEndX = 0;

        slider.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, false);

        slider.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, false);

        function handleSwipe() {
            if (touchEndX < touchStartX) {
                stopSlideInterval();
                goToSlide(currentSlide + 1);
                startSlideInterval();
            }
            if (touchEndX > touchStartX) {
                stopSlideInterval();
                goToSlide(currentSlide - 1);
                startSlideInterval();
            }
        }

       
        const addToCartButtons = document.querySelectorAll('.add-to-cart');
        addToCartButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                fetch('cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=add_to_cart&id=${productId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    showNotification('Произошла ошибка при добавлении товара.', 'error');
                });
            });
        });

      
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `<i class="${type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'}"></i> ${message}`;
            document.body.appendChild(notification);
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    });
    </script>
</body>
</html>