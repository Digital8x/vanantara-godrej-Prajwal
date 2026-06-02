<?php
session_start();
require_once '../db_config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        $stmt = $conn->prepare("INSERT INTO settings (meta_key, meta_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE meta_value = ?");
        $stmt->bind_param("sss", $key, $value, $value);
        $stmt->execute();
        $stmt->close();
    }
    $message = "Settings updated successfully!";
}

// Fetch current settings
$settings_result = $conn->query("SELECT * FROM settings");
$config = [];
while($row = $settings_result->fetch_assoc()) {
    $config[$row['meta_key']] = $row['meta_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Settings - Godrej Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --godrej-green: #008a4c; --dark: #1a1a1a; }
        body { font-family: 'Outfit', sans-serif; background: #f4f7f6; }
        .sidebar { height: 100vh; background: var(--dark); color: white; padding: 30px 20px; position: fixed; width: 260px; }
        .main-content { margin-left: 260px; padding: 50px; }
        .settings-card { background: white; border-radius: 20px; box-shadow: 0 15px 40px rgba(0,0,0,0.04); padding: 40px; border: 1px solid #eee; max-width: 800px; }
        .nav-link { color: rgba(255,255,255,0.6); font-weight: 500; padding: 12px 20px; border-radius: 10px; margin-bottom: 5px; transition: 0.3s; text-decoration: none; display: block; }
        .nav-link:hover, .nav-link.active { color: white; background: rgba(255,255,255,0.05); }
        .form-label { font-weight: 600; color: #444; margin-bottom: 10px; }
        .form-control { border-radius: 12px; padding: 12px; border: 1px solid #eee; }
        .btn-save { background: var(--dark); color: white; border-radius: 12px; padding: 12px 30px; font-weight: 600; border: none; transition: 0.3s; }
        .btn-save:hover { background: var(--godrej-green); transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="sidebar">
        <h4 class="mb-4 text-center fw-bold">Godrej Admin</h4>
        <div class="nav flex-column">
            <a class="nav-link" href="dashboard.php">Leads Database</a>
            <a class="nav-link active" href="settings.php">SMTP Settings</a>
            <a class="nav-link" href="logout.php">System Logout</a>
        </div>
    </div>
    
    <div class="main-content">
        <h2 class="fw-800 mb-5">System Configuration</h2>

        <?php if($message): ?><div class="alert alert-success border-0 shadow-sm mb-4" style="border-radius: 15px;"><?= $message ?></div><?php endif; ?>

        <div class="settings-card">
            <h5 class="mb-4 fw-bold">SMTP Configuration</h5>
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">SMTP Host</label>
                        <input type="text" name="smtp_host" class="form-control" value="<?= $config['smtp_host'] ?? '' ?>" placeholder="e.g. mail.domain.com">
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label">SMTP Port</label>
                        <input type="text" name="smtp_port" class="form-control" value="<?= $config['smtp_port'] ?? '587' ?>" placeholder="587">
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label">SMTP Username</label>
                        <input type="text" name="smtp_user" class="form-control" value="<?= $config['smtp_user'] ?? '' ?>" placeholder="email@domain.com">
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label">SMTP Password</label>
                        <input type="password" name="smtp_pass" class="form-control" value="<?= $config['smtp_pass'] ?? '' ?>" placeholder="••••••••">
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label">SMTP From Name</label>
                        <input type="text" name="smtp_from_name" class="form-control" value="<?= $config['smtp_from_name'] ?? 'Godrej Vanantara Leads' ?>" placeholder="e.g. Sales Team">
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label">CC Emails (Comma separated)</label>
                        <input type="text" name="cc_emails" class="form-control" value="<?= $config['cc_emails'] ?? '' ?>" placeholder="email1@domain.com, email2@domain.com">
                        <div class="form-text mt-2">These emails will only receive Name, Phone, Email, and Project.</div>
                    </div>
                </div>

                <hr class="my-4" style="opacity: 0.1;">
                
                <h5 class="mb-4 fw-bold">Lead Notifications</h5>
                <div class="mb-4">
                    <label class="form-label">Admin Notification Email</label>
                    <input type="email" name="admin_email" class="form-control" value="<?= $config['admin_email'] ?? '' ?>" placeholder="shiva@domain.com">
                    <div class="form-text mt-2">All new lead inquiries will be forwarded to this address instantly.</div>
                </div>

                <div class="text-end mt-4 d-flex justify-content-end gap-3">
                    <button type="button" class="btn btn-outline-dark btn-save" style="background: transparent; color: var(--dark); border: 1px solid var(--dark);" onclick="sendTestMail()">Send Test Email</button>
                    <button type="submit" class="btn-save">Save Configurations</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function sendTestMail() {
            const btn = event.target;
            const originalText = btn.innerText;
            btn.innerText = "Sending...";
            btn.disabled = true;

            $.ajax({
                url: 'test_smtp.php',
                type: 'POST',
                success: function(response) {
                    const res = JSON.parse(response);
                    alert(res.message);
                    btn.innerText = originalText;
                    btn.disabled = false;
                },
                error: function() {
                    alert("Error reaching the server.");
                    btn.innerText = originalText;
                    btn.disabled = false;
                }
            });
        }
    </script>
</body>
</html>
