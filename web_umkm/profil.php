<?php
session_start();
include 'koneksi.php';

// Akses hanya untuk user yang sudah login
if (!isset($_SESSION['id'])) {
    header("Location: login/login.php");
    exit;
}

$id = $_SESSION['id'];

// Helper: cek password (support plain-text lama & hash baru)
function verify_password($input, $stored) {
    if (empty($stored)) return false;
    // Deteksi hash bcrypt ($2y$, $2a$, $2b$)
    if (preg_match('/^\$2[aby]\$.+/', $stored)) {
        return password_verify($input, $stored);
    }
    // Plain-text (cara lama)
    return ($input === $stored);
}

// ============================================================
// 1. HANDLE POST (update profil)
// ============================================================
$success = '';
$error   = '';
$old     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama       = trim($_POST['nama'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $hp      = trim($_POST['hp'] ?? '');
    $alamat     = trim($_POST['alamat'] ?? '');
    $pass_lama  = $_POST['password_lama'] ?? '';
    $pass_baru  = $_POST['password_baru'] ?? '';
    $pass_konf  = $_POST['password_konfirmasi'] ?? '';
    $hapus_poto  = isset($_POST['hapus_poto']) ? (int)$_POST['hapus_poto'] : 0;

    $old = compact('nama', 'email', 'hp', 'alamat');

    // Ambil data user lama
    $q_user = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE id='$id' LIMIT 1");
    $user   = mysqli_fetch_array($q_user);

    if (!$user) {
        $error = "User tidak ditemukan.";
    } elseif ($nama === '') {
        $error = "Nama lengkap wajib diisi.";
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } else {
        // Cek email unik (jika diisi)
        if ($email !== '') {
            $cek = mysqli_query($koneksi, "SELECT id FROM tb_user WHERE email='" . mysqli_real_escape_string($koneksi, $email) . "' AND id <> '$id' LIMIT 1");
            if (mysqli_num_rows($cek) > 0) {
                $error = "Email sudah digunakan user lain.";
            }
        }
    }

    // Handle password (opsional)
    $update_password = '';
    if (!$error && ($pass_lama !== '' || $pass_baru !== '' || $pass_konf !== '')) {
        if ($pass_lama === '' || $pass_baru === '' || $pass_konf === '') {
            $error = "Untuk ganti password, ketiga kolom password wajib diisi.";
        } elseif (!verify_password($pass_lama, $user['password'])) {
            $error = "Password lama salah.";
        } elseif (strlen($pass_baru) < 6) {
            $error = "Password baru minimal 6 karakter.";
        } elseif ($pass_baru !== $pass_konf) {
            $error = "Konfirmasi password baru tidak cocok.";
        } else {
            // Simpan sebagai hash bcrypt (upgrade dari plain-text otomatis)
            $hash_baru       = password_hash($pass_baru, PASSWORD_DEFAULT);
            $update_password = ", password='" . mysqli_real_escape_string($koneksi, $hash_baru) . "'";
        }
    }

    // Handle foto profil
    $nama_poto_baru = $user['poto'] ?? '';

    if (!$error && $hapus_poto === 1 && !empty($user['poto'])) {
        $path_lama = "images/" . $user['poto'];
        if (file_exists($path_lama)) @unlink($path_lama);
        $nama_poto_baru = '';
    }

    if (!$error && isset($_FILES['poto']) && $_FILES['poto']['error'] === UPLOAD_ERR_OK) {
        $file     = $_FILES['poto'];
        $max_size = 2 * 1024 * 1024; // 2MB

        if ($file['size'] > $max_size) {
            $error = "Ukuran foto melebihi 2MB.";
        } else {
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
            $finfo   = finfo_open(FILEINFO_MIME_TYPE);
            $mime    = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!array_key_exists($mime, $allowed)) {
                $error = "Format foto tidak didukung. Gunakan JPG/PNG/GIF/WEBP.";
            } else {
                $ext            = $allowed[$mime];
                $nama_poto_baru = 'user_' . $id . '_' . time() . '.' . $ext;
                $tujuan         = "images/" . $nama_poto_baru;

                if (!is_dir("images")) @mkdir("images", 0777, true);

                if (!move_uploaded_file($file['tmp_name'], $tujuan)) {
                    $error = "Gagal mengunggah foto. Coba lagi.";
                } elseif (!empty($user['poto']) && $user['poto'] !== $nama_poto_baru) {
                    $path_lama = "images/" . $user['poto'];
                    if (file_exists($path_lama)) @unlink($path_lama);
                }
            }
        }
    }

    // Eksekusi UPDATE
    if (!$error) {
        $nama_e   = mysqli_real_escape_string($koneksi, $nama);
        $email_e  = mysqli_real_escape_string($koneksi, $email);
        $hp_e  = mysqli_real_escape_string($koneksi, $hp);
        $alamat_e = mysqli_real_escape_string($koneksi, $alamat);
        $foto_e   = mysqli_real_escape_string($koneksi, $nama_poto_baru);

        $sql = "UPDATE tb_user SET
                    nama   = '$nama_e',
                    email  = '$email_e',
                    hp  = '$hp_e',
                    alamat = '$alamat_e',
                    poto   = '$foto_e'
                    $update_password
                WHERE id = '$id'";

        if (mysqli_query($koneksi, $sql)) {
            $success = "Profil berhasil diperbarui.";
            $old     = [];
        } else {
            $error = "Gagal menyimpan perubahan: " . mysqli_error($koneksi);
        }
    }
}

