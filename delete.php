<?php
// File delete.php

include 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Query untuk menghapus data berdasarkan ID
    $delete_sql = "DELETE FROM sale_2024 WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Data berhasil dihapus'); window.location.href = 'display.php';</script>";
    } else {
        echo "<script>alert('Data gagal dihapus" . $conn->error . "');</script>";
    }
}
?>
