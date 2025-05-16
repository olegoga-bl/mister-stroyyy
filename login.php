<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start(); 
session_start();
include 'components/core.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
           
            $_SESSION['is_admin'] = isset($user['is_admin']) && $user['is_admin'] == 1;
            if ($_SESSION['is_admin']) {
                header("Location: admin.php");
                exit;
            } else {
                header("Location: profile.php");
                exit;
            }
        } else {
            $error = "Неверный пароль.";
        }
    } else {
        $error = "Пользователь с таким email не найден.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - Мистер Строй</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'components/header.php'; ?>
    <main>
        <div class="login-container">
            <h2><i class="fas fa-sign-in-alt"></i> Вход</h2>
            <?php if ($error): ?>
                <p class="error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p class="success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>
            <form method="post">
                <label><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" required>
                <label><i class="fas fa-lock"></i> Пароль</label>
                <input type="password" name="password" required>
                <button type="submit"><i class="fas fa-sign-in-alt"></i> Войти</button>
            </form>
            <p>Нет аккаунта? <a href="reg.php">Зарегистрируйтесь</a></p>
        </div>
    </main>
    <footer>
        <p>© 2025 Мистер Строй</p>
    </footer>
</body>
</html>
<?php ob_end_flush();  ?>