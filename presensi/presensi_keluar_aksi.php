<?php  

ob_start();
session_start();
if(!isset($_SESSION["login"])) {
    header("Location: ../../auth/login.php?pesan=belum_login");
} else if ($_SESSION["role"] != 'mahasiswq') {
    header("Location: ../../auth/login.php?pesan=tolak_akses");
}

include_once("../../config.php");

$file_foto = $_POST['photos'];
$id_presensi = $_POST['id'];
$tanggal_keluar = $_POST['tanggal_keluar'];
$jam_keluar = $_POST['jam_keluar'];

$foto = $file_foto;
$foto = str_replace('data:image/jpeg;base64,','', $foto);
$foto = str_replace(' ', '+', $foto);
$data = base64_decode($foto);
$nama_file = 'foto/'.'keluar'.date('Y-m-d').'_'.date('H-i-s').'.png'; // Mengganti : dengan -
$file = 'keluar'.date('Y-m-d').'_'.date('H-i-s').'.png'; // Mengganti : dengan -
file_put_contents($nama_file, $data);

$result = mysqli_query($connection, "UPDATE presensi SET tanggal_keluar='$tanggal_keluar', jam_keluar='$jam_keluar', foto_keluar= '$file' WHERE id=$id_presensi");

if($result) {
    $_SESSION['berhasil'] = "Presensi keluar berhasil";
} else {
    die('Query Error: ' . mysqli_error($connection)); // Menampilkan kesalahan query
    $_SESSION['gagal'] = "Presensi keluar gagal";
}

