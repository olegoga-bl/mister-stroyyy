<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'components/core.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $response = ['success' => false, 'message' => ''];
    $product_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    $stmt = $conn->prepare("SELECT id, name, price FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if (!$product) {
        $response['message'] = "Товар не найден.";
    } else {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity']++;
        } else {
            $_SESSION['cart'][$product_id] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => 1
            ];
        }
        $response['success'] = true;
        $response['message'] = "Товар добавлен в корзину!";
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_quantity') {
    $response = ['success' => false, 'message' => '', 'total' => 0];
    $product_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 0;

    if (isset($_SESSION['cart'][$product_id])) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
            $response['message'] = 'Товар удалён из корзины.';
        } else {
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
            $response['message'] = 'Количество обновлено.';
        }
        $response['success'] = true;
    }

    
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    $response['total'] = $total;

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}


$success = '';
$error = '';
if (isset($_GET['action']) && isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    $action = $_GET['action'];

    $stmt = $conn->prepare("SELECT id, name, price FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if (!$product) {
        $error = "Товар не найден.";
    } else {
        if ($action === 'add') {
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity']++;
            } else {
                $_SESSION['cart'][$product_id] = [
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'quantity' => 1
                ];
            }
            $success = "Товар добавлен в корзину.";
        } elseif ($action === 'remove') {
            if (isset($_SESSION['cart'][$product_id])) {
                unset($_SESSION['cart'][$product_id]);
                $success = "Товар удалён из корзины.";
            }
        }
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
    <title>Корзина - Мистер Строй</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
       
        .cart {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            margin: 2rem auto;
            max-width: 1200px;
            animation: fadeIn 0.5s ease-out;
        }

        .cart h2 {
            color: #2d3748;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            text-align: center;
            font-family: 'Playfair Display', serif;
        }

        
        .cart table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 1rem;
            margin: 2rem 0;
        }

        .cart th {
            background: #f28c38;
            color: white;
            padding: 1rem;
            font-weight: 600;
            text-align: left;
            border-radius: 10px;
        }

        .cart td {
            background: rgba(255, 255, 255, 0.9);
            padding: 1.2rem;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .cart tr:hover td {
            background: rgba(255, 255, 255, 1);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            background: rgba(242, 140, 56, 0.1);
            padding: 0.5rem;
            border-radius: 8px;
        }

        .quantity-controls button {
            background: #f28c38;
            color: white;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 2px 5px rgba(242, 140, 56, 0.3);
        }

        .quantity-controls button:hover {
            background: #e07b30;
            transform: scale(1.1);
        }

        .quantity-controls input {
            width: 60px;
            text-align: center;
            padding: 0.5rem;
            border: 2px solid #f28c38;
            border-radius: 8px;
            font-size: 1rem;
            background: white;
            color: #2d3748;
            font-weight: 600;
        }

       
        .cart-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .cart-actions a, .cart-actions button {
            padding: 1rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .cart-actions a:first-child {
            background: #f28c38;
            color: white;
            box-shadow: 0 4px 15px rgba(242, 140, 56, 0.3);
        }

        .cart-actions a:last-child {
            background: white;
            color: #f28c38;
            border: 2px solid #f28c38;
        }

        .cart-actions a:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(242, 140, 56, 0.4);
        }

        .delete-btn {
            background: #ff6b6b;
            color: white;
            border: none;
            padding: 0.7rem 1.2rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(255, 107, 107, 0.3);
        }

        .delete-btn:hover {
            background: #ff5252;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(255, 107, 107, 0.4);
        }

        
        .total {
            background: rgba(242, 140, 56, 0.1);
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            margin: 2rem 0;
            font-size: 1.4rem;
            font-weight: 700;
            color: #2d3748;
        }

        .total span {
            color: #f28c38;
            font-size: 1.6rem;
        }

        
        .empty-cart {
            text-align: center;
            padding: 3rem;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .empty-cart i {
            font-size: 4rem;
            color: #f28c38;
            margin-bottom: 1.5rem;
        }

        .empty-cart h3 {
            font-size: 2rem;
            color: #2d3748;
            margin-bottom: 1rem;
            font-family: 'Playfair Display', serif;
        }

        .empty-cart p {
            color: #718096;
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }

        .empty-cart a {
            display: inline-block;
            padding: 1rem 2rem;
            background: #f28c38;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(242, 140, 56, 0.3);
        }

        .empty-cart a:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(242, 140, 56, 0.4);
        }

        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .loading {
            display: none;
            color: #f28c38;
            text-align: center;
            padding: 1rem;
            font-weight: 500;
        }

        .loading i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        
        @media (max-width: 768px) {
            .cart {
                padding: 1.5rem;
                margin: 1rem;
            }

            .cart table {
                display: block;
                overflow-x: auto;
            }

            .cart th, .cart td {
                padding: 0.8rem;
                font-size: 0.9rem;
            }

            .quantity-controls {
                flex-wrap: wrap;
                justify-content: center;
            }

            .cart-actions {
                flex-direction: column;
            }

            .cart-actions a, .cart-actions button {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .cart h2 {
                font-size: 2rem;
            }

            .total {
                font-size: 1.2rem;
            }

            .total span {
                font-size: 1.4rem;
            }

            .empty-cart {
                padding: 2rem;
            }

            .empty-cart h3 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'components/header.php'; ?>
    <main>
        <h2><i class="fas fa-shopping-cart"></i> Корзина</h2>
        <?php if ($success): ?>
            <p class="success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if (empty($_SESSION['cart'])): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Корзина пуста</h3>
                <p>Вы еще не добавили товары в корзину.</p>
                <a href="index.php">Перейти к покупкам</a>
            </div>
        <?php else: ?>
            <div class="cart">
                <h2>Содержимое корзины</h2>
                <table>
                    <tr>
                        <th>Товар</th>
                        <th>Цена</th>
                        <th>Количество</th>
                        <th>Сумма</th>
                        <th>Действия</th>
                    </tr>
                    <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo $item['price']; ?> руб.</td>
                            <td>
                                <div class="quantity-controls" data-id="<?php echo $id; ?>">
                                    <button type="button" class="decrease">-</button>
                                    <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1">
                                    <button type="button" class="increase">+</button>
                                </div>
                            </td>
                            <td><?php echo $item['price'] * $item['quantity']; ?> руб.</td>
                            <td>
                                <a href="cart.php?action=remove&id=<?php echo $id; ?>" class="delete-btn"><i class="fas fa-trash"></i> Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <div class="total">
                    Итого: <span id="total-amount"><?php echo $total; ?></span> руб.
                </div>
                <div class="cart-actions">
                    <a href="index.php">Продолжить покупки</a>
                    <a href="checkout.php">Оформить заказ</a>
                </div>
                <div class="loading" id="loading">Обновление...</div>
            </div>
        <?php endif; ?>
    </main>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const quantityControls = document.querySelectorAll('.quantity-controls');
            const loading = document.getElementById('loading');
            const totalAmount = document.getElementById('total-amount');

            quantityControls.forEach(control => {
                const id = control.getAttribute('data-id');
                const decreaseBtn = control.querySelector('.decrease');
                const increaseBtn = control.querySelector('.increase');
                const input = control.querySelector('.quantity-input');

                function updateQuantity(operation) {
                    let quantity = parseInt(input.value);
                    if (operation === 'decrease' && quantity > 1) {
                        quantity--;
                    } else if (operation === 'increase') {
                        quantity++;
                    }
                    input.value = quantity;
                    updateCart(id, quantity);
                }

                decreaseBtn.addEventListener('click', () => updateQuantity('decrease'));
                increaseBtn.addEventListener('click', () => updateQuantity('increase'));

                input.addEventListener('change', () => {
                    const quantity = Math.max(1, parseInt(input.value) || 1);
                    input.value = quantity;
                    updateCart(id, quantity);
                });

                function updateCart(productId, quantity) {
                    loading.style.display = 'inline';
                    fetch('cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=update_quantity&id=${productId}&quantity=${quantity}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (quantity <= 0) {
                                control.closest('tr').remove();
                                if (document.querySelectorAll('table tr').length === 1) {
                                    window.location.reload(); // Перезагрузка, если корзина пуста
                                }
                            }
                            totalAmount.textContent = data.total;
                        } else {
                            alert('Ошибка: ' + data.message);
                        }
                        loading.style.display = 'none';
                    })
                    .catch(error => {
                        console.error('Ошибка:', error);
                        loading.style.display = 'none';
                        alert('Произошла ошибка при обновлении корзины.');
                    });
                }
            });
        });
    </script>
</body>
</html>