<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$host = 'localhost';
$dbname = 'logowanie';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "Błąd: użytkownik nie istnieje.";
        exit();
    }
} catch (PDOException $e) {
    echo "Błąd połączenia: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <meta charset="UTF-8">
    <title>Profil użytkownika</title>
    <link rel="stylesheet" href="styles.css"> <!-- Załączanie zewnętrznego pliku CSS -->
</head>
<body>
    <div class="profile-container">
        <img src="<?= htmlspecialchars($user['photo']); ?>" alt="Zdjęcie profilowe" class="profile-photo">
        
        <h2>Witaj, <?php echo htmlspecialchars($user['username']); ?>!</h2>

        <h3>Dane profilu</h3>
        <p><strong>Wiek:</strong> <?php echo htmlspecialchars($user['age']); ?> lat</p>
        <p><strong>Lokalizacja:</strong> <?php echo htmlspecialchars($user['location']); ?></p>
        <p><strong>Zainteresowania:</strong> <?php echo nl2br(htmlspecialchars($user['interests'])); ?></p>
        <p><strong>Płeć:</strong> <?php echo htmlspecialchars($user['gender']); ?></p>


        <a href="update_profile.php">Edytuj profil</a><br>
        <a href="logout.php">Wyloguj się</a>
    </div>
</body>
</html>
