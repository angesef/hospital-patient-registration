<?php
require "db.php";

// DEMO MODE: shows the reset link on screen because XAMPP cannot send email.
// In a real system this must be false and the link is emailed instead.
$DEMO_MODE = true;

$message = "";
$link = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");

    $stmt = $conn->prepare("SELECT patient_id FROM patients WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $p = $stmt->get_result()->fetch_assoc();

    if ($p) {
        $token = bin2hex(random_bytes(32));
        $hash  = hash("sha256", $token);

        $upd = $conn->prepare(
            "UPDATE patients
             SET reset_token_hash = ?, reset_expires = DATE_ADD(NOW(), INTERVAL 30 MINUTE)
             WHERE patient_id = ?"
        );
        $upd->bind_param("si", $hash, $p["patient_id"]);
        $upd->execute();

        $link = "http://localhost/hospital/reset_password.php?token=" . $token;
    }

    // Same message whether or not the email exists
    $message = "If that email is registered, a reset link has been created.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="box">
    <h2>Forgot Password</h2>
    <p>Enter your email and we'll create a reset link.</p>

    <form method="POST" action="forgot_password.php">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required>
      <button type="submit">Create reset link</button>
    </form>

    <?php if ($message !== "") { ?>
      <p class="success"><?php echo htmlspecialchars($message); ?></p>
    <?php } ?>

    <?php if ($DEMO_MODE && $link !== "") { ?>
      <div class="demo-box">
        <strong>Demo only:</strong> a real system would email this link.<br>
        <a href="<?php echo htmlspecialchars($link); ?>"><?php echo htmlspecialchars($link); ?></a>
      </div>
    <?php } ?>

    <p><a href="login.html">&larr; Back to login</a></p>
  </div>
</body>
</html>