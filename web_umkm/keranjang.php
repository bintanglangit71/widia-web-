<?php
session_start();

$koneksi = mysqli_connect("localhost", "root", "", "db_toko");

// Cek apakah session id ada (User sudah login)
$id = isset($_SESSION['id']) ? $_SESSION['id'] : null;

// PROSES TAMBAH KE KERANJANG
if (isset($_POST["add"])) {
    $id_produk = $_POST["id_produk"];
    $jumlah = (int) $_POST["jumlah"];

    if (isset($_SESSION["cart"][$id_produk])) {
        $_SESSION["cart"][$id_produk]['jumlah'] += $jumlah;
    } else {
        $_SESSION["cart"][$id_produk] = array(
            'id' => $id_produk,
            'nama' => $_POST["hidden_nama"],
            'harga' => $_POST["hidden_harga"],
            'foto' => $_POST["hidden_foto"],
            'jumlah' => $jumlah
        );
    }

    echo '<script>window.location="keranjang.php"</script>';
    exit;
}

// PROSES AKSI (HAPUS & BELI)
if (isset($_GET["aksi"])) {
    if ($_GET["aksi"] == "hapus") {
        $id_produk = $_GET["id"];
        if (isset($_SESSION["cart"][$id_produk])) {
            unset($_SESSION["cart"][$id_produk]);
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    Swal.fire({
                        icon: "success",
                        title: "Berhasil",
                        text: "Produk berhasil dihapus.",
                        confirmButtonColor: "#198754"
                    }).then(() => {
                        window.location="keranjang.php";
                    });
                });
            </script>';
        }
    } elseif ($_GET["aksi"] == "beli") {
        if (empty($_SESSION["cart"])) {
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    Swal.fire({
                        icon: "warning",
                        title: "Keranjang Kosong",
                        text: "Keranjang Anda masih kosong!",
                        confirmButtonColor: "#198754"
                    }).then(() => {
                        window.location="carousell.php";
                    });
                });
            </script>';
        } elseif (!$id) {
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    Swal.fire({
                        icon: "info",
                        title: "Perlu Login",
                        text: "Silakan login terlebih dahulu untuk melakukan checkout.",
                        confirmButtonColor: "#0d6efd"
                    }).then(() => {
                        window.location="login/login.php";
                    });
                });
            </script>';
        } else {
            $total = 0;
            foreach ($_SESSION["cart"] as $value) {
                $total += ($value["jumlah"] * $value["harga"]);
            }

            // Simpan transaksi utama
            $query = mysqli_query(
                $koneksi,
                "INSERT INTO tb_transaksi(tanggal, id_pelanggan, total_harga) 
                 VALUES ('" . date("Y-m-d") . "', '$id', '$total')"
            ) or die("Error insert transaksi: " . mysqli_error($koneksi));
            
            $id_transaksi = mysqli_insert_id($koneksi);

            // Simpan detail produk
            foreach ($_SESSION["cart"] as $value) {
                $id_produk = $value['id'];
                $jumlah = $value['jumlah'];
                $sql = "INSERT INTO tb_detail(id_transaksi, id_produk, jumlah) 
                        VALUES ('$id_transaksi', '$id_produk', '$jumlah')";
                mysqli_query($koneksi, $sql);
            }

            // Kosongkan keranjang setelah transaksi
            unset($_SESSION["cart"]);
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Pesanan berhasil dibuat!',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        window.location='cetak.php?id=" . $id_transaksi . "';
                    });
                });
            </script>";
        }
    }
}
?>

<!doctype html>
<html lang="id" data-bs-theme="auto">

<head>
    <script src="assets/js/color-modes.js"></script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Keranjang Belanja - Teh Idaman</title>

    <link href="assets/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="theme-hijau.css" rel="stylesheet" />
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .cart-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }
        .product-img-thumb {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
        }
        .table > :not(caption) > * > * {
            padding: 1rem 0.75rem;
        }
    </style>
