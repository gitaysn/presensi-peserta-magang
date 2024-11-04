<?php 
session_start();
if (!isset($_SESSION["login"])) {
    header("Location: ../../auth/login.php?pesan=belum_login");
    exit;
} elseif ($_SESSION["role"] != 'mahasiswa') {
    header("Location: ../../auth/login.php?pesan=tolak_akses");
    exit;
}

$judul = 'Home';
include('../layout/header.php'); 
include_once("../../config.php");

$lokasi_presensi = $_SESSION['lokasi_presensi'];
$result = mysqli_query($connection, "SELECT * FROM lokasi_presensi WHERE nama_lokasi = '$lokasi_presensi'");

if ($result) {
    $lokasi = mysqli_fetch_array($result);
    $latitude_kantor = $lokasi['latitude'];
    $longitude_kantor = $lokasi['longitude'];
    $radius = $lokasi['radius'];
    $zona_waktu = $lokasi['zona_waktu'];
    $jam_pulang = $lokasi['jam_pulang'];
}

if ($zona_waktu == 'WIB') {
    date_default_timezone_set('Asia/Jakarta');
} elseif ($zona_waktu == 'WITA') {
    date_default_timezone_set('Asia/Makassar');
} elseif ($zona_waktu == 'WIT') {
    date_default_timezone_set('Asia/Jayapura');
}
?>

<style>
  .parent_date, .parent_clock {
    display: grid;
    grid-template-columns: repeat(5, auto);
    text-align: center;
    justify-content: center;
  }

  .parent_date {
    font-size: 20px;
  }

  .parent_clock {
    font-size: 30px;
    font-weight: bold;
  }
</style>

<div class="page-body">
  <div class="container-xl">
    <div class="row">
      <div class="col-md-2"></div>

      <!-- Card Presensi Masuk -->
      <div class="col-md-4">
        <div class="card text-center h-100">
          <div class="card-header">Presensi Masuk</div>
          <div class="card-body">

            <?php 
            $id_mahasiswa = $_SESSION['id'];
            $tanggal_hari_ini = date('Y-m-d');

            // Cek jika query berhasil dijalankan
            $cek_presensi_masuk = mysqli_query($connection, "SELECT * FROM presensi WHERE id_mahasiswa = '$id_mahasiswa' AND tanggal_masuk = '$tanggal_hari_ini'");
            ?>

            <?php if(mysqli_num_rows($cek_presensi_masuk) === 0) { ?>
            <div class="parent_date">
              <div id="tanggal_masuk"></div>
              <div class="ms-2"></div>
              <div id="bulan_masuk"></div>
              <div class="ms-2"></div>
              <div id="tahun_masuk"></div>
            </div>

            <div class="parent_clock">
              <div id="jam_masuk"></div>
              <div>:</div>
              <div id="menit_masuk"></div>
              <div>:</div>
              <div id="detik_masuk"></div>
            </div>

            <form method="POST" action="<?= base_url('mahasiswa/presensi/presensi_masuk.php') ?>">
              <input type="hidden" name="latitude_mahasiswa" id="latitude_mahasiswa">
              <input type="hidden" name="longitude_mahasiswa" id="longitude_mahasiswa">
              <input type="hidden" value="<?= $latitude_kantor ?>" name="latitude_kantor">
              <input type="hidden" value="<?= $longitude_kantor ?>" name="longitude_kantor">
              <input type="hidden" value="<?= $radius ?>" name="radius">
              <input type="hidden" value="<?= $zona_waktu ?>" name="zona_waktu">
              <input type="hidden" value="<?= date('Y-m-d') ?>" name="tanggal_masuk">
              <input type="hidden" value="<?= date('H:i:s') ?>" name="jam_masuk">

              <button type="submit" name="tombol_masuk" class="btn btn-primary mt-3">Masuk</button>
            </form>

            <?php } else { ?>

              <i class="fa-regular fa-circle-check fa-4x text-success"></i>
              <h4 class="my-3">Anda telah melakukan <br> presensi masuk</h4>

              <?php } ?>
          </div>
        </div>
      </div>

      <!-- Card Presensi Keluar -->
      <div class="col-md-4">
        <div class="card text-center h-100">
          <div class="card-header">Presensi Keluar</div>
          <div class="card-body">
              <?php
                  $ambil_data_presensi = (mysqli_query($connection, "SELECT * FROM presensi WHERE id_mahasiswa = '$id_mahasiswa' AND tanggal_masuk = '$tanggal_hari_ini'"))
              ?>
          <?php 
          $waktu_sekarang = date('H:i:s');
          if(strtotime($waktu_sekarang) <= strtotime($jam_pulang)) { ?>

            <i class="fa-regular fa-circle-xmark fa-4x text-danger"></i>
            <h4 class="my-3">Belum waktunya pulang</h4>

            <?php } elseif(strtotime($waktu_sekarang) >= strtotime($jam_pulang) && mysqli_num_rows($ambil_data_presensi) == 0) { ?>
              <i class="fa-regular fa-circle-xmark fa-4x text-danger"></i>
              <h4 class="my-3">Silahkan melakukan presensi masuk <br> terlebih dahulu</h4>
          <?php } else { ?>

            <?php while($cek_presensi_keluar = mysqli_fetch_array($ambil_data_presensi)) { ?>

              <?php if (($cek_presensi_keluar['tanggal_masuk']) && $cek_presensi_keluar['tanggal_keluar'] == '0000-00-00') { ?>
          
            <div class="parent_date">
              <div id="tanggal_keluar"></div>
              <div class="ms-2"></div>
              <div id="bulan_keluar"></div>
              <div class="ms-2"></div>
              <div id="tahun_keluar"></div>
            </div>

            <div class="parent_clock">
              <div id="jam_keluar"></div>
              <div>:</div>
              <div id="menit_keluar"></div>
              <div>:</div>
              <div id="detik_keluar"></div>
            </div>

            <form method="POST" action="<?= base_url('mahasiswa/presensi/presensi_keluar.php') ?>">
              <input type="hidden" name="id" value="<?= $cek_presensi_keluar['id'] ?>">
              <input type="hidden" name="latitude_mahasiswa" id="latitude_mahasiswa">
              <input type="hidden" name="longitude_mahasiswa" id="longitude_mahasiswa">
              <input type="hidden" value="<?= $latitude_kantor ?>" name="latitude_kantor">
              <input type="hidden" value="<?= $longitude_kantor ?>" name="longitude_kantor">
              <input type="hidden" value="<?= $radius ?>" name="radius">
              <input type="hidden" value="<?= $zona_waktu ?>" name="zona_waktu">
              <input type="hidden" value="<?= date('Y-m-d') ?>" name="tanggal_keluar">
              <input type="hidden" value="<?= date('H:i:s') ?>" name="jam_keluar">
              
              <button type="submit" name="tombol-keluar" class="btn btn-danger mt-3">Keluar</button>
            </form>

            <?php } else { ?>
              <i class="fa-regular fa-circle-check fa-4x text-success"></i>
              <h4 class="my-3">Anda telah melakukan <br> presensi keluar</h4>
              <?php } ?>

            <?php } ?>

          <?php } ?>
          </div>
        </div>
      </div>

      <div class="col-md-2"></div>
    </div>
  </div>
