<?php
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Obsługa formularza
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Pobranie danych z formularza
    $bio = $_POST['bio'];
    $skills_good = $_POST['skills_good'];
    $skills_bad = $_POST['skills_bad'];
    $portfolio_link = $_POST['portfolio_link'];

    // Aktualizacja danych tekstowych
    $stmt = $conn->prepare("UPDATE profiles SET bio = ?, skills_good = ?, skills_bad = ?, portfolio_link = ? WHERE user_id = ?");
    $stmt->bind_param("ssssi", $bio, $skills_good, $skills_bad, $portfolio_link, $user_id);
    $stmt->execute();
    $stmt->close();
    
    // Obsługa przesyłania zdjęcia profilowego
    if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] == 0) {
        $target_dir = "assets/uploads/avatars/";
        $filename = uniqid() . basename($_FILES["profile_img"]["name"]);
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($_FILES["profile_img"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("UPDATE profiles SET profile_img = ? WHERE user_id = ?");
            $stmt->bind_param("si", $filename, $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Obsługa przesyłania baneru
    if (isset($_FILES['banner_img']) && $_FILES['banner_img']['error'] == 0) {
        $target_dir = "assets/uploads/banners/";
        $filename = uniqid() . basename($_FILES["banner_img"]["name"]);
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($_FILES["banner_img"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("UPDATE profiles SET banner_img = ? WHERE user_id = ?");
            $stmt->bind_param("si", $filename, $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    header("location: profile.php"); // Przekierowanie z powrotem na profil
    exit;
}

// Pobranie aktualnych danych profilu
$stmt = $conn->prepare("SELECT bio, skills_good, skills_bad, portfolio_link FROM profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();

?>

<h2 class="mt-4">Edytuj swój profil</h2>
<div class="card mt-4">
    <div class="card-body">
        <form action="edit_profile.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="bio" class="form-label">Bio</label>
                <textarea name="bio" id="bio" class="form-control" rows="3"><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
            </div>
             <div class="mb-3">
                <label for="skills_good" class="form-label">W czym jesteś dobry (oddziel przecinkami)</label>
                <input type="text" name="skills_good" id="skills_good" class="form-control" value="<?php echo htmlspecialchars($profile['skills_good'] ?? ''); ?>">
            </div>
             <div class="mb-3">
                <label for="skills_bad" class="form-label">W czym potrzebujesz pomocy (oddziel przecinkami)</label>
                <input type="text" name="skills_bad" id="skills_bad" class="form-control" value="<?php echo htmlspecialchars($profile['skills_bad'] ?? ''); ?>">
            </div>
             <div class="mb-3">
                <label for="portfolio_link" class="form-label">Link do portfolio</label>
                <input type="url" name="portfolio_link" id="portfolio_link" class="form-control" value="<?php echo htmlspecialchars($profile['portfolio_link'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="profile_img" class="form-label">Zmień zdjęcie profilowe</label>
                <input type="file" name="profile_img" id="profile_img" class="form-control" accept="image/*">
            </div>
             <div class="mb-3">
                <label for="banner_img" class="form-label">Zmień baner</label>
                <input type="file" name="banner_img" id="banner_img" class="form-control" accept="image/*">
            </div>
            <button type="submit" class="btn btn-danger">Zapisz zmiany</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>