<?php
session_start();
include 'components/core.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $sql = "SELECT id FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        $error = "Этот email уже зарегистрирован!";
    } else {
        $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";
        if (mysqli_query($conn, $sql)) {
            
            $user_id = mysqli_insert_id($conn);
           
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $name;
            
            header("Location: profile.php");
            exit;
        } else {
            $error = "Ошибка: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация - Мистер-строй</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'components/header.php'; ?>
    <main class="auth-container">
    <h2>Регистрация</h2>
    <?php if (isset($error)) { echo '<p class="error">' . $error . '</p>'; } ?>
    <form method="post" action="reg.php">
        <label>Имя:</label>
        <input type="text" name="name" class="form-control" placeholder="Имя" required>
        <label>Email:</label>
        <input type="email" name="email" class="form-control" placeholder="Email" required>
        <label>Пароль:</label>
        <input type="password" name="password" class="form-control" placeholder="Пароль" required>
        <button type="submit">Зарегистрироваться</button>
    </form>
    <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
</main>
    <footer>
        <p>© 2025 Мистер-строй</p>
    </footer>
</body>
</html>