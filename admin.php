<?php
session_start();
require "db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "staff") {
    header("Location: login.html");
    exit;
}

// Security token used by the Delete buttons
if (empty($_SESSION["csrf"])) {
    $_SESSION["csrf"] = bin2hex(random_bytes(16));
}

// Messages shown after an action (only these exact keys are allowed)
$messages = [
    "deleted" => "Patient deleted.",
    "updated" => "Patient details updated."
];
$flash = $messages[$_GET["msg"] ?? ""] ?? "";

$search = trim($_GET["search"] ?? "");

if ($search !== "") {
    $like = "%" . $search . "%";
    $stmt = $conn->prepare(
        "SELECT patient_id, first_name, last_name, email, blood_group, referral_letter, registered_at
         FROM patients
         WHERE role = 'patient' AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)
         ORDER BY registered_at DESC"
    );
    $stmt->bind_param("sss", $like, $like, $like);
} else {
    $stmt = $conn->prepare(
        "SELECT patient_id, first_name, last_name, email, blood_group, referral_letter, registered_at
         FROM patients
         WHERE role = 'patient'
         ORDER BY registered_at DESC"
    );
}

$stmt->execute();
$result = $stmt->get_result();
$total  = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Patients</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="box wide">
    <h2>Patient Records</h2>
    <p>Signed in as <?php echo htmlspecialchars($_SESSION["first_name"]); ?> (staff)
       &middot; <?php echo $total; ?> patient(s) found</p>

    <?php if ($flash !== "") { ?>
      <div class="flash"><?php echo htmlspecialchars($flash); ?></div>
    <?php } ?>

    <form method="GET" action="admin.php">
      <input type="text" name="search" placeholder="Search by name or email"
             value="<?php echo htmlspecialchars($search); ?>">
      <button type="submit">Search</button>
    </form>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Blood</th>
            <th>Registered</th>
            <th>Referral letter</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($total === 0) { ?>
            <tr><td colspan="7" class="empty">No patients found.</td></tr>
          <?php } ?>

          <?php while ($row = $result->fetch_assoc()) {
              $code   = "PT-" . str_pad($row["patient_id"], 4, "0", STR_PAD_LEFT);
              $letter = $row["referral_letter"];
              $ext    = strtolower(pathinfo($letter, PATHINFO_EXTENSION));
              $isImg  = in_array($ext, ["jpg", "jpeg", "png"]);
          ?>
            <tr>
              <td><?php echo $code; ?></td>
              <td><?php echo htmlspecialchars($row["first_name"] . " " . $row["last_name"]); ?></td>
              <td><?php echo htmlspecialchars($row["email"]); ?></td>
              <td><span class="badge"><?php echo htmlspecialchars($row["blood_group"]); ?></span></td>
              <td><?php echo date("d M Y", strtotime($row["registered_at"])); ?></td>
              <td>
                <a href="<?php echo htmlspecialchars($letter); ?>" target="_blank">
                  <?php if ($isImg) { ?>
                    <img class="thumb" src="<?php echo htmlspecialchars($letter); ?>" alt="Referral letter">
                  <?php } else { ?>
                    📄 View PDF
                  <?php } ?>
                </a>
              </td>
              <td class="actions">
                <a class="btn-small" href="edit_patient.php?id=<?php echo (int)$row["patient_id"]; ?>">Edit</a>

                <form method="POST" action="delete_patient.php" class="inline-form"
                      onsubmit="return confirm('Delete this patient permanently?');">
                  <input type="hidden" name="id" value="<?php echo (int)$row["patient_id"]; ?>">
                  <input type="hidden" name="csrf" value="<?php echo $_SESSION["csrf"]; ?>">
                  <button type="submit" class="btn-small btn-danger">Delete</button>
                </form>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>

    <p><a href="logout.php">Log out</a></p>
  </div>
</body>
</html>