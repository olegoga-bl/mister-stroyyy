<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'components/core.php';

$user_id = $_SESSION['user_id'];


$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$new_user = isset($_SESSION['new_user']) ? true : false;
$order_success = isset($_SESSION['order_success']) ? $_SESSION['order_success'] : false;
$error = isset($_SESSION['error']) ? $_SESSION['error'] : false;
if ($new_user) {
    unset($_SESSION['new_user']);
}
if ($order_success) {
    unset($_SESSION['order_success']);
}
if ($error) {
    unset($_SESSION['error']);
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $password = $_POST['password'];
    
    if (!empty($password)) {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $password, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $user_id);
    }
    
    if ($stmt->execute()) {
        $success = "Профиль успешно обновлён!";
        $_SESSION['user_name'] = $name;
    } else {
        $error = "Ошибка при обновлении профиля.";
    }
    $stmt->close();
}

$page_title = "Личный кабинет - Мистер Строй";
?>
<!DOCTYPE html>
<html lang="ru">

<body>
    <?php include 'components/header.php'; ?>
    <main class="fade-in">
        <h2><i class="fas fa-user-circle"></i> Личный кабинет</h2>
        <?php
        if ($new_user) {
            echo '<p class="success"><i class="fas fa-check-circle"></i> Добро пожаловать, ' . htmlspecialchars($user['name']) . '!</p>';
        }
        if ($order_success) {
            echo '<p class="success"><i class="fas fa-check-circle"></i> ' . htmlspecialchars($order_success) . '</p>';
        }
        if ($error) {
            echo '<p class="error"><i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($error) . '</p>';
        }
        if (isset($success)) {
            echo '<p class="success"><i class="fas fa-check-circle"></i> ' . $success . '</p>';
        }
        ?>
        <section class="profile-info fade-in">
            <h3>Информация о пользователе</h3>
            <form method="post">
                <label>Имя</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                <label>Email</label>
                <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                <label>Новый пароль</label>
                <input type="password" name="password" placeholder="Оставьте пустым, если не меняете">
                <button type="submit" name="update_profile"><i class="fas fa-save"></i> Сохранить</button>
            </form>
        </section>
        <section class="order-history fade-in">
            <h3>История заказов</h3>
            <?php
            $stmt = $conn->prepare("SELECT id, total, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                echo '<table>';
                echo '<tr><th>Заказ</th><th>Дата</th><th>Сумма</th><th>Детали</th></tr>';
                while ($order = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>#' . $order['id'] . '</td>';
                    echo '<td>' . date('d.m.Y H:i', strtotime($order['created_at'])) . '</td>';
                    echo '<td>' . $order['total'] . ' руб.</td>';
                    echo '<td><a href="order_details.php?order_id=' . $order['id'] . '"><i class="fas fa-info-circle"></i> Подробнее</a></td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo '<p><i class="fas fa-info-circle"></i> У вас пока нет заказов.</p>';
            }
            $stmt->close();
            ?>
        </section>
    </main>
    <?php
    include 'components/footer.php';
?>
</body>
</html>