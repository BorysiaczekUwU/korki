<?php
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit;
}


$search_query_text = "";
$sql = "SELECT u.id, u.username, p.skills_good, p.profile_img 
        FROM users u 
        JOIN profiles p ON u.id = p.user_id";

if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $search_query_text = trim($_GET['q']);
    $sql .= " WHERE u.username LIKE ? OR p.skills_good LIKE ?";
    $stmt = $conn->prepare($sql);
    $like_query = "%" . $search_query_text . "%";
    $stmt->bind_param("ss", $like_query);
} else {

    $sql .= " ORDER BY u.username ASC";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$search_results = $stmt->get_result();
?>

<div class="mb-4">
    <h2 class="fw-bold"><i class="fas fa-users text-danger me-2"></i>Społeczność</h2>
    <p class="text-white">Znajdź osoby, które mogą Ci pomóc lub którym Ty możesz zaoferować wsparcie.</p>
</div>

<div class="card mb-5">
    <div class="card-body">
        <form action="search.php" method="get">
            <div class="input-group">
                <input type="text" name="q" class="form-control form-control-lg" placeholder="Wpisz nazwę użytkownika lub szukaną umiejętność..." value="<?php echo htmlspecialchars($search_query_text); ?>">
                <button class="btn btn-danger" type="submit"><i class="fas fa-search"></i> Szukaj</button>
            </div>
        </form>
    </div>
</div>

<div>
    <?php if (!empty($search_query_text)): ?>
        <h4 class="mb-4">Wyniki wyszukiwania dla: "<?php echo htmlspecialchars($search_query_text); ?>"</h4>
    <?php else: ?>
        <h4 class="mb-4">Przeglądaj wszystkich użytkowników:</h4>
    <?php endif; ?>
    
    <div class="row">
        <?php if ($search_results && $search_results->num_rows > 0): ?>
            <?php while($user = $search_results->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 text-center">
                        <div class="card-body d-flex flex-column">
                            <img src="/korki/assets/uploads/avatars/<?php echo htmlspecialchars($user['profile_img']); ?>" class="rounded-circle mx-auto mb-3" width="100" height="100" style="object-fit: cover;">
                            <h5 class="card-title text-white"><?php echo htmlspecialchars($user['username']); ?></h5>
                            
                            <div class="mb-3">
                                <?php
                                if (!empty($user['skills_good'])) {
                                    $skills = explode(',', $user['skills_good']);
                                    foreach (array_slice($skills, 0, 3) as $skill) { // Pokaż max 3 umiejętności
                                        echo '<span class="badge bg-success me-1">' . htmlspecialchars(trim($skill)) . '</span>';
                                    }
                                } else {
                                    echo '<span class="badge bg-secondary">Brak umiejętności</span>';
                                }
                                ?>
                            </div>

                            <a href="profile.php?id=<?php echo $user['id']; ?>" class="btn btn-outline-danger mt-auto">Zobacz profil</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col">
                <div class="card p-4 text-center">
                    <p class="mb-0">Nie znaleziono użytkowników pasujących do Twojego zapytania.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
$stmt->close();
include 'includes/footer.php'; 
?>