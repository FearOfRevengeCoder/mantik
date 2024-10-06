<?php
require 'rcon.php';

// Настройки базы данных
$host = 'localhost';
$user = 'root';
$password = 'dimastar12345';
$database = 'mcauth';

// Подключение к базе данных
$conn = new mysqli($host, $user, $password, $database);

// Проверка подключения
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $player_id = $_POST['player_id'];
    $password = $_POST['password'];
    $promo_code = $_POST['promo_code'];

    // Поиск игрока в базе данных
    $stmt = $conn->prepare("SELECT password_hash FROM mc_auth_accounts WHERE player_id = ?");
    $stmt->bind_param("s", $player_id);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($password_hash);
        $stmt->fetch();

        // Проверка пароля
        if (password_verify($password, $password_hash)) {
            // Проверка промокода
            if ($promo_code === 'test') {
                // Выполнение RCON запроса
                try {
                    $rcon = new MinecraftRcon('localhost', 25678, 'dimastar12345');
                    $rcon->connect();
                    $response = $rcon->sendCommand('bc test');
                    $rcon->disconnect();

                    echo "Команда выполнена успешно: " . $response;
                } catch (Exception $e) {
                    echo "Ошибка RCON: " . $e->getMessage();
                }
            } else {
                echo "Неверный промокод.";
            }
        } else {
            echo "Неверный пароль.";
        }
    } else {
        echo "Игрок не найден.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Промокоды</title>
    <link rel="stylesheet" href="1.css">
</head>
<body>
    <div class="container">
        <h1>Проверка промокода</h1>
        <form method="POST" action="">
            <label for="player_id">Ник игрока:</label>
            <input type="text" id="player_id" name="player_id" required><br><br>

            <label for="password">Пароль игрока:</label>
            <input type="password" id="password" name="password" required><br><br>

            <label for="promo_code">Промокод:</label>
            <input type="text" id="promo_code" name="promo_code" required><br><br>

            <button type="submit">Отправить</button>
        </form>
    </div>
</body>
</html>
