<?php 
ob_start();
session_start();

// Cek apakah pengguna sudah login dan memiliki role 'admin'
if (!isset($_SESSION["login"])) {
    header("Location: ../../auth/login.php?pesan=belum_login"); 
    exit;
} elseif ($_SESSION["role"] != 'mahasiswa') {
    header("Location: ../../auth/login.php?pesan=tolak_akses");
    exit;
}

$judul = "Ubah Password";
include('../layout/header.php');
require_once('../../config.php');

if (isset($_POST['update'])) {
    $id = $_SESSION['id'];
    $password_baru = password_hash($_POST['password_baru'], PASSWORD_DEFAULT);
    $ulangi_password_baru = password_hash($_POST['ulangi_password_baru'], PASSWORD_DEFAULT);
    $pesan_kesalahan = [];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (empty($_POST['password_baru'])) {
            $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Password baru wajib diisi";
        }
        if (empty($_POST['ulangi_password_baru'])) {
            $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Ulangi password baru wajib diisi";
        }
        if ($_POST['password_baru'] != $_POST['ulangi_password_baru']) {
            $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Password tidak cocok";
        }

        if (!empty($pesan_kesalahan)) {
            $_SESSION['validasi'] = implode("<br>", $pesan_kesalahan);
        } else {
            $mahasiswa = mysqli_query($connection, "UPDATE users SET password = '$password_baru' WHERE id_mahasiswa = $id");

            if ($mahasiswa) {
                $_SESSION['berhasil'] = 'Password berhasil diubah';
                header("Location: ../home/home.php");
                exit;
            } else {
                $_SESSION['validasi'] = 'Terjadi kesalahan saat mengubah password';
            }
        }
    }
}
?>

    <!-- Page body -->
    <div class="page-body">
    <div class="container-xl">
        <form action="" method="POST">
            <div class="card col-md-5">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="password_baru">Password Baru</label>
                        <input type="password" name="password_baru" id="password_baru" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="ulangi_password_baru">Ulangi Password Baru</label>
                        <input type="password" name="ulangi_password_baru" id="ulangi_password_baru" class="form-control">
                    </div>
                    <input type="hidden" name="id" value="<?= $_SESSION['id']; ?>">
                    <button type="submit" class="btn btn-primary" name="update">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>


<?php include('../layout/footer.php'); ?>
