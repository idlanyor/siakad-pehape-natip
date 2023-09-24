<?php
function dbGetQuery($query, $tipeParam, ...$params): mysqli_result|bool
{
  include 'koneksi.php';
  try {
    $stmt = $koneksi->prepare($query);
    if ($stmt === false) {
      throw new Exception("Error menyiapkan statement");
    }
    if (!empty($params)) {
      $stmt->bind_param($tipeParam, ...$params);
    }
    if (!$stmt->execute()) {
      throw new Exception("Error executing query: " . $stmt->error);
    }
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
      return $result;
    } else {
      return false;
    }
  } catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    return false;
  }
}
