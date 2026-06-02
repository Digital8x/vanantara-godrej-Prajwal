<?php
session_start();
require_once '../db_config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Integration of PHPMailer for Testing
require_once '../phpmailer/PHPMailer.php';
require_once '../phpmailer/SMTP.php';
require_once '../phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fetch current settings for testing
$settings_result = $conn->query("SELECT * FROM settings");
$config = [];
while($row = $settings_result->fetch_assoc()) {
    $config[$row['meta_key']] = $row['meta_value'];
}

$to = $config['admin_email'] ?? 'diyarjun9@gmail.com';
$to_list = explode(',', $to);
$primary_to = trim($to_list[0]);

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = $config['smtp_host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['smtp_user'];
    $mail->Password   = $config['smtp_pass'];
    $mail->SMTPSecure = ($config['smtp_port'] == 465) ? 'ssl' : 'tls';
    $mail->Port       = $config['smtp_port'];

    $mail->setFrom($config['smtp_user'], $config['smtp_from_name'] ?? 'Godrej Test');
    $mail->addAddress($primary_to);
    
    $mail->isHTML(false);
    $mail->Subject = "SMTP Test Connection | Godrej Vanantara";
    $mail->Body    = "Congratulations! Your PHPMailer SMTP settings are working perfectly.\n\nSent from: " . $_SERVER['HTTP_HOST'];

    $mail->send();
    echo json_encode(['status' => 'success', 'message' => "Test email sent to $primary_to via SMTP! Check your inbox."]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'SMTP Error: ' . $mail->ErrorInfo]);
}

$conn->close();
?>
