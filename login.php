<?php
require_once 'includes/db.php'; 

if (isset($_SESSION["user_id"])) {
    header("location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_form = trim($_POST['username']); 
    $password = $_POST['password'];

    if (empty($username_form) || empty($password)) {
        $error = "Wprowadź nazwę użytkownika i hasło.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username_form);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $username_db, $hashed_password); 
            if ($stmt->fetch()) {
                if (password_verify($password, $hashed_password)) {

                    $_SESSION["user_id"] = $id;
                    $_SESSION["username"] = $username_db; 
                    header("location: index.php");
                    exit;
                } else {
                    $error = "Nieprawidłowa nazwa użytkownika lub hasło.";
                }
            }
        } else {
            $error = "Nieprawidłowa nazwa użytkownika lub hasło.";
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
                <h2 class="card-title text-center mb-4 text-white">Logowanie</h2>
                 <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form action="login.php" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">Nazwa użytkownika</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Hasło</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-danger">Zaloguj się</button>
                    </div>
                </form>
                 <p class="text-center mt-3 text-white">Nie masz konta? <a href="register.php" class="text-danger">Zarejestruj się</a></p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>