<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'components/core.php';

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;


$stmt = $conn->prepare("SELECT id, total, address, payment_method, created_at, status 
                        FROM orders 
                        WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    $_SESSION['error'] = "Заказ не найден или доступ запрещён.";
    header("Location: profile.php");
    exit;
}


$stmt = $conn->prepare("SELECT oi.product_id, oi.quantity, oi.price, p.name, p.image 
                        FROM order_items oi 
                        JOIN products p ON oi.product_id = p.id 
                        WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order_items = [];
while ($row = $result->fetch_assoc()) {
    $order_items[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="ru">

<body>
    <?php include 'components/header.php'; ?>
    <main>
        <h2>Детали заказа #<?php echo $order_id; ?></h2>
        <section class="order-details">
            <h3>Информация о заказе</h3>
            <p><strong>Дата:</strong> <?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></p>
            <p><strong>Адрес доставки:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
            <p><strong>Способ оплаты:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
            <p><strong>Итого:</strong> <?php echo $order['total']; ?> руб.</p>
            <p><strong>Статус:</strong> <span class="order-status"><?php echo htmlspecialchars($order['status']); ?></span></p>
        </section>
        <section class="order-items">
            <h3>Товары</h3>
            <?php if (empty($order_items)): ?>
                <p>Товары не найдены.</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Товар</th>
                        <th>Цена</th>
                        <th>Количество</th>
                        <th>Сумма</th>
                    </tr>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td>
                                <img src="images/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                                <?php echo htmlspecialchars($item['name']); ?>
                            </td>
                            <td><?php echo $item['price']; ?> руб.</td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo $item['price'] * $item['quantity']; ?> руб.</td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </section>
        <a href="profile.php" class="back-link">Вернуться в личный кабинет</a>
    </main>
    <?php
    include 'components/footer.php';
?>
</body>
</html>