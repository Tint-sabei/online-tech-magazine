<?php
session_start();

// SECURITY: Only administrators can delete the file
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrator') {
    die("Access Denied: Only administrators can delete reports.");
}

$report_file = 'Tech_Magazine_Full_Report.pdf';

if (file_exists($report_file)) {
    unlink($report_file); // This command deletes the file from the server
    header("Location: index.php?status=deleted");
} else {
    header("Location: index.php?status=notfound");
}
exit;