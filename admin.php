<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();
include 'components/core.php';


if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit;
}


$success = '';
$error = '';
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    if ($stmt->execute()) {
        $success = "Товар успешно удалён.";
    } else {
        $error = "Ошибка при удалении товара: " . $conn->error;
    }
    $stmt->close();
}


if (isset($_GET['action']) && $_GET['action'] === 'delete_order' && isset($_GET['order_id'])) {
    $order_id = (int)$_GET['order_id'];
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        if ($stmt->execute()) {
            $conn->commit();
            $success = "Заказ успешно удалён.";
        } else {
            throw new Exception("Ошибка при удалении заказа: " . $conn->error);
        }
        $stmt->close();
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}


if (isset($_GET['action']) && $_GET['action'] === 'delete_category' && isset($_GET['id'])) {
    $category_id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT image FROM categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $category = $result->fetch_assoc();
        $image = $category['image'];
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $category_id);
        if ($stmt->execute()) {
            if ($image && $image != 'default.jpg' && file_exists("images/" . $image)) {
                unlink("images/" . $image);
            }
            $success = "Категория успешно удалена.";
        } else {
            $error = "Ошибка при удалении категории: " . $conn->error;
        }
        $stmt->close();
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = (float)$_POST['price'];
    $category_id = (int)$_POST['category_id'];

    $image = 'default.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        if (in_array($_FILES['image']['type'], $allowed_types)) {
            $filename = uniqid() . '_' . basename($_FILES['image']['name']);
            $target_dir = "images/"; // Исправлено: используем "images/"
            $target_file = $target_dir . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image = $filename;
            } else {
                $error = "Ошибка при загрузке изображения: " . error_get_last()['message'];
            }
        } else {
            $error = "Допустимы только файлы JPG, JPEG или PNG.";
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, image, category_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssdsi", $name, $description, $price, $image, $category_id);
        if ($stmt->execute()) {
            $success = "Товар успешно добавлен.";
        } else {
            $error = "Ошибка при добавлении товара: " . $conn->error;
        }
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    if ($stmt->execute()) {
        $success = "Статус заказа обновлён.";
    } else {
        $error = "Ошибка при обновлении статуса: " . $conn->error;
    }
    $stmt->close();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['add_category']) || isset($_POST['edit_category']))) {
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $name = mysqli_real_escape_string($conn, $_POST['name']);

    $image = isset($_POST['current_image']) ? $_POST['current_image'] : 'default.jpg';
    if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        if (in_array($_FILES['category_image']['type'], $allowed_types)) {
            $filename = uniqid() . '_' . basename($_FILES['category_image']['name']);
            $target_dir = "images/"; // Исправлено: используем "images/"
            $target_file = $target_dir . $filename;
            if (move_uploaded_file($_FILES['category_image']['tmp_name'], $target_file)) {
                $image = $filename;
                // Удаляем старое изображение, если оно не default.jpg
                if ($category_id > 0 && $image != 'default.jpg') {
                    $old_image = $_POST['current_image'];
                    if (file_exists($target_dir . $old_image) && $old_image != 'default.jpg') {
                        unlink($target_dir . $old_image);
                    }
                }
            } else {
                $error = "Ошибка при загрузке изображения категории: " . error_get_last()['message'];
            }
        } else {
            $error = "Допустимы только файлы JPG, JPEG или PNG.";
        }
    }

    if (!$error) {
        if (isset($_POST['add_category'])) {
            $stmt = $conn->prepare("INSERT INTO categories (name, image) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $image);
            if ($stmt->execute()) {
                $success = "Категория успешно добавлена.";
            } else {
                $error = "Ошибка при добавлении категории: " . $conn->error;
            }
        } elseif (isset($_POST['edit_category']) && $category_id > 0) {
            $stmt = $conn->prepare("UPDATE categories SET name = ?, image = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $image, $category_id);
            if ($stmt->execute()) {
                $success = "Категория успешно обновлена.";
            } else {
                $error = "Ошибка при обновлении категории: " . $conn->error;
            }
        }
        $stmt->close();
    }
}


$products = [];
$result = $conn->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id");
if ($result) {
    $products = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $error = "Ошибка при загрузке товаров: " . $conn->error;
}

$categories = [];
$result = $conn->query("SELECT * FROM categories");
if ($result) {
    $categories = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $error = "Ошибка при загрузке категорий: " . $conn->error;
}


$orders = [];
$result = $conn->query("SELECT o.*, u.name AS username FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
if ($result) {
    $orders = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $error = "Ошибка при загрузке заказов: " . $conn->error;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - Мистер Строй</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .admin-panel {
            padding: 20px;
        }
        .product-form, .product-list, .order-list, .category-form, .category-list {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .product-form label, .category-form label {
            display: block;
            margin: 10px 0 5px;
        }
        .product-form input, .product-form select, .product-form textarea, .category-form input, .category-form select {
            width: 100%;
            max-width: 400px;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .product-form button, .category-form button {
            padding: 10px 20px;
            background: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .product-form button:hover, .category-form button:hover {
            background: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #f4f4f4;
        }
        td a.delete {
            color: #ff4444;
            text-decoration: none;
            margin-right: 10px;
        }
        td a.delete:hover {
            text-decoration: underline;
        }
        .order-list select {
            padding: 5px;
            border-radius: 5px;
        }
        .order-list button {
            padding: 5px 10px;
            background: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .order-list button:hover {
            background: #0056b3;
        }
        .category-image {
            max-width: 100px;
            max-height: 100px;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <?php include 'components/header.php'; ?>
    <main class="admin-panel">
        <h2><i class="fas fa-user-shield"></i> Админ-панель</h2>
        <?php if ($success): ?>
            <p class="success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <!-- Форма добавления товара -->
        <div class="product-form">
            <h3>Добавить товар</h3>
            <form method="post" enctype="multipart/form-data">
                <label>Название</label>
                <input type="text" name="name" required>
                <label>Описание</label>
                <textarea name="description" required></textarea>
                <label>Цена</label>
                <input type="number" name="price" step="0.01" required>
                <label>Категория</label>
                <select name="category_id" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <label>Изображение</label>
                <input type="file" name="image" accept="image/jpeg,image/png,image/jpg">
                <button type="submit" name="add_product"><i class="fas fa-plus"></i> Добавить</button>
            </form>
        </div>

        <!-- Список товаров -->
        <div class="product-list">
            <h3>Список товаров</h3>
            <?php if (empty($products)): ?>
                <p><i class="fas fa-info-circle"></i> Товары отсутствуют.</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Описание</th>
                        <th>Цена</th>
                        <th>Категория</th>
                        <th>Изображение</th>
                        <th>Действия</th>
                    </tr>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['description']); ?></td>
                            <td><?php echo $product['price']; ?> руб.</td>
                            <td><?php echo htmlspecialchars($product['category_name'] ?: 'Без категории'); ?></td>
                            <td><?php if ($product['image'] && $product['image'] != 'default.jpg'): ?><img src="images/<?php echo htmlspecialchars($product['image']); ?>" class="category-image"><?php endif; ?></td>
                            <td><a href="admin.php?action=delete&id=<?php echo $product['id']; ?>" class="delete" onclick="return confirm('Удалить товар?');"><i class="fas fa-trash"></i> Удалить</a></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>

        <!-- Форма управления категориями -->
        <div class="category-form">
            <h3>Управление категориями</h3>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="category_id" id="category_id" value="0">
                <label>Название категории</label>
                <input type="text" name="name" id="category_name" required>
                <label>Изображение категории</label>
                <input type="file" name="category_image" accept="image/jpeg,image/png,image/jpg">
                <input type="hidden" name="current_image" id="current_image" value="default.jpg">
                <button type="submit" name="add_category"><i class="fas fa-plus"></i> Добавить категорию</button>
                <button type="submit" name="edit_category" style="margin-left: 10px;"><i class="fas fa-edit"></i> Обновить категорию</button>
            </form>
        </div>

        <!-- Список категорий -->
        <div class="category-list">
            <h3>Список категорий</h3>
            <?php if (empty($categories)): ?>
                <p><i class="fas fa-info-circle"></i> Категории отсутствуют.</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Изображение</th>
                        <th>Действия</th>
                    </tr>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?php echo $category['id']; ?></td>
                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                            <td><?php if ($category['image'] && $category['image'] != 'default.jpg'): ?><img src="images/<?php echo htmlspecialchars($category['image']); ?>" class="category-image"><?php else: ?>Нет изображения<?php endif; ?></td>
                            <td>
                                <a href="#" onclick="editCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>', '<?php echo htmlspecialchars($category['image']); ?>')" style="margin-right: 10px;">Редактировать</a>
                                <a href="admin.php?action=delete_category&id=<?php echo $category['id']; ?>" class="delete" onclick="return confirm('Удалить категорию?');"><i class="fas fa-trash"></i> Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>

       
        <div class="order-list">
            <h3>Управление заказами</h3>
            <?php if (empty($orders)): ?>
                <p><i class="fas fa-info-circle"></i> Заказы отсутствуют.</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Пользователь</th>
                        <th>Сумма</th>
                        <th>Адрес</th>
                        <th>Способ оплаты</th>
                        <th>Статус</th>
                        <th>Дата</th>
                        <th>Действия</th>
                    </tr>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['username'] ?: 'Гость'); ?></td>
                            <td><?php echo $order['total']; ?> руб.</td>
                            <td><?php echo htmlspecialchars($order['address']); ?></td>
                            <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="status">
                                        <option value="В обработке" <?php echo $order['status'] === 'В обработке' ? 'selected' : ''; ?>>В обработке</option>
                                        <option value="Отправлен" <?php echo $order['status'] === 'Отправлен' ? 'selected' : ''; ?>>Отправлен</option>
                                        <option value="Доставлен" <?php echo $order['status'] === 'Доставлен' ? 'selected' : ''; ?>>Доставлен</option>
                                    </select>
                                    <button type="submit" name="update_order_status">Обновить</button>
                                </form>
                            </td>
                            <td><?php echo $order['created_at']; ?></td>
                            <td>
                                <a href="order_details.php?id=<?php echo $order['id']; ?>">Подробнее</a>
                                <a href="admin.php?action=delete_order&order_id=<?php echo $order['id']; ?>" class="delete" onclick="return confirm('Удалить заказ?');"><i class="fas fa-trash"></i> Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
    </main>
    <?php include 'components/footer.php'; ?>
    <script>
        function editCategory(id, name, image) {
            document.getElementById('category_id').value = id;
            document.getElementById('category_name').value = name;
            document.getElementById('current_image').value = image;
            document.querySelector('button[name="edit_category"]').style.display = 'inline-block';
            document.querySelector('button[name="add_category"]').style.display = 'none';
        }
    </script>
</body>
</html>
<?php ob_end_flush(); ?>