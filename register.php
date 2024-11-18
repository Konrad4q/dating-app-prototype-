<?php
$host = 'localhost';
$dbname = 'logowanie';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $gender = $_POST['gender']; // Pobranie wyboru płci z formularza
        $phone = $_POST['phone'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $age = $_POST['age'];  // Pobranie wieku

        // Sprawdzenie, czy hasła się zgadzają
        if ($password !== $confirm_password) {
            echo "Hasła się nie zgadzają!";
            exit();
        }

        // Sprawdzanie, czy użytkownik ma co najmniej 18 lat
        if ($age < 18) {
            echo "Musisz mieć co najmniej 18 lat, aby zarejestrować się.";
            exit();
        }

        // Sprawdzenie, czy użytkownik lub e-mail już istnieje
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username OR email = :email OR phone = :phone");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            echo "Nazwa użytkownika, e-mail lub numer telefonu już istnieje!";
            exit();
        }

        // Szyfrowanie hasła
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Wstawienie nowego użytkownika do bazy
        $stmt = $pdo->prepare("INSERT INTO users (username, email, gender, phone, password, age) VALUES (:username, :email, :gender, :phone, :password, :age)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':gender', $gender); // Zapisanie wybranej płci
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':age', $age); // Zapisanie wieku
        $stmt->execute();

        echo "Rejestracja przebiegła pomyślnie!";
        header("Location: login.html");
        exit();
    }
} catch (PDOException $e) {
    echo "Błąd połączenia: " . $e->getMessage();
}

// Sprawdzanie, czy hasło spełnia wymagania
$password = $_POST['password'];
if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
    echo "Hasło musi zawierać przynajmniej 8 znaków, w tym jedną wielką literę, cyfrę i znak specjalny.";
    exit();
}

// Sprawdzanie, czy użytkownik zgodził się na regulamin
if (!isset($_POST['terms'])) {
    echo "Musisz zgodzić się na regulamin i politykę prywatności!";
    exit();
}
?>
