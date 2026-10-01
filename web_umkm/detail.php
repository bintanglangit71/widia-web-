<?php
include "koneksi.php";

$id = isset($_GET['id']) ? $_GET['id'] : '';
$nama = '';
$harga = 0;
$deskripsi = '';
$poto = '';

$hasil = mysqli_query($koneksi, "SELECT * FROM tb_produk WHERE id='$id'");
if ($data = mysqli_fetch_array($hasil)){
  $nama = $data['nama'];
  $harga = $data['harga'];
  $deskripsi = $data['deskripsi'];
  $poto = $data['poto'];
}
?>

<!doctype html>
<html lang="id" data-bs-theme="auto">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Detail Produk - <?= htmlspecialchars($nama) ?></title>
    <script src="assets/js/color-modes.js"></script>
    <link href="assets/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="theme-hijau.css" rel="stylesheet" />
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        user-select: none;
      }
      .product-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        overflow: hidden;
      }
      .product-img {
        width: 100%;
        max-height: 450px;
        object-fit: contain;
        border-radius: 12px;
      }
      .price-tag {
        font-size: 1.75rem;
        font-weight: 700;
        color: #2fb344;
      }
    </style>
  </head>
  <body>
    <header data-bs-theme="dark">
      <div class="collapse text-bg-dark" id="navbarHeader">
        <div class="container">
          <div class="row">
            <div class="col-sm-8 col-md-7 py-4">
              <h4>About</h4>
              <p class="text-body-secondary">
                Teh Idaman lahir dari kecintaan kami terhadap tradisi teh Nusantara yang kaya dan otentik. Kami menghadirkan racikan daun teh pilihan segar dari perkebunan lokal terbaik.
              </p>
            </div>
            <div class="col-sm-4 offset-md-1 py-4">
              <h4>Contact</h4>
              <ul class="list-unstyled">
                <li><a href="https://www.threads.com/@tehidaman.anakbangsa" class="text-white">Follow on Threads</a></li>
                <li><a href="https://www.facebook.com/371247936070633" class="text-white">Like on Facebook</a></li>
                <li><a href="https://www.instagram.com/tehidaman.anakbangsa/#" class="text-white">Follow on Instagram</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <div class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container">
          <a href="carousell.php" class="navbar-brand d-flex align-items-center gap-2">
            <strong>Teh Idaman</strong>
          </a>
          <div class="d-flex align-items-center gap-3">
            <button
              class="navbar-toggler"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#navbarHeader"
              aria-controls="navbarHeader"
              aria-expanded="false"
              aria-label="Toggle navigation"
            >
              <span class="navbar-toggler-icon"></span>
            </button>
          </div>
        </div>
      </div>
    </header>

    <main class="py-5">
      <div class="container">
        <!-- Tombol Kembali -->
        <a href="carousell.php" class="btn btn-outline-secondary mb-4">&larr; Kembali ke Menu</a>

        <div class="card product-card bg-body-tertiary p-4">
          <div class="row g-4 align-items-center">
            <!-- Gambar Produk -->
            <div class="col-md-5 text-center">
              <img 
                src="images/<?= htmlspecialchars($poto) ?>" 
                alt="<?= htmlspecialchars($nama) ?>" 
                class="img-fluid product-img shadow-sm"
              >
            </div>

            <!-- Detail Produk -->
            <div class="col-md-7">
              <h1 class="display-6 fw-bold mb-2"><?= htmlspecialchars($nama) ?></h1>
              <div class="price-tag mb-3">
                Rp <?= number_format($harga, 0, ',', '.') ?>
              </div>
              <p class="lead text-body-secondary fs-6 mb-4">
                <?= nl2br(htmlspecialchars($deskripsi)) ?>
              </p>

              <!-- Form Tambah ke Keranjang -->
              <form method="post" action="keranjang.php" class="mt-4">
                <input type="hidden" name="id_produk" value="<?= htmlspecialchars($id) ?>">
                <input type="hidden" name="hidden_foto" value="<?= htmlspecialchars($poto) ?>">
                <input type="hidden" name="hidden_nama" value="<?= htmlspecialchars($nama) ?>">
                <input type="hidden" name="hidden_harga" value="<?= htmlspecialchars($harga) ?>">

                <div class="row g-3 align-items-center">
                  <div class="col-auto">
                    <label for="jumlah" class="col-form-label fw-semibold">Jumlah:</label>
                  </div>
                  <div class="col-auto">
                    <input 
                      type="number" 
                      id="jumlah" 
                      name="jumlah" 
                      value="1" 
                      min="1" 
                      class="form-control text-center" 
                      style="width: 80px;"
                    >
                  </div>
                  <div class="col-auto">
                    <button type="submit" name="add" class="btn btn-success px-4 py-2 fw-semibold">
                      + Tambah ke Keranjang
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </main>

    <script src="assets/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>