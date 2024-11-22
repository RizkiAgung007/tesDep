<?php
$servername = "localhost"; // Sesuai dengan yang terlihat di gambar
$username = "root"; // Asumsi default untuk localhost
$password = ""; // Asumsi tidak ada password untuk localhost
$dbname = "projek"; // Sesuai dengan nama database di gambar
// $dbname = "product by cabang"; // Sesuai dengan nama database di gambar


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>