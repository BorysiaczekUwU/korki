<?php
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_note'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $file_path = null;

    if (!empty($title)) {
        if (isset($_FILES['note_file']) && $_FILES['note_file']['error'] == 0) {
            $target_dir = "assets/uploads/notes/";
            $filename = uniqid() . '_' . basename($_FILES["note_file"]["name"]);
            $target_file = $target_dir . $filename;
            $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_types = ['pdf', 'doc', 'docx', 'txt', 'png', 'jpg', 'jpeg', 'gif', 'zip', 'rar', 'html', 'css', 'js', 'php', 'sql', 'md'];
            
            if (in_array($file_type, $allowed_types)) {
                if (move_uploaded_file($_FILES["note_file"]["tmp_name"], $target_file)) {
                    $file_path = $filename;
                }
            }
        }
        
        $stmt = $conn->prepare("INSERT INTO notes (user_id, title, content, file_path) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $title, $content, $file_path);
        $stmt->execute();
        $stmt->close();
        header("location: notes.php");
        exit;
    }
}


$search_query = "";
$sql = "SELECT n.id, n.title, n.content, n.file_path, n.created_at, u.id as user_id, u.username, p.profile_img 
        FROM notes n 
        JOIN users u ON n.user_id = u.id
        JOIN profiles p ON u.id = p.user_id";

if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $search_query = trim($_GET['q']);
    $sql .= " WHERE n.title LIKE ?";
    $sql .= " ORDER BY n.created_at DESC";
    $stmt = $conn->prepare($sql);
    $like_query = "%" . $search_query . "%";
    $stmt->bind_param("s", $like_query);
} else {
    $sql .= " ORDER BY n.created_at DESC";
    $stmt = $conn->prepare($sql);
}
$stmt->execute();
$notes_result = $stmt->get_result();

?>

<div class="mb-4">
    <h2 class="fw-bold"><i class="fas fa-book-open text-danger me-2"></i>Baza Wiedzy</h2>
    <p class="text-white">Przeglądaj notatki udostępnione przez innych lub dodaj coś od siebie.</p>
</div>

<div class="row">

    <div class="col-lg-8">
        <div class="row">
            <?php if ($notes_result && $notes_result->num_rows > 0): ?>
                <?php while ($note = $notes_result->fetch_assoc()): ?>
                    <div class="col-md-6 mb-4" id="note-<?php echo $note['id']; ?>">
                        <div class="card h-100">
                            <?php
                                $file_extension = $note['file_path'] ? strtolower(pathinfo($note['file_path'], PATHINFO_EXTENSION)) : '';
                                $image_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                                if (in_array($file_extension, $image_extensions)):
                            ?>
                                <img src="/korki/assets/uploads/notes/<?php echo htmlspecialchars($note['file_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($note['title']); ?>" style="height: 200px; object-fit: cover;">
                            <?php endif; ?>

                            <div class="card-body">
                                <h5 class="card-title text-danger"><?php echo htmlspecialchars($note['title']); ?></h5>
                                <p class="card-text text-white-50"><?php echo nl2br(htmlspecialchars($note['content'])); ?></p>
                            </div>

                            <div class="card-footer d-flex justify-content-between align-items-center">
                                <a href="profile.php?id=<?php echo $note['user_id']; ?>" class="text-decoration-none d-flex align-items-center">
                                    <img src="/korki/assets/uploads/avatars/<?php echo htmlspecialchars($note['profile_img']); ?>" class="rounded-circle me-2" width="30" height="30">
                                    <small class="text-white"><?php echo htmlspecialchars($note['username']); ?></small>
                                </a>
                                <?php if ($note['file_path'] && !in_array($file_extension, $image_extensions)): ?>
                                    <a href="/korki/assets/uploads/notes/<?php echo htmlspecialchars($note['file_path']); ?>" class="btn btn-sm btn-outline-danger" download>
                                        <i class="fas fa-download"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col">
                    <div class="card p-4 text-center">
                        <p class="mb-0">Nie znaleziono notatek pasujących do Twojego zapytania.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header text-white"><h5 class="mb-0"><i class="fas fa-search me-2 text-danger"></i>Wyszukaj</h5></div>
            <div class="card-body">
                <form action="notes.php" method="get">
                    <div class="input-group">
                        <input type="text" name="q" class="form-control" placeholder="Tytuł notatki..." value="<?php echo htmlspecialchars($search_query); ?>">
                        <button class="btn btn-danger" type="submit">Szukaj</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
             <div class="card-header">
                <h5 class="mb-0">
                    <a href="#addNoteCollapse" data-bs-toggle="collapse" role="button" class="text-white text-decoration-none d-block">
                       <i class="fas fa-plus-circle me-2 text-danger"></i>Opublikuj notatkę
                    </a>
                </h5>
            </div>
            <div class="collapse" id="addNoteCollapse">
                <div class="card-body">
                    <form action="notes.php" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Tytuł</label>
                            <input type="text" name="title" id="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">Treść</label>
                            <textarea name="content" id="content" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="note_file" class="form-label">Załącz plik</label>
                            <input type="file" name="note_file" id="note_file" class="form-control">
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="add_note" class="btn btn-danger">Opublikuj</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$stmt->close();
include 'includes/footer.php'; 
?>