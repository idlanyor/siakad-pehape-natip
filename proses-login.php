<?php
session_start();
require 'fungsi.php';

$response = [];

if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['role'])) {
  $username = $_POST['username'];
  $password = $_POST['password'];
  $role     = $_POST['role'];

  $query = "SELECT * FROM user WHERE username=? AND password=? AND role=?";
  $result = dbGetQuery($query,'ssi',$username, $password, $role);

  if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $_SESSION['id_user'] = $user['id_user'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];

    $data = [];
    if ($role == 1) {
      $response = [
        'status'   => 'success',
        'message'  => 'Login sukses!',
        'redirect' => 'dosen.php',
      ];
    } elseif ($role == 2) {
      $response = [
        'status'   => 'success',
        'message'  => 'Login sukses!',
        'redirect' => 'mahasiswa.php',
      ];
    }

    
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
