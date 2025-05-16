<?php
include 'components/core.php';


$is_logged_in = isset($_SESSION['user_id']);


$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Мистер Строй</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .product {
            display: flex;
            align-items: center;
            margin: 20px 0;
            padding: 20px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .product img {
            max-width: 300px;
            margin-right: 20px;
        }
        .product-details {
            flex: 1;
        }
        .product-details button {
            padding: 10px 20px;
            background: #f28c38;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.2s ease;
        }
        .product-details button:hover {
            background: #e07b30;
        }
    </style>
</head>
<body>
    <?php include 'components/header.php'; ?>
    <main>
        <div class="product">
            <img src="images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <div class="product-details">
                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                <p>Цена: <?php echo $product['price']; ?> руб.</p>
                <p><?php echo htmlspecialchars($product['description']); ?></p>
                <?php if ($is_logged_in): ?>
                    <button class="add-to-cart" data-id="<?php echo $product['id']; ?>"><i class="fas fa-cart-plus"></i> В корзину</button>
                <?php else: ?>
                    <a href="login.php" class="add-to-cart"><i class="fas fa-sign-in-alt"></i> Войдите, чтобы добавить в корзину</a>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <?php include 'components/footer.php'; ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const addToCartButton = document.querySelector('.add-to-cart');
        if (addToCartButton && addToCartButton.hasAttribute('data-id')) {
            addToCartButton.addEventListener('click', function() {
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
        }

      
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