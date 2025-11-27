<?php
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit;
}
$current_user_id = $_SESSION['user_id'];


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_request'])) {
        $subject = trim($_POST['subject']);
        $description = trim($_POST['description']);
        $proposed_date = $_POST['proposed_date'];
        if (!empty($subject) && !empty($description) && !empty($proposed_date)) {
            $stmt = $conn->prepare("INSERT INTO tutoring_requests (student_id, subject, description, proposed_date) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $current_user_id, $subject, $description, $proposed_date);
            $stmt->execute();
            header("Location: tutoring.php");
            exit;
        }
    }
    if (isset($_POST['update_request'])) {
        $request_id = $_POST['request_id'];
        $status = $_POST['status'];
        $check_stmt = $conn->prepare("SELECT status FROM tutoring_requests WHERE id = ?");
        $check_stmt->bind_param("i", $request_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result()->fetch_assoc();
        if ($result['status'] === 'pending') {
            $stmt = $conn->prepare("UPDATE tutoring_requests SET status = ?, tutor_id = ? WHERE id = ?");
            $stmt->bind_param("sii", $status, $current_user_id, $request_id);
            $stmt->execute();
            header("Location: tutoring.php");
            exit;
        }
    }
}


$open_requests_stmt = $conn->prepare("
    SELECT t.*, u.username as student_username, p.profile_img as student_avatar 
    FROM tutoring_requests t 
    JOIN users u ON t.student_id = u.id 
    JOIN profiles p ON u.id = p.user_id 
    WHERE t.status = 'pending' AND t.student_id != ? 
    ORDER BY t.created_at DESC");
$open_requests_stmt->bind_param("i", $current_user_id);
$open_requests_stmt->execute();
$open_requests_result = $open_requests_stmt->get_result();


$my_requests_stmt = $conn->prepare("
    SELECT t.*, u.username as tutor_username, p.profile_img as tutor_avatar 
    FROM tutoring_requests t 
    LEFT JOIN users u ON t.tutor_id = u.id 
    LEFT JOIN profiles p ON u.id = p.user_id 
    WHERE t.student_id = ? 
    ORDER BY t.created_at DESC");
$my_requests_stmt->bind_param("i", $current_user_id);
$my_requests_stmt->execute();
$my_requests_result = $my_requests_stmt->get_result();


$my_jobs_stmt = $conn->prepare("
    SELECT t.*, u.username as student_username, u.email as student_email, p.profile_img as student_avatar 
    FROM tutoring_requests t 
    JOIN users u ON t.student_id = u.id 
    JOIN profiles p ON u.id = p.user_id 
    WHERE t.tutor_id = ? 
    ORDER BY t.created_at DESC");
$my_jobs_stmt->bind_param("i", $current_user_id);
$my_jobs_stmt->execute();
$my_jobs_result = $my_jobs_stmt->get_result();
?>

<div class="mb-4">
    <h2 class="fw-bold"><i class="fas fa-hands-helping text-danger me-2"></i>Centrum Korepetycji</h2>
    <p class="text-white">Znajdź pomoc, której potrzebujesz, lub zaoferuj swoje wsparcie innym.</p>
</div>

<div class="row">

    <div class="col-lg-8">
        <div class="card">
<div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="tutoringTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#open-requests" type="button">Otwarte zapytania</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#my-requests" type="button">Moje zapytania</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#my-jobs" type="button">Udzielam pomocy</button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="tutoringTabsContent">
                    <div class="tab-pane fade show active" id="open-requests" role="tabpanel">
                        <?php if ($open_requests_result->num_rows > 0): while($req = $open_requests_result->fetch_assoc()): ?>
                        <div class="card mb-3" id="request-<?php echo $req['id']; ?>">
                            <div class="card-body">
                                <h5 class="card-title text-danger"><?php echo htmlspecialchars($req['subject']); ?></h5>
                                <p class="card-text text-white-50"><?php echo htmlspecialchars($req['description']); ?></p>
                                <form action="tutoring.php" method="post" class="mt-3">
                                    <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                    <button type="submit" name="update_request" value="accepted" class="btn btn-success"><i class="fas fa-check-circle me-2"></i>Akceptuj i pomóż</button>
                                </form>
                            </div>
                            <div class="card-footer d-flex justify-content-between align-items-center">
                                <a href="profile.php?id=<?php echo $req['student_id']; ?>" class="text-decoration-none d-flex align-items-center">
                                    <img src="/korki/assets/uploads/avatars/<?php echo htmlspecialchars($req['student_avatar']); ?>" class="rounded-circle me-2" width="30" height="30">
                                    <small class="text-white"><?php echo htmlspecialchars($req['student_username']); ?></small>
                                </a>
                                <small class="text-muted"><i class="far fa-calendar-alt me-2"></i><?php echo date('d.m.Y H:i', strtotime($req['proposed_date'])); ?></small>
                            </div>
                        </div>
                        <?php endwhile; else: ?>
                        <p>Brak otwartych zapytań o pomoc.</p>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade" id="my-requests" role="tabpanel">
                        <?php if ($my_requests_result->num_rows > 0): while($req = $my_requests_result->fetch_assoc()): ?>
                        <div class="card mb-3">
                             <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($req['subject']); ?></h5>
                                <p>Status: <span class="badge bg-<?php echo $req['status'] == 'pending' ? 'warning text-dark' : ($req['status'] == 'accepted' ? 'success' : 'danger'); ?>"><?php echo htmlspecialchars($req['status']); ?></span></p>
                                <?php if ($req['status'] == 'accepted' && $req['tutor_username']): ?>
                                <p class="mb-0">Zaakceptowane przez: <strong><?php echo htmlspecialchars($req['tutor_username']); ?></strong></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endwhile; else: ?>
                        <p>Nie wysłałeś jeszcze żadnych zapytań o pomoc.</p>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade" id="my-jobs" role="tabpanel">
                        <?php if ($my_jobs_result->num_rows > 0): while($req = $my_jobs_result->fetch_assoc()): ?>
                        <div class="card mb-3">
                             <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($req['subject']); ?></h5>
                                <p>Termin: <strong class="text-danger"><?php echo date('d.m.Y H:i', strtotime($req['proposed_date'])); ?></strong></p>
                                <p class="mt-3 mb-0">
                                    <i class="fas fa-envelope text-danger me-2"></i>
                                    Skontaktuj się z uczniem: 
                                    <a href="mailto:<?php echo htmlspecialchars($req['student_email']); ?>" class="fw-bold"><?php echo htmlspecialchars($req['student_email']); ?></a>
                                </p>
                            </div>
                             <div class="card-footer d-flex align-items-center">
                                <a href="profile.php?id=<?php echo $req['student_id']; ?>" class="text-decoration-none d-flex align-items-center">
                                    <img src="/korki/assets/uploads/avatars/<?php echo htmlspecialchars($req['student_avatar']); ?>" class="rounded-circle me-2" width="30" height="30">
                                    <small class="text-white">Zapytanie od: <?php echo htmlspecialchars($req['student_username']); ?></small>
                                </a>
                            </div>
                        </div>
                        <?php endwhile; else: ?>
                        <p>Nie udzielasz aktualnie nikomu pomocy.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
             <div class="card-header">
                <h5 class="mb-0">
                    <a href="#addRequestCollapse" data-bs-toggle="collapse" role="button" class="text-white text-decoration-none d-block">
                       <i class="fas fa-plus-circle me-2 text-danger"></i>Złóż zapytanie o pomoc
                    </a>
                </h5>
            </div>
            <div class="collapse" id="addRequestCollapse">
                <div class="card-body">
                    <form action="tutoring.php" method="post">
                        <div class="mb-3">
                            <label class="form-label">Przedmiot / Temat</label>
                            <input type="text" name="subject" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Opis problemu</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Proponowany termin</_label>
                            <input type="datetime-local" name="proposed_date" class="form-control" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="add_request" class="btn btn-danger">Wyślij</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 

$open_requests_stmt->close();
$my_requests_stmt->close();
$my_jobs_stmt->close();
include 'includes/footer.php'; 
?>