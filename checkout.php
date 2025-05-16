<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'components/core.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $user_id = $_SESSION['user_id'];

    
    if (empty($_SESSION['cart'])) {
        $error = "Корзина пуста. Добавьте товары перед оформлением.";
    } else {
       
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total, address, payment_method, status, created_at) VALUES (?, ?, ?, ?, 'В обработке', NOW())");
        $stmt->bind_param("idss", $user_id, $total, $address, $payment_method);
        if ($stmt->execute()) {
            $order_id = $conn->insert_id;

            
            $stmt_items = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($_SESSION['cart'] as $product_id => $item) {
                $stmt_items->bind_param("iiid", $order_id, $product_id, $item['quantity'], $item['price']);
                if (!$stmt_items->execute()) {
                    $error = "Ошибка при добавлении товаров в заказ: " . $conn->error;
                    break;
                }
            }
            $stmt_items->close();

            if (!$error) {
                
                $_SESSION['cart'] = [];
                $success = "Заказ успешно оформлен! Номер заказа: #$order_id";
                header("Location: profile.php");
                exit;
            }
        } else {
            $error = "Ошибка при создании заказа: " . $conn->error;
        }
        $stmt->close();
    }
}


$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформление заказа - Мистер Строй</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
       
    </style>
</head>
<body>
    <?php include 'components/header.php'; ?>
    <main>
        <div class="checkout-container">
            <h2><i class="fas fa-check-circle"></i> Оформление заказа</h2>
            <?php if ($error): ?>
                <div class="error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <?php if (empty($_SESSION['cart'])): ?>
                <div class="empty-cart-message">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Корзина пуста</h3>
                    <p>Добавьте товары в корзину перед оформлением заказа</p>
                    <a href="index.php"><i class="fas fa-arrow-left"></i> Вернуться к покупкам</a>
                </div>
            <?php else: ?>
                <div class="total">
                    Итого: <span><?php echo $total; ?> руб.</span>
                </div>
                <form method="post" class="checkout-form">
                    <label>
                        <i class="fas fa-map-marker-alt"></i> Адрес доставки
                    </label>
                    <input type="text" name="address" required 
                           placeholder="г. Москва, ул. Примерная, д. 1">
                    
                    <label>
                        <i class="fas fa-credit-card"></i> Способ оплаты
                    </label>
                    <select name="payment_method" required>
                        <option value="Наличными при получении">Наличными при получении</option>
                        <option value="Картой онлайн">Картой онлайн</option>
                    </select>
                    
                    <button type="submit" name="place_order">
                        <i class="fas fa-check"></i> Оформить заказ
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </main>
    <footer>
        <p>© 2025 Мистер Строй</p>
    </footer>
</body>
</html>