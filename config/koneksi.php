<?php
$host     = "localhost";
$database = "siakad";
$username = "root";
$password = "";

$koneksi = new mysqli($host, $username, $password, $database);

if ($koneksi->connect_error) {
  die("Koneksi ke database gagal");
}
