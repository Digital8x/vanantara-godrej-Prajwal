<?php
header('Content-Type: application/json');
// require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendLeadEmail($to, $subject, $body, $config) {
    require_once 'phpmailer/PHPMailer.php';
    require_once 'phpmailer/SMTP.php';
    require_once 'phpmailer/Exception.php';

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $config['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['smtp_user'];
        $mail->Password   = $config['smtp_pass'];
        $mail->SMTPSecure = ($config['smtp_port'] == 465) ? 'ssl' : 'tls';
        $mail->Port       = $config['smtp_port'];

        $mail->setFrom($config['smtp_user'], $config['smtp_from_name'] ?? 'Godrej Leads');
        $mail->addAddress($to);
        
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

$response = ['status' => 'error', 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $project = trim($_POST['project'] ?? 'Godrej Vanantara');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($phone) || empty($email)) {
        echo json_encode(['status' => 'error', 'message' => 'Name, Phone, and Email are required.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email address format.']);
        exit;
    }

    date_default_timezone_set('Asia/Kolkata');
    $timestamp = date('Y-m-d H:i:s');
    $ipAddress = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
    if (strpos($ipAddress, ',') !== false) {
        $ipAddress = explode(',', $ipAddress)[0]; // Take the first IP if multiple present
    }
    if ($ipAddress === '::1') $ipAddress = '127.0.0.1';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // 1. Device Type Detection (Readable Labels)
    $deviceType = 'Other Device';
    if (strpos($userAgent, 'iPhone') !== false) $deviceType = 'iPhone (iOS)';
    elseif (strpos($userAgent, 'iPad') !== false) $deviceType = 'iPad / Tablet (iOS)';
    elseif (strpos($userAgent, 'Android') !== false) {
        $deviceType = (strpos($userAgent, 'Mobile') !== false) ? 'Android Mobile' : 'Android Tablet';
    }
    elseif (strpos($userAgent, 'Windows') !== false) $deviceType = 'Windows Desktop';
    elseif (strpos($userAgent, 'Mac') !== false) $deviceType = 'Mac Desktop';

    // 2. Browser Detection
    $browser = "Other";
    if (strpos($userAgent, 'SamsungBrowser') !== false) $browser = 'Samsung Internet';
    elseif (strpos($userAgent, 'Opera') !== false || strpos($userAgent, 'OPR') !== false) $browser = 'Opera';
    elseif (strpos($userAgent, 'Edge') !== false || strpos($userAgent, 'Edg') !== false) $browser = 'Edge';
    elseif (strpos($userAgent, 'Chrome') !== false) $browser = 'Chrome';
    elseif (strpos($userAgent, 'Safari') !== false) $browser = 'Safari';
    elseif (strpos($userAgent, 'Firefox') !== false) $browser = 'Firefox';

    $recordedInfo = "$browser on $deviceType";

    // IP-API Geo-Location and VPN Check
    $geoData = @json_decode(file_get_contents("http://ip-api.com/json/{$ipAddress}?fields=city,country,proxy,hosting"), true);
    $city = $geoData['city'] ?? 'Unknown';
    $country = $geoData['country'] ?? 'Unknown';
    $isVPN = (isset($geoData['proxy']) && $geoData['proxy'] == true) || (isset($geoData['hosting']) && $geoData['hosting'] == true);

    if ($isVPN) {
        echo json_encode(['status' => 'error', 'message' => 'VPNs/Proxies are not allowed. Please disable your VPN.']);
        exit;
    }

    // Include Database Config
    require_once 'db_config.php';

    // Get UTM Data (Optional)
    $utm_source = $_POST['utm_source'] ?? 'Direct';
    $utm_medium = $_POST['utm_medium'] ?? 'None';
    $utm_campaign = $_POST['utm_campaign'] ?? 'Organic';

    // Resilient Column Detection
    $checkColumn = $conn->query("SHOW COLUMNS FROM leads LIKE 'browser'");
    $hasBrowserCol = ($checkColumn->num_rows > 0);

    if ($hasBrowserCol) {
        $stmt = $conn->prepare("INSERT INTO leads (name, phone, email, project, city, country, ip_address, device_type, browser, utm_source, utm_medium, utm_campaign) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssss", $name, $phone, $email, $project, $city, $country, $ipAddress, $deviceType, $browser, $utm_source, $utm_medium, $utm_campaign);
    } else {
        // Fallback: Bundle browser info into device_type if column is missing
        $recordedInfo = $deviceType . " (" . $browser . ")";
        $stmt = $conn->prepare("INSERT INTO leads (name, phone, email, project, city, country, ip_address, device_type, utm_source, utm_medium, utm_campaign) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssss", $name, $phone, $email, $project, $city, $country, $ipAddress, $recordedInfo, $utm_source, $utm_medium, $utm_campaign);
    }

    if ($stmt->execute()) {
        // Fetch System Settings (if available)
        $config_res = $conn->query("SELECT * FROM settings");
        $config = [];
        if ($config_res) {
            while($c_row = $config_res->fetch_assoc()) {
                $config[$c_row['meta_key']] = $c_row['meta_value'];
            }
        }

        // Override or fallback to .env settings
        $config['smtp_host'] = $_ENV['SMTP_HOST'] ?? $config['smtp_host'] ?? '';
        $config['smtp_user'] = $_ENV['SMTP_USER'] ?? $config['smtp_user'] ?? '';
        $config['smtp_pass'] = $_ENV['SMTP_PASS'] ?? $config['smtp_pass'] ?? '';
        $config['smtp_port'] = $_ENV['SMTP_PORT'] ?? $config['smtp_port'] ?? '465';
        $config['smtp_from_name'] = $_ENV['SMTP_FROM_NAME'] ?? $config['smtp_from_name'] ?? 'Godrej Vanantara Leads';
        $config['admin_email'] = $_ENV['ADMIN_EMAIL'] ?? $config['admin_email'] ?? '';
        $config['cc_emails'] = $_ENV['CC_EMAILS'] ?? $config['cc_emails'] ?? '';

        // Admin Notification
        $to_emails = !empty($config['admin_email']) ? $config['admin_email'] : "admin@example.com";
        $subject = "New Lead: " . $name . " | " . $project;
        
        $techInfo = ($hasBrowserCol) ? "$deviceType | $browser" : "$deviceType ($browser)";
        $body = "New lead received for Godrej Vanantara:\n\n";
        $body .= "Name: " . $name . "\n";
        $body .= "Phone: " . $phone . "\n";
        $body .= "Email: " . $email . "\n";
        $body .= "Location: " . $city . ", " . $country . "\n";
        $body .= "Source: " . $utm_source . "\n";
        $body .= "Device Info: " . $techInfo . "\n";
        $body .= "IP: " . $ipAddress . "\n";

        $to_list = explode(',', $to_emails);
        foreach ($to_list as $admin_to) {
            $admin_to = trim($admin_to);
            if (!empty($admin_to)) {
                sendLeadEmail($admin_to, $subject, $body, $config);
            }
        }

        // CC Notification
        $cc_emails = $config['cc_emails'] ?? '';
        if (!empty($cc_emails)) {
            $cc_list = explode(',', $cc_emails);
            $cc_body = "New Inquiry for $project:\n\n";
            $cc_body .= "Name: " . $name . "\n";
            $cc_body .= "Phone: " . $phone . "\n";
            $cc_body .= "Email: " . $email . "\n";
            $cc_body .= "Project: " . $project . "\n";
            $cc_body .= "IP Address: " . $ipAddress . "\n";
            
            foreach ($cc_list as $cc) {
                $cc = trim($cc);
                if (!empty($cc)) {
                    sendLeadEmail($cc, "Inquiry: $name | $project", $cc_body, $config);
                }
            }
        }

        $response = ['status' => 'success', 'message' => 'Thank you! Your inquiry has been submitted.'];
    } else {
        $response = ['status' => 'error', 'message' => 'Database error: ' . $stmt->error];
    }

    $stmt->close();
    $conn->close();
}

echo json_encode($response);
?>
