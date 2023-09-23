<?php
$host     = "localhost";
$database = "simakad";
$username = "root";
$password = "";

$koneksi = new mysqli($host, $username, $password, $database);

if ($koneksi->connect_error) {
  die("Koneksi ke database gagal");
}
