<?php
$host = 'localhost';
$dbname = 'ordersnew';
$user = 'root';
$password = '';
$conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);


// Проверяем, что запрос был отправлен методом POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получаем данные из формы
    $order_id = htmlspecialchars(trim($_POST['order_id']));
    $reason = isset($_POST['reason']) ? htmlspecialchars(trim($_POST['reason'])) : '';

    // Проверка наличия номера заказа
    if (empty($order_id)) {
        echo "Номер заказа обязателен.";
    } else {
        try {
            // Подключаемся к базе данных (используя PDO)
            $pdo = new PDO('mysql:host=localhost;dbname=ordersnew', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Проверка, существует ли заказ и его текущий статус
            $stmt = $pdo->prepare("SELECT status FROM ordersnew WHERE order_id = :order_id");
            $stmt->bindParam(':order_id', $order_id);
            $stmt->execute();

            // Получаем статус заказа
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($order) {
                if ($order['status'] !== 'cancelled') {
                    // Обновляем статус заказа на "Отменен"
                    $update_stmt = $pdo->prepare("UPDATE ordersnew SET status = 'cancelled', cancel_reason = :reason WHERE order_id = :order_id");
                    $update_stmt->bindParam(':reason', $reason);
                    $update_stmt->bindParam(':order_id', $order_id);

                    if ($update_stmt->execute()) {
                        echo "Заказ успешно отменен.";
                    } else {
                        echo "Ошибка при отмене заказа.";
                    }
                } else {
                    echo "Заказ уже был отменен ранее.";
                }
            } else {
                echo "Заказ с указанным номером не найден.";
            }
        } catch (PDOException $e) {
            echo "Ошибка подключения к базе данных: " . $e->getMessage();
        }
    }
} else {
    echo "Неверный метод запроса.";
}