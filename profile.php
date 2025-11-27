<?php
include 'includes/header.php';


if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit;
}

$current_user_id = $_SESSION['user_id'];

$profile_user_id = isset($_GET['id']) ? intval($_GET['id']) : $current_user_id;


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_note'])) {
    $note_id_to_delete = $_POST['note_id'];
    $stmt = $conn->prepare("SELECT user_id, file_path FROM notes WHERE id = ?");
    $stmt->bind_param("i", $note_id_to_delete);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $note = $result->fetch_assoc();
        if ($note['user_id'] == $current_user_id) {
            if (!empty($note['file_path'])) {
                $file_on_server = $_SERVER['DOCUMENT_ROOT'] . '/korki/assets/uploads/notes/' . $note['file_path'];
                if (file_exists($file_on_server)) {
                    unlink($file_on_server);
                }
            }
            $delete_stmt = $conn->prepare("DELETE FROM notes WHERE id = ?");
            $delete_stmt->bind_param("i", $note_id_to_delete);
            $delete_stmt->execute();
            $delete_stmt->close();
            header("Location: profile.php?id=" . $profile_user_id);
            exit;
        }
    }
    $stmt->close();
}


$stmt = $conn->prepare("SELECT u.username, u.email, p.bio, p.skills_good, p.skills_bad, p.portfolio_link, p.profile_img, p.banner_img FROM users u JOIN profiles p ON u.id = p.user_id WHERE u.id = ?");
$stmt->bind_param("i", $profile_user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "<div class='alert alert-danger'>Nie znaleziono profilu.</div>";
    include 'includes/footer.php';
    exit;
}
$user = $result->fetch_assoc();
$stmt->close();


$notes_stmt = $conn->prepare("SELECT id, title, content, file_path, created_at FROM notes WHERE user_id = ? ORDER BY created_at DESC");
$notes_stmt->bind_param("i", $profile_user_id);
$notes_stmt->execute();
$notes_result = $notes_stmt->get_result();
?>

<div class="card text-white mt-4 border-0">
    <div class="profile-banner" style="background-image: url('/korki/assets/uploads/banners/<?php echo htmlspecialchars($user['banner_img']); ?>'); border-radius: .375rem;"></div>
    <div class="card-body">
        <div class="row align-items-end">
            <div class="col-auto">
                <img src="/korki/assets/uploads/avatars/<?php echo htmlspecialchars($user['profile_img']); ?>" alt="Avatar" class="profile-avatar">
            </div>
            <div class="col">
                <h2 class="card-title mb-0"><?php echo htmlspecialchars($user['username']); ?></h2>
                <p class="text-white mb-1"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <div class="col-auto">
                <?php if ($profile_user_id === $current_user_id): ?>
                    <a href="edit_profile.php" class="btn btn-outline-danger"><i class="fas fa-pencil-alt me-2"></i>Edytuj profil</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<hr>

<div class="row mt-4">

    <div class="col-lg-4">
        <div class="card p-3">
            <div class="card-body">
                <h5 class="text-danger"><i class="fas fa-user-circle me-2"></i>Bio</h5>
                <p class="text-white-50"><?php echo !empty($user['bio']) ? nl2br(htmlspecialchars($user['bio'])) : 'Użytkownik nie dodał jeszcze swojego bio.'; ?></p>
                <hr>

                <h5 class="text-success mt-4"><i class="fas fa-thumbs-up me-2"></i>Mocne strony</h5>
                <div>
                    <?php
                    if (!empty($user['skills_good'])) {
                        $skills_good = explode(',', $user['skills_good']);
                        foreach ($skills_good as $skill) {
                            echo '<span class="badge bg-success me-1 mb-1">' . htmlspecialchars(trim($skill)) . '</span>';
                        }
                    } else {
                        echo '<p class="text-white-50 small">Brak informacji.</p>';
                    }
                    ?>
                </div>

                <h5 class="text-warning mt-4"><i class="fas fa-thumbs-down me-2"></i>Potrzebuję pomocy z</h5>
                <div>
                     <?php
                    if (!empty($user['skills_bad'])) {
                        $skills_bad = explode(',', $user['skills_bad']);
                        foreach ($skills_bad as $skill) {
                            echo '<span class="badge bg-warning text-dark me-1 mb-1">' . htmlspecialchars(trim($skill)) . '</span>';
                        }
                    } else {
                        echo '<p class="text-white-50 small">Brak informacji.</p>';
                    }
                    ?>
                </div>
                <hr>

                <?php if (!empty($user['portfolio_link'])): ?>
                <h5 class="text-danger mt-4"><i class="fas fa-link me-2"></i>Portfolio</h5>
                <a href="<?php echo htmlspecialchars($user['portfolio_link']); ?>" target="_blank" class="text-break"><?php echo htmlspecialchars($user['portfolio_link']); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <h3 class="mb-4"><i class="fas fa-thumbtack me-2 text-danger"></i>Udostępnione notatki (<?php echo $notes_result->num_rows; ?>)</h3>
        
        <?php if ($notes_result->num_rows > 0): ?>
            <?php while ($note = $notes_result->fetch_assoc()): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title text-danger mb-1"><?php echo htmlspecialchars($note['title']); ?></h5>
                             <small class="text-white"><?php echo date('d.m.Y', strtotime($note['created_at'])); ?></small>
                        </div>
                        <p class="card-text mt-2 text-white"><?php echo nl2br(htmlspecialchars($note['content'])); ?></p>
                        
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <?php if ($note['file_path']): ?>
                                    <a href="/korki/assets/uploads/notes/<?php echo htmlspecialchars($note['file_path']); ?>" class="btn btn-sm btn-outline-light" target="_blank" download><i class="fas fa-download me-1"></i>Pobierz plik</a>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($profile_user_id === $current_user_id): ?>
                                <form method="POST" action="profile.php?id=<?php echo $profile_user_id; ?>" onsubmit="return confirm('Czy na pewno chcesz usunąć tę notatkę?');">
                                    <input type="hidden" name="note_id" value="<?php echo $note['id']; ?>">
                                    <button type="submit" name="delete_note" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="card p-4 text-center">
                <p class="mb-0">Użytkownik nie udostępnił jeszcze żadnych notatek.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$notes_stmt->close();
include 'includes/footer.php';
?>