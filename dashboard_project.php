<?php
// dashboard_project.php
include 'includes/config.php';

// PROSES CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
  $title = trim($_POST['title'] ?? '');
  $desc = trim($_POST['description'] ?? '');
  $image_name = '';

  if (!empty($_FILES['image']['name'])) {
    // simple sanitize filename
    $image_name = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['image']['name']));
    $target = __DIR__ . '/assets/images/' . $image_name;
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
      $image_name = ''; // gagal upload
    }
  }

  $stmt = mysqli_prepare($conn, "INSERT INTO projects (title, description, image) VALUES (?, ?, ?)");
  mysqli_stmt_bind_param($stmt, "sss", $title, $desc, $image_name);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);
  header("Location: dashboard_project.php");
  exit;
}

// PROSES UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
  $id    = intval($_POST['id'] ?? 0);
  $title = trim($_POST['title'] ?? '');
  $desc  = trim($_POST['description'] ?? '');

  // cek apakah ada file baru
  if (!empty($_FILES['image']['name'])) {
    $image_name = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['image']['name']));
    $target = __DIR__ . '/assets/images/' . $image_name;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
      // update dengan gambar baru
      $stmt = mysqli_prepare($conn, "UPDATE projects SET title = ?, description = ?, image = ? WHERE id = ?");
      mysqli_stmt_bind_param($stmt, "sssi", $title, $desc, $image_name, $id);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_close($stmt);
    } else {
      // gagal upload, update tanpa mengganti gambar
      $stmt = mysqli_prepare($conn, "UPDATE projects SET title = ?, description = ? WHERE id = ?");
      mysqli_stmt_bind_param($stmt, "ssi", $title, $desc, $id);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_close($stmt);
    }
  } else {
    // update tanpa gambar
    $stmt = mysqli_prepare($conn, "UPDATE projects SET title = ?, description = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssi", $title, $desc, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
  }

  header("Location: dashboard_project.php");
  exit;
}

// PROSES DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
  $id = intval($_POST['id'] ?? 0);

  // ambil nama file gambar dulu untuk hapus file fisik
  $stmt = mysqli_prepare($conn, "SELECT image FROM projects WHERE id = ?");
  mysqli_stmt_bind_param($stmt, "i", $id);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_bind_result($stmt, $image_ToDelete);
  mysqli_stmt_fetch($stmt);
  mysqli_stmt_close($stmt);

  if (!empty($image_ToDelete) && file_exists(__DIR__ . '/assets/images/' . $image_ToDelete)) {
    @unlink(__DIR__ . '/assets/images/' . $image_ToDelete);
  }

  $stmt = mysqli_prepare($conn, "DELETE FROM projects WHERE id = ?");
  mysqli_stmt_bind_param($stmt, "i", $id);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);

  header("Location: dashboard_project.php");
  exit;
}

// Ambil semua project untuk ditampilkan
$stmt = mysqli_prepare($conn, "SELECT id, title, description, image, created_at FROM projects ORDER BY id DESC");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

include 'includes/header.php';
?>

<section class="dashboard">
  <h3 class="section-title">Dashboard Projects</h3>
  <div class="admin-grid">
    <div class="panel">
      <h4>Tambah Project</h4>
      <form method="post" enctype="multipart/form-data" class="form-admin">
        <input type="hidden" name="action" value="create">
        <label>Judul:</label>
        <input type="text" name="title" required>
        <label>Deskripsi:</label>
        <textarea name="description" required></textarea>
        <label>Gambar (opsional)</label>
        <input type="file" name="image" accept="image/*">
        <button type="submit">Tambah Project</button>
      </form>
    </div>

    <div class="panel">
      <h4>Daftar Project</h4>
      <table class="admin-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Judul</th>
            <th>Deskripsi</th>
            <th>Gambar</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
              <td><?php echo $row['id']; ?></td>
              <td>
                <form method="post" enctype="multipart/form-data" class="inline-form">
                  <input type="hidden" name="action" value="update">
                  <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                  <input type="text" name="title" value="<?php echo htmlspecialchars($row['title']); ?>" required>
                  <textarea name="description" required><?php echo htmlspecialchars($row['description']); ?></textarea>
              </td>
              <td class="image-col">
                <?php if (!empty($row['image']) && file_exists('assets/images/' . $row['image'])): ?>
                  <img src="assets/images/<?php echo htmlspecialchars($row['image']); ?>" alt="" class="thumb">
                  <div><label>Ganti Gambar:</label><input type="file" name="image" accept="image/*"></div>
                <?php else: ?>
                  <div class="noimg">No Image</div>
                  <div><label>Upload Gambar:</label><input type="file" name="image" accept="image/*"></div>
                <?php endif; ?>
              </td>
              <td>
                <button type="submit" class="btn small">Simpan</button>
                </form>

                <!-- form delete terpisah -->
                <form method="post" style="margin-top:8px;">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                  <button type="submit" class="btn danger small">Hapus</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