// ============================================================
// 2. AMBIL DATA USER (selalu fresh setelah update)
// ============================================================
$q_user = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE id='$id' LIMIT 1");
$user   = mysqli_fetch_array($q_user);

if (!$user) {
    header("Location: logout.php");
    exit;
}

// Nilai form: pakai input lama (jika ada error) atau data DB
$f_nama   = $old['nama']   ?? $user['nama']   ?? '';
$f_email  = $old['email']  ?? $user['email']  ?? '';
$f_hp  = $old['hp']  ?? $user['hp']  ?? '';
$f_alamat = $old['alamat'] ?? $user['alamat'] ?? '';

$foto_url = (!empty($user['poto']) && file_exists("images/" . $user['poto']))
    ? 'images/' . htmlspecialchars($user['poto'])
    : 'https://placehold.co/160x160?text=Foto';
?>
<!doctype html>
<html lang="id" data-bs-theme="auto">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Profil Saya - Carousel</title>
    <link href="assets/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="carousel.css" rel="stylesheet" />
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .profile-avatar {
            width: 160px;
            height: 160px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid var(--bs-border-color, #dee2e6);
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
        }
        .profile-form {
            max-width: 760px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
<header data-bs-theme="dark">
    <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="carousell.php">Teh Idaman</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <ul class="navbar-nav me-auto mb-2 mb-md-0">
                    <li class="nav-item"><a class="nav-link" href="carousell.php">Home</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php if (!empty($user['poto']) && file_exists("images/" . $user['poto'])): ?>
                                <img src="images/<?= htmlspecialchars($user['poto']) ?>" class="rounded-circle me-2" width="28" height="28" style="object-fit:cover" alt="">
                            <?php else: ?>
                                <span class="me-2 d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary text-white" style="width:28px;height:28px;font-size:12px;font-weight:600;">
                                    <?= strtoupper(substr($user['nama'] ?? 'U', 0, 1)) ?>
                                </span>
                            <?php endif; ?>
                            <?= htmlspecialchars($user['nama'] ?? 'Akun') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item active" href="profil.php">Profil Saya</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<main class="py-5" style="margin-top: 56px;">
    <div class="container">
        <div class="profile-form">

            <h2 class="mb-4">Profil Saya</h2>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Avatar + identitas singkat -->
            <div class="text-center mb-4">
                <img id="avatarPreview" src="<?= $foto_url ?>" class="profile-avatar" alt="Foto Profil">
                <h3 class="mt-3 mb-0"><?= htmlspecialchars($user['nama']) ?></h3>
                <p class="text-muted mb-0">@<?= htmlspecialchars($user['username']) ?></p>
            </div>

            <!-- Form edit (inline di halaman yang sama) -->
            <form action="profil.php" method="POST" enctype="multipart/form-data" novalidate>

                <!-- Foto Profil -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Foto Profil</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="poto" class="form-label">Upload Foto Baru</label>
                            <input type="file" class="form-control" id="poto" name="poto" accept="image/*">
                            <div class="form-text">Format: JPG, JPEG, PNG, GIF, WEBP. Maks 2MB.</div>
                        </div>
                        <?php if (!empty($user['poto'])): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="hapus_poto" name="hapus_poto" value="1">
                                <label class="form-check-label text-danger" for="hapus_poto">
                                    Hapus foto profil saat ini
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Data Profil -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Profil</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama" required
                                   value="<?= htmlspecialchars($f_nama) ?>"
                                   placeholder="Masukkan nama lengkap">
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                            <div class="form-text">Username tidak dapat diubah.</div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= htmlspecialchars($f_email) ?>"
                                   placeholder="nama@email.com">
                        </div>

                        <div class="mb-3">
                            <label for="hp" class="form-label">No. HP</label>
                            <input type="tel" class="form-control" id="hp" name="hp"
                                   value="<?= htmlspecialchars($f_hp) ?>"
                                   placeholder="08xxxxxxxxxx">
                        </div>

                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3" placeholder="Alamat lengkap"><?= htmlspecialchars($f_alamat) ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Ganti Password (opsional) -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Keamanan <small class="text-muted fw-normal">(opsional)</small></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="password_lama" class="form-label">Password Lama</label>
                            <input type="password" class="form-control" id="password_lama" name="password_lama" placeholder="Kosongkan jika tidak ganti password">
                        </div>
                        <div class="mb-3">
                            <label for="password_baru" class="form-label">Password Baru</label>
                            <input type="password" class="form-control" id="password_baru" name="password_baru" placeholder="Minimal 6 karakter">
                        </div>
                        <div class="mb-3">
                            <label for="password_konfirmasi" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" id="password_konfirmasi" name="password_konfirmasi" placeholder="Ulangi password baru">
                        </div>
                        <div class="form-text text-muted">
                            Isi ketiga kolom di atas hanya jika ingin mengganti password.
                            Password baru akan disimpan dalam bentuk hash (lebih aman).
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="carousell.php" class="btn btn-outline-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>

        </div>
    </div>
</main>

<script src="assets/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Preview foto sebelum upload
document.getElementById('poto').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('avatarPreview').src = ev.target.result;
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php if ($success): ?>
<script>
  Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: '<?= htmlspecialchars($success); ?>',
    confirmButtonColor: '#0d6efd'
  });
</script>
<?php endif; ?>

<?php if ($error): ?>
<script>
  Swal.fire({
    icon: 'error',
    title: 'Gagal',
    text: '<?= htmlspecialchars($error); ?>',
    confirmButtonColor: '#d33'
  });
</script>
<?php endif; ?>
</body>
</html>