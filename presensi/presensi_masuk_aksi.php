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
$id_mahasiswa = $_POST['id'];
$tanggal_masuk = $_POST['tanggal_masuk'];
$jam_masuk = $_POST['jam_masuk'];

$foto = $file_foto;
$foto = str_replace('data:image/jpeg;base64,','', $foto);
$foto = str_replace(' ', '+', $foto);
$data = base64_decode($foto);
$nama_file = 'foto/'.'masuk'.date('Y-m-d').'_'.date('H-i-s').'.png'; // Mengganti : dengan -
$file = 'masuk'.date('Y-m-d').'_'.date('H-i-s').'.png'; // Mengganti : dengan -
file_put_contents($nama_file, $data);

$result = mysqli_query($connection, "INSERT INTO presensi(id_mahasiswa, tanggal_masuk, jam_masuk, foto_masuk) VALUES ('$id_mahasiswa', '$tanggal_masuk', '$jam_masuk', '$file')");

if($result) {
    $_SESSION['berhasil'] = "Presensi masuk berhasil";
} else {
    die('Query Error: ' . mysqli_error($connection)); // Menampilkan kesalahan query
    $_SESSION['gagal'] = "Presensi masuk gagal";
}

