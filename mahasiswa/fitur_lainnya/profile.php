<?php 
session_start();

// Cek apakah pengguna sudah login dan memiliki role 'admin'
if (!isset($_SESSION["login"])) {
    header("Location: ../../auth/login.php?pesan=belum_login"); 
    exit;
} elseif ($_SESSION["role"] != 'mahasiswa') {
    header("Location: ../../auth/login.php?pesan=tolak_akses");
    exit;
}

$judul = "";
include('../layout/header.php');
require_once('../../config.php');

// Ambil data mahasiswa berdasarkan id pengguna yang sedang login
$id = $_SESSION['id'];
$result = mysqli_query($connection, "SELECT users.id_mahasiswa, users.username, users.status, 
users.role, mahasiswa.* FROM users JOIN mahasiswa ON users.id_mahasiswa = mahasiswa.id WHERE mahasiswa.id = $id");
?>

<?php while ($mahasiswa = mysqli_fetch_array($result)): ?>
    <!-- Page body -->
    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <div class="col-md-4"></div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <table class="table mt-4">
                                <tr>
                                    <td>Nama</td>
                                    <td>: <?= $mahasiswa['nama'] ?></td>
                                </tr>

                                <tr>
                                    <td>Jenis Kelamin</td>
                                    <td>: <?= $mahasiswa['jenis_kelamin'] ?></td>
                                </tr>

                                <tr>
                                    <td>No. Handphone</td>
                                    <td>: <?= $mahasiswa['no_handphone'] ?></td>
                                </tr>

                                <tr>
                                    <td>Jurusan</td>
                                    <td>: <?= $mahasiswa['jurusan'] ?></td>
                                </tr>

                                <tr>
                                    <td>Username</td>
                                    <td>: <?= $mahasiswa['username'] ?></td>
                                </tr>

                                <tr>
                                    <td>Role</td>
                                    <td>: <?= $mahasiswa['role'] ?></td>
                                </tr>

                                <tr>
                                    <td>Lokasi Presensi</td>
                                    <td>: <?= $mahasiswa['lokasi_presensi'] ?></td>
                                </tr>

                                <tr>
                                    <td>Status</td>
                                    <td>: <?= $mahasiswa['status'] ?></td>
                                </tr>

                            </table>
                        </div>

                    </div>
                </div>
                <div class="col-md-4"></div>
            </div>
        </div>
    </div>
<?php endwhile; ?>

<?php include('../layout/footer.php'); ?>
