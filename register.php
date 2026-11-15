<?php
require "db.php";

function fail($msg) {
    die("<p style='color:red'>$msg</p><a href='signup.html'>Go back</a>");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: signup.html");
    exit;
}

$first_name  = trim($_POST["first_name"] ?? "");
$last_name   = trim($_POST["last_name"] ?? "");
$email       = trim($_POST["email"] ?? "");
$password    = $_POST["password"] ?? "";
$blood_group = $_POST["blood_group"] ?? "";

$valid_groups = ["A+","A-","B+","B-","AB+","AB-","O+","O-"];

if ($first_name === "" || $last_name === "") fail("Name is required.");
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fail("Invalid email address.");
if (strlen($password) < 8) fail("Password must be at least 8 characters.");
if (!in_array($blood_group, $valid_groups)) fail("Invalid blood group.");

// Is the email already registered?
$check = $conn->prepare("SELECT patient_id FROM patients WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
if ($check->get_result()->num_rows > 0) fail("This email is already registered.");

// Handle the referral letter
$file = $_FILES["referral_letter"] ?? null;
if (!$file || $file["error"] !== UPLOAD_ERR_OK) fail("Referral letter upload failed.");

$ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
if (!in_array($ext, ["pdf", "jpg", "jpeg", "png"])) fail("Only PDF, JPG or PNG files are allowed.");
if ($file["size"] > 2 * 1024 * 1024) fail("File must be smaller than 2 MB.");

$new_name = "ref_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
if (!move_uploaded_file($file["tmp_name"], "uploads/" . $new_name)) fail("Could not save the file.");

// Save the patient
$hash = password_hash($password, PASSWORD_DEFAULT);
$path = "uploads/" . $new_name;

$stmt = $conn->prepare(
    "INSERT INTO patients (first_name, last_name, email, password_hash, blood_group, referral_letter)
     VALUES (?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("ssssss", $first_name, $last_name, $email, $hash, $blood_group, $path);
$stmt->execute();

header("Location: login.html?registered=1");
exit;
?>