</head>

<body>
    <!-- Header / Navbar -->
    <header data-bs-theme="dark">
        <div class="navbar navbar-dark bg-dark shadow-sm">
            <div class="container">
                <a href="carousell.php" class="navbar-brand d-flex align-items-center gap-2">
                    <strong>Teh Idaman</strong>
                </a>
                <a href="carousell.php" class="btn btn-outline-light btn-sm">&larr; Lanjut Belanja</a>
            </div>
        </div>
    </header>

    <main class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card cart-card bg-body-tertiary p-4">
                        <h3 class="fw-bold mb-4 text-center">🛒 Keranjang Belanja Anda</h3>

                        <?php if (!empty($_SESSION["cart"])) { ?>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="table-dark">
                                        <tr class="text-center">
                                            <th scope="col" class="text-start">Produk</th>
                                            <th scope="col" style="width: 120px;">Harga</th>
                                            <th scope="col" style="width: 100px;">Jumlah</th>
                                            <th scope="col" style="width: 150px;">Total</th>
                                            <th scope="col" style="width: 100px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $total = 0;
                                        foreach ($_SESSION["cart"] as $key => $value) {
                                            $subtotal = $value["jumlah"] * $value["harga"];
                                            $total += $subtotal;
                                        ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <img src="images/<?= htmlspecialchars($value["foto"]) ?>" class="product-img-thumb shadow-sm" alt="<?= htmlspecialchars($value["nama"]) ?>">
                                                        <span class="fw-semibold fs-6"><?= htmlspecialchars($value["nama"]) ?></span>
                                                    </div>
                                                </td>
                                                <td class="text-center">Rp <?= number_format($value["harga"], 0, ',', '.') ?></td>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary px-3 py-2 fs-6"><?= $value["jumlah"] ?></span>
                                                </td>
                                                <td class="text-center fw-bold text-success">
                                                    Rp <?= number_format($subtotal, 0, ',', '.') ?>
                                                </td>
                                                <td class="text-center">
                                                    <a href="#" onclick="confirmHapus('<?= $value['id']; ?>'); return false;" class="btn btn-outline-danger btn-sm">
                                                         Hapus
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Ringkasan Grand Total -->
                            <div class="row align-items-center mt-4 pt-3 border-top">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <a href="carousell.php" class="btn btn-outline-secondary">
                                        &larr; Tambah Menu Lain
                                    </a>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <div class="mb-3">
                                        <span class="fs-5 text-body-secondary me-2">Grand Total:</span>
                                        <span class="fs-3 fw-bold text-success">Rp <?= number_format($total, 0, ',', '.') ?></span>
                                    </div>
                                    <a href="keranjang.php?aksi=beli" class="btn btn-success btn-lg px-5 py-2 fw-semibold shadow-sm">
                                        Checkout & Beli Sekarang &rarr;
                                    </a>
                                </div>
                            </div>

                        <?php } else { ?>
                            <!-- Tampilan Saat Keranjang Kosong -->
                            <div class="text-center py-5">
                                <h4 class="fw-bold">Keranjang Belanja Anda Kosong</h4>
                                <p class="text-body-secondary mb-4">Yuk, pilih minuman kesukaanmu dan tambahkan ke keranjang!</p>
                                <a href="carousell.php" class="btn btn-success btn-lg px-4 fw-semibold">
                                    Lihat Menu Teh Idaman
                                </a>
                            </div>
                        <?php } ?>

                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="text-center text-body-secondary py-4 border-top mt-auto">
        <div class="container">
            <p class="mb-0">&copy; <?= date('Y') ?> Teh Idaman Indonesia. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function confirmHapus(id) {
        Swal.fire({
            title: 'Yakin menghapus?',
            text: 'Produk akan dihapus dari keranjang belanja Anda.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'keranjang.php?aksi=hapus&id=' + id;
            }
        });
    }
    </script>
</body>

</html>