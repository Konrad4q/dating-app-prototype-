<?php
session_start();

// Połączenie z bazą danych
$host = 'localhost';
$dbname = 'logowanie';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Pobranie listy użytkowników z bazy danych
    $stmt = $pdo->query("SELECT id, username, age, interests, location, photo, gender FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pobranie danych zalogowanego użytkownika, jeśli jest zalogowany
    $loggedUser = null;
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT photo FROM users WHERE id = :id");
        $stmt->bindParam(':id', $_SESSION['user_id']);
        $stmt->execute();
        $loggedUser = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    echo "Błąd połączenia: " . $e->getMessage();
    exit();
}

// Sprawdzanie, czy użytkownik polubił kogoś
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['like'])) {
    $liked_user_id = $_POST['liked_user_id']; // ID osoby, którą użytkownik chce polubić
    $user_id = $_SESSION['user_id']; // ID zalogowanego użytkownika

    // Sprawdzenie, czy już polubił tę osobę
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE user_id = :user_id AND liked_user_id = :liked_user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':liked_user_id', $liked_user_id);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        // Jeśli nie polubił jeszcze tej osoby, zapisujemy to
        $stmt = $pdo->prepare("INSERT INTO likes (user_id, liked_user_id) VALUES (:user_id, :liked_user_id)");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':liked_user_id', $liked_user_id);
        $stmt->execute();
        echo "Polubiłeś tę osobę!";
    } else {
        echo "Już polubiłeś tę osobę.";
    }
}

// Wysyłanie wiadomości
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_message'])) {
    $message = $_POST['message']; // Treść wiadomości
    $receiver_id = $_POST['receiver_id']; // ID osoby, do której wysyłamy wiadomość
    $sender_id = $_SESSION['user_id']; // ID zalogowanego użytkownika

    // Zapisanie wiadomości do bazy
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (:sender_id, :receiver_id, :message)");
    $stmt->bindParam(':sender_id', $sender_id);
    $stmt->bindParam(':receiver_id', $receiver_id);
    $stmt->bindParam(':message', $message);
    $stmt->execute();

    echo "Wiadomość została wysłana!";
}
?>



<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Strona Główna</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Nagłówek z nawigacją -->
    <nav>
        <h1>Strona Główna</h1>
        <div class="menu">
            <?php if ($loggedUser): ?>
                <!-- Zalogowany użytkownik -->
                <div class="dropdown">
                    <img src="<?= htmlspecialchars($loggedUser['photo']); ?>" alt="Zdjęcie profilowe">
                    <a href="profile.php"><span class="material-icons icon">account_circle</span> Mój Profil</a> | 
                    <a href="logout.php"><span class="material-icons icon">logout</span> Wyloguj się</a>
                    <div class="dropdown-content">
                        <a href="profile.php">Mój profil</a>
                        <a href="update_profile.php">Edytuj profil</a>
                        <a href="logout.php">Wyloguj się</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Niezalogowany użytkownik -->
                <div class="dropdown">
                    <span class="material-icons icon">account_circle</span> Konto
                    <div class="dropdown-content">
                        <a href="login.html">Zaloguj się</a>
                        <a href="register.html">Załóż konto</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </nav>
<main>
<?php foreach ($users as $user): ?>
    <div class="card">
        <img src="<?= $user['photo']; ?>" alt="Zdjęcie użytkownika">
        <div class="username"><?= htmlspecialchars($user['username']); ?></div>
        <div class="details">
            <p><span>Wiek:</span> <?= htmlspecialchars($user['age']); ?> lat</p>
            <p><span>Zainteresowania:</span> <?= htmlspecialchars($user['interests']); ?></p>
            <p><span>Lokalizacja:</span> <?= htmlspecialchars($user['location']); ?></p>
            <p><span>Płeć:</span> <?= isset($user['gender']) && ($user['gender'] == 'Mężczyzna' || $user['gender'] == 'Kobieta') ? htmlspecialchars($user['gender']) : 'Brak danych'; ?></p>
            <form action="index.php" method="post">
                        <input type="hidden" name="liked_user_id" value="<?= $user['id']; ?>"> <!-- ID osoby, którą chcesz polubić -->
                        <button type="submit" name="like">Lubię to!</button>
                    </form>

                    <!-- Formularz wysyłania wiadomości -->
                    <form action="index.php" method="post">
                        <input type="hidden" name="receiver_id" value="<?= $user['id']; ?>"> <!-- ID osoby, do której wysyłasz wiadomość -->
                        <textarea name="message" placeholder="Napisz wiadomość" required></textarea>
                        <button type="submit" name="send_message">Wyślij wiadomość</button>
                    </form>



        </div>
    </div>
    <?php endforeach; ?>
</main>
    


</body>
</html>
