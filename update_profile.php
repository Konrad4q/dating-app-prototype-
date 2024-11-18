<?php
session_start();

// Sprawdzenie, czy użytkownik jest zalogowany
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

    // Pobranie danych użytkownika z bazy
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Przetwarzanie formularza
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $location = $_POST['location'];
        $interests = $_POST['interests'];

        // Przetwarzanie zdjęcia
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $targetDir = "uploads/"; // Upewnij się, że ten katalog istnieje i jest zapisywalny
            $targetFile = $targetDir . basename($_FILES["photo"]["name"]);
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

            // Sprawdź, czy przesłany plik jest obrazem
            $check = getimagesize($_FILES["photo"]["tmp_name"]);
            if ($check !== false) {
                if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile)) {
                    // Zapisz ścieżkę do zdjęcia w bazie
                    $stmt = $pdo->prepare("UPDATE users SET location = :location, interests = :interests, photo = :photo WHERE id = :id");
                    $stmt->bindParam(':photo', $targetFile);
                } else {
                    echo "Wystąpił błąd podczas przesyłania zdjęcia.";
                }
            } else {
                echo "Plik nie jest prawidłowym obrazem.";
            }
        } else {
            // Aktualizacja bez zdjęcia
            $stmt = $pdo->prepare("UPDATE users SET location = :location, interests = :interests WHERE id = :id");
        }

        // Ustawianie pozostałych danych
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':interests', $interests);
        $stmt->bindParam(':id', $_SESSION['user_id']);
        $stmt->execute();

        // Po pomyślnej aktualizacji przekieruj do profilu
        header("Location: profile.php");
        exit();
    }
} catch (PDOException $e) {
    echo "Błąd połączenia: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Edytuj profil</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        /* Globalne ustawienia */
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Nagłówek */
        h2 {
            text-align: center;
            color: #333;
        }

        /* Formularz */
        form {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            display: flex;
            flex-direction: column;
        }

        /* Etykiety i inputy */
        label {
            font-size: 14px;
            margin-bottom: 6px;
            color: #333;
        }

        input[type="text"],
        input[type="file"],
        textarea {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        textarea {
            resize: vertical;
        }

        /* Przycisk */
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        /* Komunikat o błędzie */
        p {
            font-size: 16px;
            font-weight: bold;
        }

        /* Ikony */
        .icon {
            vertical-align: middle;
            margin-right: 8px;
        }

        /* Media Queries dla mniejszych ekranów */
        @media (max-width: 768px) {
            form {
                width: 90%;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <h2>Edytuj profil</h2>
    <form action="update_profile.php" method="post" enctype="multipart/form-data">
        <label for="location">Lokalizacja:</label>
        <input type="text" id="location" name="location" value="<?= htmlspecialchars($user['location']); ?>" required><br><br>

        <label for="interests">Zainteresowania:</label>
        <textarea id="interests" name="interests" required><?= htmlspecialchars($user['interests']); ?></textarea><br><br>

        <label for="photo">Zdjęcie profilowe:</label>
        <input type="file" id="photo" name="photo"><br><br>

        <input type="submit" value="Zaktualizuj profil">
    </form>
</body>
</html>
