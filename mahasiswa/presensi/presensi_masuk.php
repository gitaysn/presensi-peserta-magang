<!-- Include webcam js: -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js" integrity="sha512-dQIiHSl2hr3NWKKLycPndtpbh5iaHLo6MwrXm7F0FM5e+kL2U16oE9uIwPHUl6fQBeCthiEuV/rzP3MiAB8Vfw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<!-- Include leaflet js: -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

<style>
    #map {
        height: 300px;
    }
</style>

<?php 
ob_start();
session_start();

// Cek session login
if (!isset($_SESSION["login"])) {
    header("Location: ../../auth/login.php?pesan=belum_login"); 
    exit;
} elseif ($_SESSION["role"] != 'mahasiswa') {
    header("Location: ../../auth/login.php?pesan=tolak_akses");
    exit;
}

$judul = 'Presensi Masuk';
include('../layout/header.php'); 
include_once("../../config.php"); 

if (isset($_POST['tombol_masuk'])) {
    $latitude_mahasiswa = $_POST['latitude_mahasiswa'];
    $longitude_mahasiswa = $_POST['longitude_mahasiswa'];
    $latitude_kantor = $_POST['latitude_kantor'];
    $longitude_kantor = $_POST['longitude_kantor'];
    $radius = $_POST['radius'];
    $zona_waktu = $_POST['zona_waktu'];
    $tanggal_masuk = $_POST['tanggal_masuk'];
    $jam_masuk = $_POST['jam_masuk'];
}

if(empty($latitude_mahasiswa) || empty($longitude_mahasiswa)) {
    $_SESSION['gagal'] = 'Presensi gagal, lokasi Anda belum aktif';
    header("Location: ../home/home.php");
    exit; 
}

if(empty($latitude_kantor) || empty($longitude_kantor)) {
    $_SESSION['gagal'] = 'Presensi gagal, koordinat kantor belum di setting';
    header("Location: ../home/home.php");
    exit; 
}

// Menghitung jarak
$perbedaan_koordinat = $longitude_kantor - $longitude_mahasiswa; // Koreksi
$jarak = sin(deg2rad($latitude_mahasiswa)) * sin(deg2rad($latitude_kantor)) +
         cos(deg2rad($latitude_mahasiswa)) * cos(deg2rad($latitude_kantor)) * cos(deg2rad($perbedaan_koordinat));
$jarak = acos($jarak);
$jarak = rad2deg($jarak);
$mil = $jarak * 60 * 1.1515;
$jarak_km = $mil * 1.609344;
$jarak_meter = $jarak_km * 1000;

if ($jarak_meter > $radius) {
    $_SESSION['gagal'] = 'Anda berada di luar area kantor';
    header("Location: ../home/home.php");
    exit; 
} else {  
?>
    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div id="map"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body" style="margin: auto;">
                            <input type="hidden" id="id" value="<?= $_SESSION['id'] ?>">
                            <input type="hidden" id="tanggal_masuk" value="<?= $tanggal_masuk ?>">
                            <input type="hidden" id="jam_masuk" value="<?= $jam_masuk ?>">
                            <div id="my_camera"></div>
                            <div id="my_result"></div>
                            <div><?= date('d F Y', strtotime($tanggal_masuk)) . ' - ' . $jam_masuk ?></div>
                            <button class="btn btn-primary mt-2" id="ambil-foto">Masuk</button>
                        </div>
                    </div>
                </div>  
            </div>
        </div>
    </div>
    
    <canvas id="canvas" width="320" height="240" style="display:none;"></canvas>

    <script language="JavaScript">
        // Pengaturan Webcam
        Webcam.set({
            width: 320,
            height: 240,
            dest_width: 320,
            dest_height: 240,
            image_format: 'jpeg',
            jpeg_quality: 90,
            force_flash: false
        });
        Webcam.attach('#my_camera');

        document.getElementById('ambil-foto').addEventListener('click', function() {
            let id = document.getElementById('id').value;
            let tanggal_masuk = document.getElementById('tanggal_masuk').value;
            let jam_masuk = document.getElementById('jam_masuk').value;

            Webcam.snap(function(data_uri) {
                // Menggambar gambar ke canvas
                let canvas = document.getElementById('canvas');
                let context = canvas.getContext('2d');
                let img = new Image();
                
                img.onload = function() {
                    // Membalik gambar di canvas
                    context.save();
                    context.scale(-1, 1); // Membalik gambar secara horizontal
                    context.drawImage(img, -canvas.width, 0);
                    context.restore();

                    // Ambil data dari canvas yang telah dibalik
                    let flipped_data_uri = canvas.toDataURL('image/jpeg');

                    // Mengirim data ke server
                    var xhttp = new XMLHttpRequest();
                    xhttp.onreadystatechange = function() {
                        document.getElementById('my_result').innerHTML = '<img src="'+flipped_data_uri+'"/>';
                        if (xhttp.readyState == 4 && xhttp.status == 200) {
                            window.location.href = '../home/home.php';
                        }
                    };
                    xhttp.open("POST", "presensi_masuk_aksi.php", true);
                    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xhttp.send(
                        'photo=' + encodeURIComponent(flipped_data_uri) +
                        '&id=' + id +
                        '&tanggal_masuk=' + tanggal_masuk +
                        '&jam_masuk=' + jam_masuk 
                    );
                };

                img.src = data_uri; // Set src untuk gambar
            });
        });

        // Inisialisasi peta Leaflet
        let latitude_ktr = <?= $latitude_kantor; ?>;
        let longitude_ktr = <?= $longitude_kantor; ?>;
        let latitude_mah = <?= $latitude_mahasiswa; ?>;
        let longitude_mah = <?= $longitude_mahasiswa; ?>;

        let map = L.map('map').setView([latitude_ktr, longitude_ktr], 13);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        var marker = L.marker([latitude_ktr, longitude_ktr]).addTo(map);
        var circle = L.circle([latitude_mah, longitude_mah], {
            color: 'red',
            fillColor: '#f03',
            fillOpacity: 0.5,
            radius: 500
        }).addTo(map).bindPopup("Lokasi Anda saat ini").openPopup();
    </script>

<?php } ?>

<?php include('../layout/footer.php') ?>
