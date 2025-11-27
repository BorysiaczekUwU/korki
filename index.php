<?php
include 'includes/header.php';


if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit;
}

$current_user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];


$my_notes_count_stmt = $conn->prepare("SELECT COUNT(id) as count FROM notes WHERE user_id = ?");
$my_notes_count_stmt->bind_param("i", $current_user_id);
$my_notes_count_stmt->execute();
$my_notes_count = $my_notes_count_stmt->get_result()->fetch_assoc()['count'];


$my_requests_count_stmt = $conn->prepare("SELECT COUNT(id) as count FROM tutoring_requests WHERE student_id = ?");
$my_requests_count_stmt->bind_param("i", $current_user_id);
$my_requests_count_stmt->execute();
$my_requests_count = $my_requests_count_stmt->get_result()->fetch_assoc()['count'];


$open_requests_count = $conn->query("SELECT COUNT(id) as count FROM tutoring_requests WHERE status = 'pending'")->fetch_assoc()['count'];


$latest_notes = $conn->query("SELECT n.id, n.title, n.created_at, u.username, p.profile_img 
    FROM notes n 
    JOIN users u ON n.user_id = u.id 
    JOIN profiles p ON u.id = p.user_id 
    ORDER BY n.created_at DESC LIMIT 5");


$tutoring_requests = $conn->query("SELECT t.id, t.subject, t.created_at, u.username, p.profile_img 
    FROM tutoring_requests t 
    JOIN users u ON t.student_id = u.id 
    JOIN profiles p ON u.id = p.user_id 
    WHERE t.status = 'pending' 
    ORDER BY t.created_at DESC LIMIT 5");
?>

<div class="mb-4">
    <h2 class="fw-bold">Witaj z powrotem, <?php echo htmlspecialchars($username); ?>!</h2>
    <p class="text-white">Oto przegląd aktywności w serwisie Korki.</p>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card h-100 p-3">
            <div class="card-body text-center">
                <i class="fas fa-book-open fa-3x text-danger mb-3"></i>
                <h3 class="card-title text-danger"><?php echo $my_notes_count; ?></h3>
                <p class="card-text text-white ">Twoich Notatek</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card h-100 p-3">
            <div class="card-body text-center">
                <i class="fas fa-question-circle fa-3x text-danger mb-3"></i>
                <h3 class="card-title text-danger"><?php echo $my_requests_count; ?></h3>
                <p class="card-text text-white">Twoich Zapytań</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card h-100 p-3">
            <div class="card-body text-center">
                <i class="fas fa-users fa-3x text-danger mb-3"></i>
                <h3 class="card-title text-danger"><?php echo $open_requests_count; ?></h3>
                <p class="card-text text-white">Czeka na Pomoc</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="activityTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active text-danger" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes" type="button" role="tab">Najnowsze Notatki</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-danger" id="requests-tab" data-bs-toggle="tab" data-bs-target="#requests" type="button" role="tab">Szukają Pomocy</button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="activityTabContent">
                    <div class="tab-pane fade show active" id="notes" role="tabpanel">
                        <?php if ($latest_notes->num_rows > 0): ?>
                            <ul class="list-group list-group-flush">
                                <?php while($note = $latest_notes->fetch_assoc()): ?>
                                <li class="list-group-item d-flex align-items-center bg-dark text-white">
                                    <img src="/korki/assets/uploads/avatars/<?php echo $note['profile_img']; ?>" class="rounded-circle me-3" width="40" height="40">
                                    <div class="flex-grow-1">
                                        <a href="notes.php#note-<?php echo $note['id']; ?>" class="text-white text-decoration-none stretched-link">
                                            <strong><?php echo htmlspecialchars($note['title']); ?></strong>
                                        </a>
                                        <small class="d-block text-white">przez <?php echo htmlspecialchars($note['username']); ?></small>
                                    </div>
                                    <small class="text-white"><?php echo date('d.m.Y', strtotime($note['created_at'])); ?></small>
                                </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <p>Brak dostępnych notatek.</p>
                        <?php endif; ?>
                    </div>
                    <div class="tab-pane fade" id="requests" role="tabpanel">
                        <?php if ($tutoring_requests->num_rows > 0): ?>
                             <ul class="list-group list-group-flush">
                                <?php while($request = $tutoring_requests->fetch_assoc()): ?>
                                 <li class="list-group-item d-flex align-items-center bg-dark text-white">
                                    <img src="/korki/assets/uploads/avatars/<?php echo $request['profile_img']; ?>" class="rounded-circle me-3" width="40" height="40">
                                    <div class="flex-grow-1">
                                         <a href="tutoring.php#request-<?php echo $request['id']; ?>" class="text-white text-decoration-none stretched-link">
                                            <strong><?php echo htmlspecialchars($request['subject']); ?></strong>
                                        </a>
                                        <small class="d-block text-white">od <?php echo htmlspecialchars($request['username']); ?></small>
                                    </div>
                                    <small class="text-white"><?php echo date('d.m.Y', strtotime($request['created_at'])); ?></small>
                                </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <p>Brak otwartych zapytań o pomoc.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
         <div class="card">
            <div class="card-header">
                <h5 class="mb-0 text-white"><i class="fas fa-bolt me-2 text-danger"></i>Szybkie Akcje</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="notes.php" class="btn btn-danger"><i class="fas fa-plus-circle me-2"></i>Dodaj nową notatkę</a>
                    <a href="tutoring.php" class="btn btn-outline-danger"><i class="fas fa-hands-helping me-2"></i>Poproś o pomoc</a>
                    <a href="profile.php" class="btn btn-outline-light mt-2"><i class="fas fa-user-circle me-2"></i>Przejdź do profilu</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>