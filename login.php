<?php
session_start();

$host = 'localhost';
$dbname = 'logowanie';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Pobranie danych z formularza
        $login_input = $_POST['login_input'];
        $password = $_POST['password'];

        // Przygotowanie zapytania - sprawdzenie loginu, e-maila lub numeru telefonu
        $stmt = $pdo->prepare("
            SELECT * FROM users 
            WHERE (username = :login_input OR email = :login_input OR phone = :login_input) 
            LIMIT 1
        ");
        $stmt->bindParam(':login_input', $login_input);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Weryfikacja hasła
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: profile.php"); // Przekierowanie do profilu
            exit();
        } else {
            echo "Nieprawidłowy login lub hasło.";
        }
    }
} catch (PDOException $e) {
    echo "Błąd połączenia: " . $e->getMessage();
}
?>
