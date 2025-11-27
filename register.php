<?php
require_once 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Walidacja
    if (empty($username) || empty($email) || empty($password) || $password !== $confirm_password) {
        $error = "Wypełnij wszystkie pola poprawnie.";
    } elseif (strlen($password) < 6) {
        $error = "Hasło musi mieć co najmniej 6 znaków.";
    } else {
        // Sprawdzenie czy użytkownik istnieje
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Użytkownik o tej nazwie lub emailu już istnieje.";
        } else {
            // Hashowanie hasła
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Dodanie użytkownika
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;
                // Utworzenie domyślnego profilu
                $stmt_profile = $conn->prepare("INSERT INTO profiles (user_id) VALUES (?)");
                $stmt_profile->bind_param("i", $user_id);
                $stmt_profile->execute();
                $stmt_profile->close();

                header("location: login.php");
                exit;
            } else {
                $error = "Coś poszło nie tak. Spróbuj ponownie.";
            }
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card mt-5">
            <div class="card-body">
                <h2 class="card-title text-center mb-4 text-white">Rejestracja</h2>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form action="register.php" method="post" id="registerForm">
                    <div class="mb-3">
                        <label for="username" class="form-label">Nazwa użytkownika</label>
                        <input type="text" name="username" id="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Hasło</label>
                        <input type="password" name="password" id="password" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Potwierdź hasło</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-danger">Zarejestruj się</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>