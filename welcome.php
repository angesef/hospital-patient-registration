<?php
session_start();
require "db.php";

if (!isset($_SESSION["patient_id"])) {
    header("Location: login.html");
    exit;
}

$stmt = $conn->prepare(
    "SELECT patient_id, first_name, last_name, email, blood_group, referral_letter, registered_at
     FROM patients WHERE patient_id = ?"
);
$stmt->bind_param("i", $_SESSION["patient_id"]);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();

if (!$p) {
    session_destroy();
    header("Location: login.html");
    exit;
}

$patient_code = "PT-" . str_pad($p["patient_id"], 4, "0", STR_PAD_LEFT);
$initial      = strtoupper(substr($p["first_name"], 0, 1));
$registered   = date("d M Y", strtotime($p["registered_at"]));
$letter       = $p["referral_letter"];
$is_image     = in_array(strtolower(pathinfo($letter, PATHINFO_EXTENSION)), ["jpg", "jpeg", "png"]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Patient Card</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="box">
    <h2>Welcome, <?php echo htmlspecialchars($p["first_name"]); ?></h2>
    <p>You are registered as a patient.</p>

    <div class="patient-card">
      <div class="avatar"><?php echo htmlspecialchars($initial); ?></div>
      <div class="card-name">
        <?php echo htmlspecialchars($p["first_name"] . " " . $p["last_name"]); ?>
      </div>
      <div class="card-id"><?php echo $patient_code; ?></div>

      <div class="info-row">
        <span>Email</span>
        <strong><?php echo htmlspecialchars($p["email"]); ?></strong>
      </div>
      <div class="info-row">
        <span>Blood group</span>
        <strong class="badge"><?php echo htmlspecialchars($p["blood_group"]); ?></strong>
      </div>
      <div class="info-row">
        <span>Registered</span>
        <strong><?php echo $registered; ?></strong>
      </div>

      <div class="letter">
        <span>Referral letter</span>
        <?php if ($is_image) { ?>
          <a href="<?php echo htmlspecialchars($letter); ?>" target="_blank">
            <img src="<?php echo htmlspecialchars($letter); ?>" alt="Referral letter">
          </a>
        <?php } else { ?>
          <a class="file-box" href="<?php echo htmlspecialchars($letter); ?>" target="_blank">
            📄 View referral letter (PDF)
          </a>
        <?php } ?>
      </div>
    </div>

    <p><a href="logout.php">Log out</a></p>
  </div>
</body>
</html>