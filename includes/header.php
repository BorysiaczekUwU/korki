<?php 
require_once 'db.php'; 


$user_avatar = 'default_avatar.png'; 
if (isset($_SESSION['user_id'])) {
    $current_user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT profile_img FROM profiles WHERE user_id = ?");
    $stmt->bind_param("i", $current_user_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user_data = $result->fetch_assoc();
            $user_avatar = $user_data['profile_img'];
        }
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Korki</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/korki/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/korki/index.php">
                <i class="fas fa-graduation-cap me-2"></i>Korki
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="/korki/index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/korki/notes.php">Notatki</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/korki/search.php">Społeczność</a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link" href="/korki/tutoring.php">Korepetycje</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="/korki/assets/uploads/avatars/<?php echo htmlspecialchars($user_avatar); ?>" alt="Avatar" class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
                                <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li>
                                    <a class="dropdown-item" href="/korki/profile.php?id=<?php echo $_SESSION['user_id']; ?>">
                                        <i class="fas fa-user-circle me-2"></i>Mój profil
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/korki/edit_profile.php">
                                        <i class="fas fa-cog me-2"></i>Ustawienia
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="/korki/logout.php">
                                        <i class="fas fa-sign-out-alt me-2"></i>Wyloguj
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/korki/login.php">Logowanie</a>
                        </li>
                        <li class="nav-item">
                            <a href="/korki/register.php" class="btn btn-danger">Dołącz teraz</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container main-container">