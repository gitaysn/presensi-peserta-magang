<?php  
ob_start();
session_start();
if (!isset($_SESSION["login"])) {
    header("Location: ../../auth/login.php?pesan=belum_login");
    exit;
} elseif ($_SESSION["role"] != 'mahasiswa') {
    header("Location: ../../auth/login.php?pesan=tolak_akses");
    exit;
}

$judul = 'Ketidakhadiran';
include('../layout/header.php'); 
include_once("../../config.php");

$id = $_SESSION['id'];
$result = mysqli_query($connection, "SELECT * FROM ketidakhadiran WHERE id_mahasiswa = '$id' ORDER BY id DESC");

?>

<div class="page-body">
  <div class="container-xl">

    <a href="<?= base_url('mahasiswa/ketidakhadiran/pengajuan_ketidakhadiran.php') ?>" class="btn btn-primary">Tambah Data</a>
    <table class="table table-bordered mt-2">
        <tr class="text-center">
            <th>No.</th>
            <th>Tanggal</th>
            <th>Keterangan</th>
            <th>Deskripsi</th>
            <th>File</th>
            <th>Status Pengajuan</th>
            <th>Aksi</th>
        </tr>

        <?php if (mysqli_num_rows($result) == 0) : ?>
            <tr>
                <td colspan="7">Data ketidakhadiran masih kosong</td>
            </tr>
        <?php else : ?>
            <?php $no = 1; ?>
            <?php while ($data = mysqli_fetch_array($result)) : ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= date('d F Y', strtotime($data['tanggal'])) ?></td>
                    <td><?= $data['keterangan'] ?></td>
                    <td><?= $data['deskripsi'] ?></td>
                    <td class="text-center">
                        <a target="_blank" href="<?= base_url('assets/file_ketidakhadiran/'.$data['file'])?>" class="badge bg-primary">Download</a>
                    </td>
                    <td><?= $data['status_pengajuan'] ?></td>
                    <td class="text-center">
                        <a href="edit.php?id=<?= $data['id'] ?>" class="badge bg-success">Update</a>
                        <a href="hapus.php?id=<?= $data['id'] ?>" class="badge bg-danger tombol-hapus">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php endif; ?>
    </table>

  </div>
</div>

<?php include('../layout/footer.php') ?>
