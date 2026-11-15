<?php
session_start();
require "db.php";

if (($_SESSION["role"] ?? "") !== "staff") {
    header("Location: login.html");
    exit;
}

if (empty($_SESSION["csrf"])) {
    $_SESSION["csrf"] = bin2hex(random_bytes(16));
}

$valid_groups = ["A+","A-","B+","B-","AB+","AB-","O+","O-"];
$error = "";
$id = (int)($_GET["id"] ?? $_POST["id"] ?? 0);

// Load the patient
$stmt = $conn->prepare("SELECT patient_id, first_name, last_name, email, blood_group FROM patients WHERE patient_id = ? AND role = 'patient'");
$stmt->bind_param("i", $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();

if (!$p) {
    header("Location: admin.php");
    exit;
}

// Save changes
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_SESSION["csrf"];
    if (!hash_equals($token, $_POST["csrf"] ?? "")) {
        die("Invalid request.");
    }

    $first_name  = trim($_POST["first_name"] ?? "");
    $last_name   = trim($_POST["last_name"] ?? "");
    $email       = trim($_POST["email"] ?? "");
    $blood_group = $_POST["blood_group"] ?? "";

    // Show what the staff member typed again if there is an error
    $p["first_name"]  = $first_name;
    $p["last_name"]   = $last_name;
    $p["email"]       = $email;
    $p["blood_group"] = $blood_group;

    if ($first_name === "" || $last_name === "") {
        $error = "Name is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif (!in_array($blood_group, $valid_groups)) {
        $error = "Invalid blood group.";
    } else {
        $check = $conn->prepare("SELECT patient_id FROM patients WHERE email = ? AND patient_id != ?");
        $check->bind_param("si", $email, $id);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            $error = "Another patient already uses this email.";
        } else {
            $upd = $conn->prepare(
                "UPDATE patients SET first_name = ?, last_name = ?, email = ?, blood_group = ?
                 WHERE patient_id = ? AND role = 'patient'"
            );
            $upd->bind_param("ssssi", $first_name, $last_name, $email, $blood_group, $id);
            $upd->execute();

            header("Location: admin.php?msg=updated");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Patient</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="box">
    <h2>Edit Patient</h2>
    <p><?php echo "PT-" . str_pad($p["patient_id"], 4, "0", STR_PAD_LEFT); ?></p>

    <form method="POST" action="edit_patient.php">
      <input type="hidden" name="id" value="<?php echo (int)$p["patient_id"]; ?>">
      <input type="hidden" name="csrf" value="<?php echo $_SESSION["csrf"]; ?>">

      <label for="first_name">First name</label>
      <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($p["first_name"]); ?>">

      <label for="last_name">Last name</label>
      <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($p["last_name"]); ?>">

      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($p["email"]); ?>">

      <label for="blood_group">Blood group</label>
      <select id="blood_group" name="blood_group">
        <?php foreach ($valid_groups as $g) { ?>
          <option value="<?php echo $g; ?>" <?php echo ($p["blood_group"] === $g) ? "selected" : ""; ?>>
            <?php echo $g; ?>
          </option>
        <?php } ?>
      </select>

      <p class="error"><?php echo htmlspecialchars($error); ?></p>
      <button type="submit">Save changes</button>
    </form>

    <p><a href="admin.php">&larr; Back to patient list</a></p>
  </div>
</body>
</html>