</div>

<script>
  // Fungsi untuk update waktu presensi masuk
  function waktuMasuk() {
    const waktu = new Date();
    document.getElementById("tanggal_masuk").innerHTML = waktu.getDate();
    document.getElementById("bulan_masuk").innerHTML = namaBulan[waktu.getMonth()];
    document.getElementById("tahun_masuk").innerHTML = waktu.getFullYear();
    document.getElementById("jam_masuk").innerHTML = waktu.getHours();
    document.getElementById("menit_masuk").innerHTML = waktu.getMinutes();
    document.getElementById("detik_masuk").innerHTML = waktu.getSeconds();
    setTimeout(waktuMasuk, 1000);
  }

  // Fungsi untuk update waktu presensi keluar
  function waktuKeluar() {
    const waktu = new Date();
    document.getElementById("tanggal_keluar").innerHTML = waktu.getDate();
    document.getElementById("bulan_keluar").innerHTML = namaBulan[waktu.getMonth()];
    document.getElementById("tahun_keluar").innerHTML = waktu.getFullYear();
    document.getElementById("jam_keluar").innerHTML = waktu.getHours();
    document.getElementById("menit_keluar").innerHTML = waktu.getMinutes();
    document.getElementById("detik_keluar").innerHTML = waktu.getSeconds();
    setTimeout(waktuKeluar, 1000);
  }

  // Daftar nama bulan
  const namaBulan = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

  // Panggil fungsi untuk menampilkan waktu
  window.setTimeout(waktuMasuk, 1000);
  window.setTimeout(waktuKeluar, 1000);

  // Mendapatkan lokasi geografis
  function getLocation() {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(showPosition);
    } else {
      alert("Browser Anda tidak mendukung");
    }
  }

  // Menampilkan posisi
  function showPosition(position) {
    document.getElementById('latitude_mahasiswa').value = position.coords.latitude;
    document.getElementById('longitude_mahasiswa').value = position.coords.longitude;
  }

  // Jalankan fungsi geolocation
  getLocation();
</script>

<?php include('../layout/footer.php') ?>
