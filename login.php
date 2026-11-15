<?php
session_start();
require "db.php";

$email    = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

$stmt = $conn->prepare("SELECT patient_id, first_name, password_hash, role FROM patients WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

if ($patient && password_verify($password, $patient["password_hash"])) {
    session_regenerate_id(true);
    $_SESSION["patient_id"] = $patient["patient_id"];
    $_SESSION["first_name"] = $patient["first_name"];
    $_SESSION["role"]       = $patient["role"];

    if ($patient["role"] === "staff") {
        header("Location: admin.php");
    } else {
        header("Location: welcome.php");
    }
    exit;
}

die("<p style='color:red'>Wrong email or password.</p><a href='login.html'>Try again</a>");
?>