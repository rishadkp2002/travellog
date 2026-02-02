<?php
session_start();
if (!isset($_SESSION['hod'])) {
    header('Location: hod_login.php');
    exit;
}

$csvFile = __DIR__ . '/logs.csv';
if (!file_exists($csvFile)) {
    die("No data to export.");
}

// Read CSV data
$data = array_map('str_getcsv', file($csvFile));

// Optional: Clean header (fix unwanted columns if needed)
$expectedHeaders = ['Log ID', 'Date', 'SAP ID', 'Name', 'Break OUT', 'Break IN', 'Purpose', 'Status'];
if (count($data[0]) > count($expectedHeaders)) {
    $data[0] = $expectedHeaders;
}

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="hod_dashboard_' . date('Y-m-d_H-i-s') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Open output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel to recognize UTF-8 encoding properly
fwrite($output, "\xEF\xBB\xBF");

// Output each row
foreach ($data as $row) {
    // Optionally, clean each row to remove unwanted trailing empty columns
    $row = array_slice($row, 0, count($expectedHeaders));
    fputcsv($output, $row);
}

fclose($output);
exit;
