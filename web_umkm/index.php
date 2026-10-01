<?php
session_start();
include 'koneksi.php';

// Ambil data user yang sedang login (jika ada)
$current_user = null;
if (isset($_SESSION['id'])) {
    $id_user = $_SESSION['id'];
    $q_user = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE id='$id_user' LIMIT 1");
    $current_user = mysqli_fetch_array($q_user);
}

$produk = mysqli_query($koneksi, "SELECT * FROM tb_produk ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Téh Idaman</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root{
    --hijau-tua: #0f3d2e;
    --hijau: #1a5c42;
    --krem: #f7f2e7;
    --emas: #c9a24b;
    --teks: #2b2b26;
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  body{font-family:'Poppins',sans-serif;color:var(--teks);background:var(--krem);}
  h1,h2,h3{font-family:'Fraunces',serif;}
  a{text-decoration:none;color:inherit;}

  /* ===== NAVBAR ===== */
  header{
    position:fixed; top:0; left:0; right:0; z-index:50;
    display:flex; align-items:center; justify-content:space-between;
    padding:18px 6%;
    background:rgba(15,61,46,0.85);
    backdrop-filter: blur(6px);
  }
  .logo{color:var(--krem); font-size:22px; font-weight:700; font-family:'Fraunces',serif;}
  nav a{color:var(--krem); margin-left:28px; font-size:14px; letter-spacing:.5px;}
  nav a.btn-login{
    border:1px solid var(--emas); padding:8px 18px; border-radius:30px;
  }
  nav .nav-divider{color:rgba(247,242,231,.3); margin:0 4px;}
  nav .nav-avatar{
    display:inline-flex; align-items:center; gap:8px; vertical-align:middle;
  }
  nav .nav-avatar img.nav-avatar,
  nav .nav-avatar span.nav-avatar{
    width:28px; height:28px; border-radius:50%; object-fit:cover;
    border:1.5px solid var(--emas); display:inline-block;
  }
  nav .nav-avatar-initial{
    background:var(--emas); color:var(--hijau-tua);
    font-weight:700; font-size:13px;
    display:inline-flex; align-items:center; justify-content:center;
    line-height:1;
  }
  nav a.nav-akun{display:inline-flex; align-items:center; gap:8px;}
  nav a.btn-logout{
    background:rgba(201,162,75,.15); border:1px solid var(--emas);
    padding:8px 18px; border-radius:30px; color:var(--emas);
    transition:.2s;
  }
  nav a.btn-logout:hover{background:var(--emas); color:var(--hijau-tua);}

  /* ===== HERO ===== */
  .hero{
    position:relative; height:100vh; min-height:560px;
    display:flex; align-items:center; justify-content:center; text-align:center;
    background:url('hero.jpeg') center/cover no-repeat;
  }
  .hero::before{
    content:""; position:absolute; inset:0;
    background:linear-gradient(180deg, rgba(15,61,46,.55), rgba(15,61,46,.75));
  }
  .hero-content{position:relative; z-index:1; color:var(--krem); max-width:640px; padding:0 20px;}
  .hero-content .eyebrow{
    letter-spacing:3px; font-size:12px; text-transform:uppercase; color:var(--emas); margin-bottom:14px; display:block;
  }
  .hero-content h1{font-size:clamp(36px,6vw,58px); line-height:1.1; margin-bottom:16px;}
  .hero-content p{font-size:16px; opacity:.9; margin-bottom:28px;}
  .hero-content .btn{
    display:inline-block; background:var(--emas); color:var(--hijau-tua);
    padding:14px 32px; border-radius:30px; font-weight:600; font-size:14px;
  }

  /* ===== SECTION UMUM ===== */
  section{padding:80px 6%;}
  .section-title{text-align:center; margin-bottom:48px;}
  .section-title span{color:var(--hijau); letter-spacing:3px; font-size:12px; text-transform:uppercase;}
  .section-title h2{font-size:clamp(26px,4vw,36px); margin-top:8px;}

  /* ===== TENTANG / SEJARAH ===== */
  .tentang{background:var(--hijau-tua); color:var(--krem);}
  .tentang .section-title span{color:var(--emas);}
  .tentang-grid{
    display:grid; grid-template-columns:1fr 1fr; gap:48px; align-items:center; max-width:1100px; margin:0 auto;
  }
  .tentang-grid img{width:100%; border-radius:16px; object-fit:cover; aspect-ratio:4/3;}
  .tentang-grid p{margin-bottom:16px; line-height:1.8; opacity:.9;}

  /* ===== PRODUK ===== */
  .produk-grid{
    display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:28px; max-width:1200px; margin:0 auto;
  }
  .card-produk{
    background:#fff; border-radius:16px; overflow:hidden;
    box-shadow:0 6px 20px rgba(15,61,46,.08); transition:.25s;
  }
  .card-produk:hover{transform:translateY(-6px); box-shadow:0 14px 28px rgba(15,61,46,.15);}
  .card-produk img{width:100%; aspect-ratio:1/1; object-fit:cover;}
  .card-body{padding:16px;}
  .card-body h3{font-size:16px; font-family:'Poppins',sans-serif; font-weight:600; margin-bottom:4px;}
  .card-body .harga{color:var(--hijau); font-weight:600; font-size:14px; margin-bottom:14px;}
  .card-actions{display:flex; gap:8px;}
  .card-actions button{
    flex:1; padding:10px; border-radius:24px; border:none; font-size:13px; font-weight:600; cursor:pointer;
  }
  .btn-detail{background:var(--krem); border:1px solid var(--hijau) !important; color:var(--hijau);}
  .btn-keranjang{background:var(--hijau-tua); color:var(--krem);}

  /* ===== MODAL DETAIL ===== */
  .modal-overlay{
    display:none; position:fixed; inset:0; background:rgba(15,61,46,.6);
    align-items:center; justify-content:center; z-index:100; padding:20px;
  }
  .modal-overlay.active{display:flex;}
  .modal-box{
    background:#fff; max-width:480px; width:100%; border-radius:18px; overflow:hidden; position:relative;
  }
  .modal-box img{width:100%; aspect-ratio:4/3; object-fit:cover;}
  .modal-box .modal-body{padding:24px;}
  .modal-box .modal-close{
    position:absolute; top:12px; right:14px; background:rgba(0,0,0,.5); color:#fff;
    border:none; width:32px; height:32px; border-radius:50%; cursor:pointer; font-size:16px;
  }

  /* ===== CABANG & MAPS ===== */
  .cabang-grid{
    display:grid; grid-template-columns:1fr 1.2fr; gap:40px; max-width:1100px; margin:0 auto; align-items:start;
  }
  .list-cabang li{
    list-style:none; padding:18px 0; border-bottom:1px solid #ddd6c4;
  }
  .list-cabang li h4{font-size:16px; margin-bottom:4px;}
  .list-cabang li p{font-size:13px; opacity:.75;}
  .cabang-grid iframe{width:100%; height:100%; min-height:380px; border:0; border-radius:16px;}

  /* ===== CONTACT US ===== */
  footer{background:var(--hijau-tua); color:var(--krem); padding:60px 6% 30px;}
  .contact-grid{
    display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:32px; max-width:1100px; margin:0 auto 30px;
  }
  .contact-grid h4{font-size:13px; letter-spacing:2px; text-transform:uppercase; color:var(--emas); margin-bottom:14px;}
  .contact-grid p, .contact-grid a{display:block; font-size:14px; opacity:.85; margin-bottom:8px;}
  footer .copy{text-align:center; font-size:12px; opacity:.6; border-top:1px solid rgba(255,255,255,.15); padding-top:20px;}

  @media(max-width:768px){
    .tentang-grid, .cabang-grid{grid-template-columns:1fr;}
  }
</style>
</head>
<body>

<!-- NAVBAR -->
<header>
  <a href="index.php" class="logo">Téh Idaman</a>
  <nav>
    <a href="#produk">Produk</a>
    <a href="#cabang">Cabang</a>
    <a href="#contact">Contact</a>
    <?php if ($current_user): ?>
      <span class="nav-divider">|</span>
      <a href="profil.php" class="nav-akun">
        <?php if (!empty($current_user['poto']) && file_exists("images/" . $current_user['poto'])): ?>
          <img src="images/<?= htmlspecialchars($current_user['poto']) ?>" class="nav-avatar" alt="">
        <?php else: ?>
          <span class="nav-avatar nav-avatar-initial"><?= strtoupper(substr($current_user['nama'] ?? 'U', 0, 1)) ?></span>
        <?php endif; ?>
        <?= htmlspecialchars($current_user['nama']) ?>
      </a>
      <a href="logout.php" class="btn-logout">Logout</a>
    <?php else: ?>
      <a href="login/login.php" >Login</a>
      <a href="login/register.php" class="btn-login">Daftar</a>
    <?php endif; ?>
  </nav>
</header>

<!-- HERO -->
<section class="hero">
  <div class="hero-content">
    <span class="eyebrow">Sejak dari kebun ke gelas kamu</span>
    <h1>Téh Idaman</h1>
    <p>Teh dan minuman seduh segar dengan bahan pilihan — dari Tea Series klasik sampai Milky Series creamy yang bikin nagih.</p>
    <a href="#produk" class="btn">Lihat Menu</a>
  </div>
</section>

<!-- TENTANG / SEJARAH -->
<section class="tentang">
  <div class="tentang-grid">
    <img src="assets/sejarah-teh-idaman.jpg" alt="Sejarah Téh Idaman">
    <div>
      <span style="display:block;letter-spacing:3px;font-size:12px;text-transform:uppercase;color:var(--emas);margin-bottom:10px;">Cerita Kami</span>
      <h2 style="margin-bottom:16px;">Berawal dari Kecintaan pada Teh</h2>
      <p>Téh Idaman lahir dari kebiasaan sederhana: ngeteh sore sambil ngobrol santai. Dari situ kami racik berbagai varian, mulai dari Tea Series yang ringan, Thai Tea yang creamy, sampai Yakult Series yang segar dan kekinian.</p>
      <p>Kini Téh Idaman hadir di beberapa cabang, tetap dengan resep dan kualitas bahan yang sama seperti pertama kali kami mulai.</p>
    </div>
  </div>
</section>

<!-- PRODUK -->
<section id="produk">
  <div class="section-title">
    <span>Menu Kami</span>
    <h2>Pilih Minuman Favoritmu</h2>
  </div>

  <div class="produk-grid">
    <?php while ($row = mysqli_fetch_array($produk)): ?>
    <div class="card-produk">
      <img src="uploads/<?= htmlspecialchars($row['poto']) ?>" alt="<?= htmlspecialchars($row['nama']) ?>">
      <div class="card-body">
        <h3><?= htmlspecialchars($row['nama']) ?></h3>
        <div class="harga">Rp<?= number_format($row['harga'],0,',','.') ?></div>
        <div class="card-actions">
          <button class="btn-detail"
            onclick="bukaDetail('<?= htmlspecialchars($row['nama'], ENT_QUOTES) ?>','<?= htmlspecialchars($row['deskripsi'], ENT_QUOTES) ?>','uploads/<?= htmlspecialchars($row['poto']) ?>','<?= $row['harga'] ?>')">
            Detail
          </button>
          <button class="btn-keranjang" onclick="tambahKeranjang(<?= $row['id'] ?>)">+ Keranjang</button>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
  </div>
</section>

<!-- MODAL DETAIL PRODUK -->
<div class="modal-overlay" id="modalDetail">
  <div class="modal-box">
    <button class="modal-close" onclick="tutupDetail()">&times;</button>
    <img id="modalImg" src="" alt="">
    <div class="modal-body">
      <h3 id="modalNama" style="margin-bottom:8px;"></h3>
      <div id="modalHarga" style="color:var(--hijau);font-weight:600;margin-bottom:12px;"></div>
      <p id="modalDeskripsi" style="line-height:1.7;opacity:.85;"></p>
    </div>
  </div>
</div>

<!-- CABANG & MAPS -->
<section id="cabang">
  <div class="section-title">
    <span>Temukan Kami</span>
    <h2>Cabang Téh Idaman</h2>
  </div>
  <div class="cabang-grid">
    <ul class="list-cabang">
      <li>
        <h4>Téh Idaman — Cabang 1</h4>
        <p>Jl. Contoh Alamat No. 10, Kota A. Buka 09.00–21.00</p>
      </li>
      <li>
        <h4>Téh Idaman — Cabang 2</h4>
        <p>Jl. Contoh Alamat No. 25, Kota B. Buka 09.00–21.00</p>
      </li>
      <li>
        <h4>Téh Idaman — Cabang 3</h4>
        <p>Jl. Contoh Alamat No. 7, Kota C. Buka 09.00–21.00</p>
      </li>
    </ul>
    <iframe
      src="https://www.google.com/maps?q=Indonesia&output=embed"
      allowfullscreen loading="lazy"></iframe>
  </div>
</section>

<!-- CONTACT US -->
<footer id="contact">
  <div class="contact-grid">
    <div>
      <h4>Téh Idaman</h4>
      <p>Teh segar, racikan idaman, langsung dari hati.</p>
    </div>
    <div>
      <h4>Kontak</h4>
      <a href="tel:+628123456789">0812-3456-789</a>
      <a href="mailto:halo@tehidaman.id">halo@tehidaman.id</a>
    </div>
    <div>
      <h4>Sosial Media</h4>
      <a href="#">Instagram</a>
      <a href="#">TikTok</a>
      <a href="#">WhatsApp</a>
    </div>
  </div>
  <div class="copy">&copy; <?= date('Y') ?> Téh Idaman. All rights reserved.</div>
</footer>

<script>
function bukaDetail(nama, deskripsi, foto, harga){
  document.getElementById('modalNama').innerText = nama;
  document.getElementById('modalDeskripsi').innerText = deskripsi;
  document.getElementById('modalImg').src = foto;
  document.getElementById('modalHarga').innerText = 'Rp' + Number(harga).toLocaleString('id-ID');
  document.getElementById('modalDetail').classList.add('active');
}
function tutupDetail(){
  document.getElementById('modalDetail').classList.remove('active');
}
function tambahKeranjang(id){
  // sementara pakai alert, nanti diganti fetch/AJAX ke keranjang.php
  alert('Produk ditambahkan ke keranjang!');
}
</script>

</body>
</html>