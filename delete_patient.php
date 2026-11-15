<?php
session_start();
require "db.php";

// Staff only
if (($_SESSION["role"] ?? "") !== "staff") {
    header("Location: login.html");
    exit;
}

// Must be a POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: admin.php");
    exit;
}

// Check the security token
$token = $_SESSION["csrf"] ?? "";
if ($token === "" || !hash_equals($token, $_POST["csrf"] ?? "")) {
    die("Invalid request.");
}

$id = (int)($_POST["id"] ?? 0);

// Find the patient so we can also remove their uploaded file
$stmt = $conn->prepare("SELECT referral_letter FROM patients WHERE patient_id = ? AND role = 'patient'");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if ($row) {
    $file = __DIR__ . "/uploads/" . basename($row["referral_letter"]);
    if (is_file($file)) {
        unlink($file);
    }

    $del = $conn->prepare("DELETE FROM patients WHERE patient_id = ? AND role = 'patient'");
    $del->bind_param("i", $id);
    $del->execute();
}

header("Location: admin.php?msg=deleted");
exit;
?>