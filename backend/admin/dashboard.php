<?php
session_start();
require_once '../db_config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Check for Browser Column existence
$checkCol = $conn->query("SHOW COLUMNS FROM leads LIKE 'browser'");
$hasBrowserCol = ($checkCol->num_rows > 0);

$result = $conn->query("SELECT * FROM leads ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Godrej Vanantara Admin - Leads</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --godrej-green: #008a4c; --dark: #1a1a1a; }
        body { font-family: 'Outfit', sans-serif; background: #f4f7f6; }
        .sidebar { height: 100vh; background: var(--dark); color: white; padding: 30px 20px; position: fixed; width: 260px; }
        .main-content { margin-left: 260px; padding: 50px; }
        .table-card { background: white; border-radius: 20px; box-shadow: 0 15px 40px rgba(0,0,0,0.04); padding: 30px; border: 1px solid #eee; }
        .nav-link { color: rgba(255,255,255,0.6); font-weight: 500; padding: 12px 20px; border-radius: 10px; margin-bottom: 5px; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { color: white; background: rgba(255,255,255,0.05); }
        .table thead th { border: none; color: #999; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; padding: 15px; }
        .table tbody td { padding: 18px 15px; vertical-align: middle; border-bottom: 1px solid #f8f9fa; color: #444; font-size: 0.95rem; }
        .lead-checkbox { width: 18px; height: 18px; border-radius: 4px; cursor: pointer; }
        .btn-action { padding: 10px 20px; border-radius: 10px; font-weight: 600; font-size: 0.9rem; transition: 0.3s; }
        .badge-source { background: #eef2ff; color: #4f46e5; padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 0.8rem; }
        .bulk-actions { display: none; background: white; padding: 15px 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-bottom: 30px; border-left: 5px solid var(--godrej-green); }
    </style>
</head>
<body>
    <div class="sidebar">
        <h4 class="mb-4 text-center fw-bold">Godrej Admin</h4>
        <div class="nav flex-column">
            <a class="nav-link active" href="dashboard.php">Leads Database</a>
            <a class="nav-link" href="settings.php">SMTP Settings</a>
            <a class="nav-link" href="logout.php">System Logout</a>
        </div>
    </div>
    
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-800 mb-1">Leads Database</h2>
                <p class="text-muted">Manage your prospective customer inquiries</p>
            </div>
            <button class="btn btn-dark btn-action" onclick="exportTableToCSV('godrej_leads.csv')">Download Report</button>
        </div>

        <div id="bulkMenu" class="bulk-actions d-flex justify-content-between align-items-center">
            <span id="selectedCount" class="fw-bold text-dark">0 leads selected</span>
            <button class="btn btn-danger btn-action" onclick="deleteSelected()">Delete Selected</button>
        </div>

        <div class="table-card">
            <table class="table" id="leadsTable">
                <thead>
                    <tr>
                        <th width="40"><input type="checkbox" class="lead-checkbox" id="selectAll"></th>
                        <th>Lead Details</th>
                        <th>Project</th>
                        <th>Device</th>
                        <?php if($hasBrowserCol): ?><th>Browser</th><?php endif; ?>
                        <th>Location/IP</th>
                        <th>Timestamp</th>
                        <th>Source</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr id="row-<?= $row['id'] ?>">
                        <td><input type="checkbox" class="lead-checkbox row-checkbox" value="<?= $row['id'] ?>"></td>
                        <td>
                            <div class="fw-bold text-dark"><?= $row['name'] ?></div>
                            <div class="text-muted small"><?= $row['phone'] ?></div>
                        </td>
                        <td class="fw-600 text-dark"><?= $row['project'] ?></td>
                        <td><div class="fw-bold text-dark"><?= $row['device_type'] ?></div></td>
                        <?php if($hasBrowserCol): ?><td><div class="text-muted small"><?= $row['browser'] ?></div></td><?php endif; ?>
                        <td>
                            <div><?= $row['city'] ?> <span class="text-muted small"><?= $row['country'] ?></span></div>
                            <div class="text-muted small"><?= $row['ip_address'] ?></div>
                        </td>
                        <td><?= date('d M, Y', strtotime($row['created_at'])) ?><br><span class="text-muted small"><?= date('H:i', strtotime($row['created_at'])) ?></span></td>
                        <td><span class="badge-source"><?= $row['utm_source'] ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Select All Functionality
        $('#selectAll').on('click', function() {
            $('.row-checkbox').prop('checked', this.checked);
            updateBulkMenu();
        });

        $('.row-checkbox').on('change', function() {
            updateBulkMenu();
        });

        function updateBulkMenu() {
            let count = $('.row-checkbox:checked').length;
            $('#selectedCount').text(count + ' leads selected');
            if (count > 0) {
                $('#bulkMenu').fadeIn();
            } else {
                $('#bulkMenu').fadeOut();
            }
        }

        function deleteSelected() {
            if (!confirm('Are you sure you want to delete the selected leads?')) return;
            
            let ids = [];
            $('.row-checkbox:checked').each(function() {
                ids.push($(this).val());
            });

            $.ajax({
                url: 'delete_leads.php',
                type: 'POST',
                data: { ids: ids },
                success: function(response) {
                    let res = JSON.parse(response);
                    if (res.status === 'success') {
                        ids.forEach(id => $('#row-' + id).fadeOut());
                        $('#bulkMenu').fadeOut();
                        $('#selectAll').prop('checked', false);
                    } else {
                        alert(res.message);
                    }
                }
            });
        }

        function exportTableToCSV(filename) {
            let csv = [];
            let rows = document.querySelectorAll("table tr");
            for (let i = 0; i < rows.length; i++) {
                let row = [], cols = rows[i].querySelectorAll("td, th");
                for (let j = 1; j < cols.length; j++) row.push('"' + cols[j].innerText.replace(/\n/g, " ") + '"');
                csv.push(row.join(","));
            }
            let csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
            let downloadLink = document.createElement("a");
            downloadLink.download = filename;
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = "none";
            document.body.appendChild(downloadLink);
            downloadLink.click();
        }
    </script>
</body>
</html>
