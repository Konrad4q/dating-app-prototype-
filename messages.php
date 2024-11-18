<?php
session_start();
$host = 'localhost';
$dbname = 'logowanie';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $current_user_id = $_SESSION['user_id'];
    $chat_user_id = $_GET['chat_user_id']; // ID użytkownika, z którym czatujemy

    // Pobieranie wiadomości między zalogowanym użytkownikiem a wybranym użytkownikiem
    $stmt = $pdo->prepare("
        SELECT sender_id, message, timestamp 
        FROM messages 
        WHERE (sender_id = :current_user_id AND receiver_id = :chat_user_id) 
           OR (sender_id = :chat_user_id AND receiver_id = :current_user_id) 
        ORDER BY timestamp ASC
    ");
    $stmt->bindParam(':current_user_id', $current_user_id);
    $stmt->bindParam(':chat_user_id', $chat_user_id);
    $stmt->execute();

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($messages); // Zwracanie wiadomości w formacie JSON
} catch (PDOException $e) {
    echo "Błąd połączenia: " . $e->getMessage();
}
?>