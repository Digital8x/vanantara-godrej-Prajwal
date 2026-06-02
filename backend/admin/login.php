<?php
session_start();
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    // Updated Password as per user request
    if ($user === 'admin' && $pass === 'arjungodrej') {
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Invalid credentials";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Godrej Admin Access</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --godrej-green: #008a4c; --dark: #1a1a1a; }
        body { font-family: 'Outfit', sans-serif; background: #f8f9fa; height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; overflow: hidden; }
        .login-box { width: 100%; max-width: 420px; padding: 50px; background: white; border-radius: 24px; box-shadow: 0 40px 80px rgba(0,0,0,0.08); border: 1px solid #eee; position: relative; }
        .login-box::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #02aab0, #00cdac); border-radius: 24px 24px 0 0; }
        .logo-area { text-align: center; margin-bottom: 40px; }
        .logo-area img { height: 50px; }
        h2 { font-weight: 800; color: var(--dark); text-align: center; margin-bottom: 30px; letter-spacing: -1px; }
        .form-label { font-weight: 600; font-size: 0.9rem; color: #666; margin-bottom: 10px; }
        .form-control { border-radius: 12px; padding: 12px 15px; border: 1px solid #eee; background: #fdfdfd; transition: 0.3s; }
        .form-control:focus { box-shadow: 0 0 0 4px rgba(0, 138, 76, 0.1); border-color: var(--godrej-green); outline: none; }
        .btn-login { background: var(--dark); color: white; border: none; padding: 15px; border-radius: 12px; font-weight: 700; width: 100%; margin-top: 20px; transition: 0.3s; }
        .btn-login:hover { background: var(--godrej-green); transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0, 138, 76, 0.2); }
        .alert { border-radius: 12px; font-weight: 600; font-size: 0.9rem; margin-bottom: 25px; border: none; background: #ffebeb; color: #d63031; }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="logo-area">
            <img src="../../logo.png" alt="Godrej Properties">
        </div>
        <h2>Admin Portal</h2>
        <?php if(isset($error)): ?><div class="alert"><?= $error ?></div><?php endif; ?>
        <form method="POST">
            <div class="mb-4">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter admin username" required autocomplete="off">
            </div>
            <div class="mb-4">
                <label class="form-label">Secure Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-login">Authorize Access</button>
        </form>
    </div>
</body>
</html>
