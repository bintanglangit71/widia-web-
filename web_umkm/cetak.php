<?php
session_start();
include 'koneksi.php';

$id_user = $_SESSION['id'] ?? ($_SESSION['id_user'] ?? null);

if (empty($id_user)) {
    header("Location: login/login.php?pesan=harus_login");
    exit;
}

$id_transaksi = (int) ($_GET['id'] ?? 0);

$stmt = mysqli_prepare($koneksi, "SELECT t.*, u.nama AS nama_pelanggan, u.alamat, u.hp, u.email
                                   FROM tb_transaksi t
                                   JOIN tb_user u ON u.id = t.id_pelanggan
                                   WHERE t.id_transaksi = ?");
mysqli_stmt_bind_param($stmt, "i", $id_transaksi);
mysqli_stmt_execute($stmt);
$transaksi = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

if (!$transaksi || (!$is_admin && (int) $transaksi['id_pelanggan'] !== (int) $id_user)) {
    header("Location: carousell.php");
    exit;
}

$stmt2 = mysqli_prepare($koneksi, "SELECT d.jumlah, p.nama, p.harga, p.poto
                                    FROM tb_detail d
                                    JOIN tb_produk p ON p.id = d.id_produk
                                    WHERE d.id_transaksi = ?");
mysqli_stmt_bind_param($stmt2, "i", $id_transaksi);
mysqli_stmt_execute($stmt2);
$items = mysqli_stmt_get_result($stmt2);

$total = 0;
$rows = [];
while ($row = mysqli_fetch_assoc($items)) {
    $subtotal = $row['jumlah'] * $row['harga'];
    $total += $subtotal;
    $rows[] = $row;
}

$no_struk = 'TRX-' . str_pad($transaksi['id_transaksi'], 5, '0', STR_PAD_LEFT);
?>
<!doctype html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk <?= htmlspecialchars($no_struk) ?> - Teh Idaman</title>

    <link href="assets/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="theme-hijau.css" rel="stylesheet" />
    <style>
        .invoice-card {
            max-width: 720px;
            margin: 2rem auto;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            background-color: #fff;
            overflow: hidden;
        }
        .invoice-head {
            background: #198754;
            color: #fff;
            padding: 2rem;
            text-align: center;
        }
        .product-thumb {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 12px;
        }
        @media print {
            .no-print, header, footer { display: none !important; }
            body { background: #fff !important; }
            .invoice-card { box-shadow: none !important; margin: 0 !important; max-width: 100% !important; }
        }
    </style>
</head>
<body class="bg-light">

    <header class="no-print" data-bs-theme="dark">
        <div class="navbar navbar-dark bg-dark shadow-sm">
            <div class="container">
                <a href="carousell.php" class="navbar-brand d-flex align-items-center gap-2">
                    <strong>Teh Idaman</strong>
                </a>
                <a href="carousell.php" class="btn btn-outline-light btn-sm">&larr; Kembali ke Beranda</a>
            </div>
        </div>
    </header>

    <main class="py-4">
        <div class="container">

            <div class="d-flex justify-content-between align-items-center mx-auto mb-3 no-print" style="max-width: 720px;">
                <a href="carousell.php" class="btn btn-outline-secondary">&larr; Belanja Lagi</a>
                <button type="button" class="btn btn-success" onclick="window.print()">
                    🖨️ Cetak / Save PDF
                </button>
            </div>

            <div class="card invoice-card border-0">
                <div class="invoice-head">
                    <h2 class="fw-bold mb-1">TEH IDAMAN</h2>
                    <p class="mb-2 opacity-75">Cita Rasa Teh Otentik Indonesia</p>
                    <span class="badge bg-light text-success fw-bold px-3 py-2">Pesanan Berhasil</span>
                </div>

                <div class="card-body p-4">
                    <div class="row mb-4 pb-3 border-bottom">
                        <div class="col-sm-6 mb-3 mb-sm-0">
                            <h6 class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.8rem;">No. Transaksi</h6>
                            <p class="fw-bold mb-2"><?= htmlspecialchars($no_struk) ?></p>
                            <h6 class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.8rem;">Tanggal</h6>
                            <p class="mb-0"><?= date('d F Y', strtotime($transaksi['tanggal'])) ?></p>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <h6 class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.8rem;">Pelanggan</h6>
                            <p class="fw-bold mb-0"><?= htmlspecialchars($transaksi['nama_pelanggan'] ?? '-') ?></p>
                            <p class="text-muted small mb-0"><?= htmlspecialchars($transaksi['email'] ?? '-') ?></p>
                            <p class="text-muted small mb-0"><?= htmlspecialchars($transaksi['hp'] ?? '-') ?></p>
                        </div>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr class="text-center">
                                    <th class="text-start">Produk</th>
                                    <th style="width: 80px;">Qty</th>
                                    <th style="width: 120px;">Harga</th>
                                    <th style="width: 130px;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $r) { ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="images/<?= htmlspecialchars($r['poto']) ?>" class="product-thumb shadow-sm" alt="<?= htmlspecialchars($r['nama']) ?>">
                                            <span class="fw-semibold"><?= htmlspecialchars($r['nama']) ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center"><?= (int) $r['jumlah'] ?></td>
                                    <td class="text-center">Rp <?= number_format($r['harga'], 0, ',', '.') ?></td>
                                    <td class="text-end fw-bold text-success">Rp <?= number_format($r['jumlah'] * $r['harga'], 0, ',', '.') ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 bg-light rounded-3 mb-4">
                        <div class="d-flex justify-content-between fs-5 fw-bold text-success">
                            <span>Total Bayar:</span>
                            <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
                        </div>
                    </div>

                    <div class="text-center text-muted small border-top pt-3">
                        <p class="mb-0">Terima kasih telah berbelanja di <strong>Teh Idaman</strong>!</p>
                        <p class="mb-0">Simpan bukti transaksi ini sebagai bukti pembayaran yang sah.</p>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script src="assets/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>