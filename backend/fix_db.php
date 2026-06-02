<?php
require_once 'db_config.php';

echo "<h2>Godrej Vanantara Database Fix</h2>";

// Add Browser column
$sql = "ALTER TABLE leads ADD COLUMN browser VARCHAR(100) AFTER device_type";
if ($conn->query($sql) === TRUE) {
    echo "<p style='color: green;'>Successfully added 'browser' column.</p>";
}
// Update SMTP Settings
$settings = [
    'smtp_host' => 'mail.shivabihani.com',
    'smtp_port' => '465',
    'smtp_user' => 'leads@shivabihani.com',
    'smtp_pass' => '={3)%J6b1mh7',
    'admin_email' => 'harshmheswry@gmail.com,diyarjun9@gmail.com',
    'cc_emails' => 'binodbihanij@yahoo.com,henry_siva@outlook.com'
];

foreach ($settings as $key => $val) {
    $conn->query("INSERT INTO settings (meta_key, meta_value) VALUES ('$key', '$val') ON DUPLICATE KEY UPDATE meta_value = '$val'");
}

echo "<p style='color: blue;'>SMTP Settings updated successfully.</p>";

echo "<p>Your system is now up to date. You can delete this file for security.</p>";
?>
