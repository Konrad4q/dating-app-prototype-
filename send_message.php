<?php
session_start();
$host = 'localhost';
$dbname = 'logowanie';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sender_id = $_SESSION['user_id'];
    $receiver_id = $_POST['receiver_id']; // ID użytkownika, do którego wysyłamy wiadomość
    $message = $_POST['message'];

    // Wstawienie wiadomości do bazy danych
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (:sender_id, :receiver_id, :message)");
    $stmt->bindParam(':sender_id', $sender_id);
    $stmt->bindParam(':receiver_id', $receiver_id);
    $stmt->bindParam(':message', $message);
    $stmt->execute();

    echo "Wiadomość wysłana!";
} catch (PDOException $e) {
    echo "Błąd połączenia: " . $e->getMessage();
}
