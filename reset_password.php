<?php
require "db.php";

$token = $_GET["token"] ?? $_POST["token"] ?? "";
$hash  = hash("sha256", $token);

$stmt = $conn->prepare(
    "SELECT patient_id FROM patients WHERE reset_token_hash = ? AND reset_expires > NOW()"
);
$stmt->bind_param("s", $hash);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();

$valid = ($token !== "" && $p !== null);
$error = "";
$done  = false;

if ($valid && $_SERVER["REQUEST_METHOD"] === "POST") {
    $password = $_POST["password"] ?? "";
    $confirm  = $_POST["confirm_password"] ?? "";

    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $upd = $conn->prepare(
            "UPDATE patients
             SET password_hash = ?, reset_token_hash = NULL, reset_expires = NULL
             WHERE patient_id = ?"
        );
        $upd->bind_param("si", $newHash, $p["patient_id"]);
        $upd->execute();
        $done = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="box">
    <h2>Reset Password</h2>

    <?php if ($done) { ?>
      <p class="success">Your password has been changed.</p>
      <p><a href="login.html">Log in with your new password</a></p>

    <?php } elseif (!$valid) { ?>
      <p class="error">This reset link is invalid or has expired.</p>
      <p><a href="forgot_password.php">Request a new link</a></p>

    <?php } else { ?>
      <form method="POST" action="reset_password.php">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

        <label for="password">New password</label>
        <div class="pw-wrap">
          <input type="password" id="password" name="password" required>
          <button type="button" class="toggle-pw" aria-label="Show password">👁️</button>
        </div>

        <label for="confirm_password">Confirm new password</label>
        <div class="pw-wrap">
          <input type="password" id="confirm_password" name="confirm_password" required>
          <button type="button" class="toggle-pw" aria-label="Show password">👁️</button>
        </div>

        <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <button type="submit">Change password</button>
      </form>
    <?php } ?>
  </div>

  <script src="toggle.js"></script>
</body>
</html>