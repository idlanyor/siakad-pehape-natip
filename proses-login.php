<?php
session_start();
require 'config/koneksi.php';

$username = $_POST['username'];
$password = $_POST['password'];
$role     = $_POST['role'];

$stmt = $koneksi->prepare("SELECT * FROM user WHERE username=? AND password=? AND role=?");
$stmt->bind_param('ssi', $username, $password, $role);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
  $user = $result->fetch_assoc();
  $_SESSION['username'] = $user['username'];
  $_SESSION['role']     = $user['role'];

  $response = [
    'status' => 'success',
    'message' => 'Login sukses!',
    'redirect' => 'dashboard.php',
  ];
} else {
  $response = [
    'status' => 'error',
    'message' => 'Username atau password salah!'
  ];
}
echo json_encode($response);
