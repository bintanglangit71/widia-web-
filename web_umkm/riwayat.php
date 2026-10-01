<?php
session_start();
include 'koneksi.php';

$id_user = $_SESSION['id_user'] ?? $_SESSION['id'] ?? null;

if (empty($id_user)) {
    header("Location: login.php?pesan=harus_login");
    exit;
}

$stmt = mysqli_prepare($koneksi, "SELECT id_transaksi, tanggal, total_harga
                                   FROM tb_transaksi
                                   WHERE id_pelanggan = ?
                                   ORDER BY tanggal DESC, id_transaksi DESC");
mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$riwayat = mysqli_stmt_get_result($stmt);

$jumlah_keranjang = isset($_SESSION['cart']) ? count($_SESSION['cart']) : (isset($_SESSION['keranjang']) ? count($_SESSION['keranjang']) : 0);
$sudah_login      = !empty($id_user);
$nama_user        = $_SESSION['nama'] ?? $_SESSION['username'] ?? 'Pengguna';
?>
<!doctype html>
<html lang="id" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riwayat Pesanan · Teh Idaman</title>

    <link href="assets/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="assets/js/color-modes.js"></script>
    <meta name="theme-color" content="#198754">
    <link href="carousel.css" rel="stylesheet">
    <style>
        .ti-riwayat-wrap { padding: 3rem 0 6rem; }
        .ti-riwayat-card {
            background-color: var(--bs-body-bg);
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            overflow: hidden;
            border: 1px solid var(--bs-border-color);
        }
        .ti-riwayat-table { margin-bottom: 0; }
        .ti-riwayat-table thead th {
            background-color: rgba(25, 135, 84, 0.12);
            color: #198754;
            border: none;
            font-weight: 700;
            padding: 1rem 0.75rem;
        }
        .ti-riwayat-table td {
            vertical-align: middle;
            padding: 0.9rem 0.75rem;
        }
        .ti-riwayat-id {
            font-weight: 700;
            color: #198754;
        }
        .btn-ti-outline {
            border: 2px solid #198754;
            color: #198754;
            border-radius: 50rem;
            font-weight: 600;
            padding: 0.35rem 1.1rem;
            background: transparent;
            text-decoration: none;
            font-size: 0.85rem;
            display: inline-block;
            transition: all 0.2s ease-in-out;
        }
        .btn-ti-outline:hover { 
            background-color: #198754; 
            color: #fff; 
        }
        .btn-ti-primary {
            background-color: #198754;
            color: #fff;
            border: none;
        }
        .btn-ti-primary:hover {
            background-color: #146c43;
            color: #fff;
        }
        .ti-empty-riwayat {
            text-align: center;
            padding: 4rem 1rem;
        }
        footer a {
            text-decoration: none;
            transition: color 0.2s ease-in-out;
        }
        footer a:hover {
            color: #198754 !important;
        }
        .footer-icon {
            width: 20px;
            height: 20px;
            fill: currentColor;
        }
    </style>
</head>
<body>

    <header data-bs-theme="dark">
      <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
        <div class="container-fluid">
          <a class="navbar-brand text-success fw-bold" href="carousell.php">Teh Idaman</a>
          <button class="navbar-toggler" type="button"
                  data-bs-toggle="collapse" data-bs-target="#navbarCollapse"
                  aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav me-auto mb-2 mb-md-0">
              <li class="nav-item"><a class="nav-link" href="carousell.php#home">Home</a></li>
              <li class="nav-item"><a class="nav-link" href="carousell.php#menu">Menu</a></li>
            </ul>
            <ul class="navbar-nav align-items-lg-center gap-lg-2">
              <li class="nav-item">
                <a class="nav-link position-relative" href="keranjang.php" title="Keranjang">
                  <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                  </svg>
                  <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success"><?= $jumlah_keranjang ?></span>
                </a>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center active" href="#" role="button"
                  data-bs-toggle="dropdown" aria-expanded="false">
                  <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                    <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/>
                  </svg>
                  <?= $sudah_login ? htmlspecialchars($nama_user) : 'Akun' ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <?php if ($sudah_login) { ?>
                    <li><a class="dropdown-item" href="profil.php">Profil Saya</a></li>
                    <li><a class="dropdown-item active bg-success" href="riwayat.php">Riwayat Pesanan</a></li>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') { ?>
                      <li><a class="dropdown-item" href="dashboard/dashboard.php">Dashboard</a></li>
                    <?php } ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                  <?php } else { ?>
                    <li><a class="dropdown-item" href="login/login.php">Login</a></li>
                    <li><a class="dropdown-item" href="login/register.php">Daftar Akun</a></li>
                  <?php } ?>
                </ul>
              </li>
            </ul>
          </div>
        </div>
      </nav>
    </header>

    <main class="mt-5 pt-3">
        <div class="container ti-riwayat-wrap">
            <h2 class="fw-bold mb-4">Riwayat Pesanan Saya</h2>

            <?php if ($riwayat && mysqli_num_rows($riwayat) > 0) { ?>
                <div class="ti-riwayat-card">
                    <table class="table ti-riwayat-table align-middle">
                        <thead>
                            <tr class="text-center">
                                <th class="text-start">No. Struk</th>
                                <th>Tanggal</th>
                                <th>Total Bayar</th>
                                <th>Invoice</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($trx = mysqli_fetch_assoc($riwayat)) {
                                $no_struk = 'TI-' . str_pad($trx['id_transaksi'], 5, '0', STR_PAD_LEFT);
                            ?>
                                <tr>
                                    <td class="ti-riwayat-id">#<?= htmlspecialchars($no_struk) ?></td>
                                    <td class="text-center"><?= date('d F Y', strtotime($trx['tanggal'])) ?></td>
                                    <td class="text-center fw-semibold">Rp <?= number_format($trx['total_harga'], 0, ',', '.') ?></td>
                                    <td class="text-center">
                                        <a href="cetak.php?id=<?= (int) $trx['id_transaksi'] ?>" class="btn-ti-outline" target="_blank">
                                            Lihat &amp; Cetak
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <div class="ti-empty-riwayat">
                    <p class="lead mb-3">Belum ada riwayat pesanan.</p>
                    <a href="carousell.php#menu" class="btn btn-ti-primary rounded-pill px-4">Yuk Pesan Teh Idaman</a>
                </div>
            <?php } ?>

        </div>

        <footer class="bg-dark text-white pt-5 pb-4 border-top border-secondary">
          <div class="container">
            <div class="row g-4">
              <div class="col-md-4 col-sm-12">
                <h5 class="fw-bold text-uppercase mb-3 text-success">Teh Idaman</h5>
                <p class="text-body-secondary small">
                  Cita rasa teh otentik Indonesia dengan konsep minuman modern. Hadir dengan 700+ cabang di seluruh Indonesia, racikan nikmat 100% gula asli tanpa pemanis buatan.
                </p>
              </div>

              <div class="col-md-4 col-sm-6">
                <h5 class="fw-bold mb-3">Hubungi Kami</h5>
                <ul class="list-unstyled small text-body-secondary">
                  <li class="mb-2 d-flex align-items-center gap-2">
                    <svg class="footer-icon text-success" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
                      <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592z"/>
                    </svg>
                    <span>Info Kemitraan (WA): <strong>0882-1640-2253</strong></span>
                  </li>
                  <li class="mb-2 d-flex align-items-center gap-2">
                    <svg class="footer-icon text-danger" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
                      <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.28.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.012 8.012 0 0 0 16 8c0-4.42-3.58-8-8-8z"/>
                    </svg>
                    <span>Kantor Pusat: DI Yogyakarta</span>
                  </li>
                </ul>
              </div>

              <div class="col-md-4 col-sm-6">
                <h5 class="fw-bold mb-3">Media Sosial</h5>
                <div class="d-flex flex-column gap-2 small">
                  <a href="https://www.instagram.com/tehidaman.anakbangsa" target="_blank" class="text-body-secondary d-flex align-items-center gap-2">
                    <svg class="footer-icon text-warning" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
                      <path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.917 3.917 0 0 0-1.417.923A3.927 3.927 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.916 3.916 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.926 3.926 0 0 0-.923-1.417A3.911 3.911 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0h.003zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599.28.28.453.546.598.92.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.47 2.47 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.478 2.478 0 0 1-.92-.598 2.48 2.48 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233 0-2.136.008-2.388.046-3.231.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92.28-.28.546-.453.92-.598.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045v.002zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92zm-4.27 1.122a3.11 3.11 0 1 0 0 6.22 3.11 3.11 0 0 0 0-6.22zm0 1.442a1.668 1.668 0 1 1 0 3.336 1.668 1.668 0 0 1 0-3.336z"/>
                    </svg>
                    <span>Instagram: @tehidaman.anakbangsa</span>
                  </a>
                  <a href="#" class="text-body-secondary mt-2">Back to top &uarr;</a>
                </div>
              </div>
            </div>

            <hr class="my-4 border-secondary" />

            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center small text-body-secondary">
              <p class="mb-0">&copy; <?= date('Y') ?> Teh Idaman Indonesia. All rights reserved.</p>
              <div class="d-flex gap-3 mt-2 mt-sm-0">
                <a href="#" class="text-body-secondary">Privacy Policy</a>
                <a href="#" class="text-body-secondary">Terms & Conditions</a>
              </div>
            </div>
          </div>
        </footer>
    </main>

    <script src="assets/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>