<?php 
// index.php
include 'includes/config.php';
include 'includes/header.php';

// Ambil semua project
$stmt = mysqli_prepare($conn, "SELECT id, title, description, image, created_at FROM projects ORDER BY id DESC");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
  echo "Terjadi kesalahan dalam mengambil data: " . mysqli_error($conn);
}
?>

<section class="hero">
  <div class="hero-inner">
    <h2>Hello, I'm Riani.</h2>
    <p class="lead">Backend Developer & UI/UX Designer - siswa SMK Hidayah yang ingin menjadi Full Stack Programmer.</p>
  </div>
</section>

<section class="about">
  <div class="about-left">
    <img src="assets/images/profile-placeholder.png" alt="Foto Riani" class="profil-pic">
    <div class="sosials">
      <span>Email: <a href="mailto:rianiasha_@gmail.com">rianiasha_@gmail.com</a></span>
      <span>WA: 0882006770324</span>
      <span>IG: @rianiasha_</span>
      <span>Github: @rianiasha22</span>
    </div>
  </div>
  <div class="about-right">
    <h3>About Me</h3>
    <p>Saya Riani, ingin menjadi pengangguran sukses.</p>
  </div>
</section>

<section class="portofolio">
  <h3 class="section-title">My Portfolio</h3>
  <div class="grid">
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
      <article class="card">
        <?php if (!empty($row['image']) && file_exists('assets/images/' . $row['image'])): ?>
          <div class="card-media">
            <img src="assets/images/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
          </div>
        <?php else: ?>
          <div class="card-media noimg">
            <span>No Image</span>
          </div> 
        <?php endif; ?>

        <div class="card-body">
          <h4><?php echo htmlspecialchars($row['title']); ?></h4>
          <p class="muted"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
          <span class="meta"><?php echo htmlspecialchars($row['created_at']); ?></span>
        </div>
      </article>
    <?php endwhile; ?>
  </div>
</section>

<section class="contact">
  <h3 class="section-title">Contact Me</h3>
  <p>Jika tertarik bekerja sama atau ingin bertanya, hubungi via email atau WA yang tercantum di atas.</p>
</section>

<?php include 'includes/footer.php'; ?>
