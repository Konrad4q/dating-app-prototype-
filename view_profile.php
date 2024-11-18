<?php
session_start();

if (!isset($_GET['id'])) {
    echo "Błąd: brak identyfikatora użytkownika.";
    exit();
}

$userId = $_GET['id'];

// Połączenie z bazą danych
$host = 'localhost';
$dbname = 'logowanie';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Pobranie szczegółowych informacji o użytkowniku
    $stmt = $pdo->prepare("SELECT username, age, interests, location, photo FROM users WHERE id = :id");
    $stmt->bindParam(':id', $userId);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "Błąd: użytkownik nie istnieje.";
        exit();
    }
} catch (PDOException $e) {
    echo "Błąd połączenia: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Profil użytkownika</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<!-- Fragment view_profile.php, wyświetlanie szczegółów profilu -->
<div class="profile">
    <img src="<?= htmlspecialchars($user['photo']); ?>" alt="Zdjęcie profilowe" style="width: 150px; height: 150px; border-radius: 50%;">
    <h2><?= htmlspecialchars($user['username']); ?></h2>
    <p><strong>Płeć:</strong> <?= htmlspecialchars($user['gender'] === 'man' ? 'Mężczyzna' : 'Kobieta'); ?></p>
    <p><strong>Wiek:</strong> <?= htmlspecialchars($user['age']); ?> lat</p>
    <p><strong>Zainteresowania:</strong> <?= htmlspecialchars($user['interests']); ?></p>
    <p><strong>Lokalizacja:</strong> <?= htmlspecialchars($user['location']); ?></p>
</div>

</body>
</html>
