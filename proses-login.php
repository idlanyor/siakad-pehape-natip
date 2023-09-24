<?php
session_start();
require 'config/koneksi.php';

$response = [];

if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['role'])) {
  $username = $_POST['username'];
  $password = $_POST['password'];
  $role     = $_POST['role'];

  $stmt = $koneksi->prepare("SELECT * FROM user WHERE username=? AND password=? AND role=?");
  $stmt->bind_param('ssi', $username, $password, $role);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $_SESSION['id_user'] = $user['id_user'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];

    $data = [];
    if ($role == 1) {
      // Fetch data from the "dosen" table
      $stmt = $koneksi->prepare("SELECT * FROM dosen WHERE id_user = ?");
      $stmt->bind_param('i', $user['id_user']);
      $stmt->execute();
      $data = $stmt->get_result()->fetch_assoc();
    } elseif ($role == 2) {
      // Fetch data from the "mahasiswa" table
      $stmt = $koneksi->prepare("SELECT * FROM mahasiswa WHERE id_user = ?");
      $stmt->bind_param('i', $user['id_user']);
      $stmt->execute();
      $data = $stmt->get_result()->fetch_assoc();
    }

    $response = [
      'status'   => 'success',
      'message'  => 'Login sukses!',
      'redirect' => 'mahasiswa.php',
      'user_data' => $data, // Add user data here
    ];
  } else {
    $response = [
      'status'  => 'error',
      'message' => 'Username atau password salah!',
    ];
  }
} else {
  $response = [
    'status'  => 'error',
    'message' => 'Permintaan tidak valid. Pastikan semua data diterima.',
  ];
}

echo json_encode($response);
