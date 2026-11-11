<?php
require_once 'config/db.php';
require_once 'middleware/auth_check.php';

check_login();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :id");
$stmt->execute([':id' => $id]);
$project = $stmt->fetch();

if (!$project) {
    die("Data karya tidak ditemukan.");
}

// Pengecekan Hak Akses Edit
if ($_SESSION['role'] === 'siswa' && $project['user_id'] != $_SESSION['user_id']) {
    die("Akses Ditolak: Anda tidak dapat mengedit karya milik siswa lain.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_project = trim($_POST['nama_project']);
    $deskripsi    = trim($_POST['deskripsi']);
    $link_project = trim($_POST['link_project']);
    $gambar_nama  = $project['gambar'];

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $new_name = uniqid('project_', true) . '.' . $ext;

        if (move_uploaded_file($_FILES['gambar']['tmp_name'], 'uploads/' . $new_name)) {
            if (file_exists('uploads/' . $project['gambar'])) {
                unlink('uploads/' . $project['gambar']);
            }
            $gambar_nama = $new_name;
        }
    }

    $stmt = $pdo->prepare("UPDATE projects SET nama_project = :nama, deskripsi = :desk, gambar = :img, link_project = :link WHERE id = :id");
    $stmt->execute([':nama' => $nama_project, ':desk' => $deskripsi, ':img' => $gambar_nama, ':link' => $link_project, ':id' => $id]);
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Karya</title>
    <link rel="stylesheet" href="../assets\css/style.php.css">
    <link rel="stylesheet" href="../assets\css/form.css">

<body>
    <div class="form-container">
        <a href="index.php" class="btn btn-secondary" style="margin-bottom:15px;">&larr; Batal</a>
        <h2>Edit Karya Digital</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Nama Karya</label>
                <input type="text" name="nama_project" value="<?= htmlspecialchars($project['nama_project']) ?>" required>
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="deskripsi" required><?= htmlspecialchars($project['deskripsi']) ?></textarea>
            </div>
            <div class="form-group">
                <label>Link Project</label>
                <input type="url" name="link_project" value="<?= htmlspecialchars($project['link_project']) ?>" required>
            </div>
            <div class="form-group">
                <label>Ganti Gambar (Opsional)</label>
                <input type="file" name="gambar" accept="image/*">
            </div>
            <button type="submit" class="btn btn-warning" style="width:100%;">Simpan Perubahan</button>
        </form>
    </div>
</body>

</html>