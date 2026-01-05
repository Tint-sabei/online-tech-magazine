<?php
/**
 * IMPORT/EXPORT FUNCTIONALITY
 * This script handles the "Export" requirement by generating a professional PDF report.
 * 1. FORMAT: Uses the FPDF library to create a .pdf file (meeting the requirement for non-text/json formats).
 * 2. DATA MODELING: Aggregates database statistics into tables and rankings.
 * 3. MULTIMEDIA: Integrates the QuickChart API to embed a dynamic PNG chart into the document.
 */

session_start();
require_once 'classes/Database.php';
require_once 'libs/fpdf.php';

// --- SECURITY CHECK (Requirement 4) ---
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrator') {
    die("Access Denied.");
}

$db = Database::getInstance();
$conn = $db->getConnection();

// --- 0. FILE CLEANUP ---
$report_file = 'Tech_Magazine_Full_Report.pdf';
if (file_exists($report_file)) { unlink($report_file); }

// --- 1. DATA COLLECTION ---

// A. Summary Stats
$summary_sql = "
    SELECT 
        (SELECT COUNT(*) FROM visits WHERE page_visited LIKE 'article_view.php?id=%') as total_reads,
        (SELECT COUNT(*) FROM visits WHERE page_visited LIKE 'article_view.php?id=%' AND (user_id IS NULL OR user_id = 0)) as guest_reads,
        (SELECT COUNT(*) FROM comments) as total_comments";
$summary = $conn->query($summary_sql)->fetch(PDO::FETCH_ASSOC);

// B. Trending Logic (Top 3)
$trending_sql = "
    SELECT a.article_id, a.title, 
           (SELECT COUNT(*) FROM visits v WHERE v.page_visited = CONCAT('article_view.php?id=', a.article_id)) as click_count,
           (SELECT COUNT(*) FROM comments c WHERE c.article_id = a.article_id) as comment_count
    FROM articles a
    WHERE EXISTS (SELECT 1 FROM visits v WHERE v.page_visited = CONCAT('article_view.php?id=', a.article_id))
    ORDER BY (click_count + comment_count) DESC 
    LIMIT 3";
$trending_articles = $conn->query($trending_sql)->fetchAll(PDO::FETCH_ASSOC);

// C. Author Contributions (Requirement 4)
$author_sql = "
    SELECT u.username, COUNT(a.article_id) as article_count 
    FROM users u 
    LEFT JOIN articles a ON u.user_id = a.author_id 
    WHERE u.role IN ('author', 'administrator')
    GROUP BY u.user_id 
    ORDER BY article_count DESC";
$author_rankings = $conn->query($author_sql)->fetchAll(PDO::FETCH_ASSOC);

// D. Category Stats for Pie Chart
$cat_stats = $conn->query("SELECT c.category_name, COUNT(a.article_id) as count FROM categories c LEFT JOIN articles a ON c.category_id = a.category_id GROUP BY c.category_id")->fetchAll(PDO::FETCH_ASSOC);
$labels = []; $counts = [];
foreach($cat_stats as $row) { 
    $labels[] = "'" . addslashes($row['category_name']) . "'"; 
    $counts[] = $row['count']; 
}

// --- 2. CHART GENERATION (QuickChart API) ---
$chartConfig = "{type:'pie',data:{labels:[".implode(',',$labels)."],datasets:[{data:[".implode(',',$counts)."],backgroundColor:['#512DA8','#B39DDB','#D1C4E9','#FFCCBC']}]}}";
$chartUrl = "https://quickchart.io/chart?width=300&height=200&format=png&c=" . urlencode($chartConfig);
$tempChartPath = 'libs/temp_chart.png';
$chartData = @file_get_contents($chartUrl);
if ($chartData) { file_put_contents($tempChartPath, $chartData); }

// --- 3. PDF GENERATION ---
$pdf = new FPDF();
$pdf->AddPage();

// Report Title
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(81, 45, 168); 
$pdf->Cell(0, 15, 'Tech Magazine - Performance Audit', 0, 1, 'C');
$pdf->Ln(5);

// SUMMARY TABLE
$pdf->SetFont('Arial', 'B', 12); 
$pdf->SetFillColor(243, 229, 245);
$pdf->Cell(95, 10, 'Engagement Metric', 1, 0, 'C', true); 
$pdf->Cell(95, 10, 'Total Count', 1, 1, 'C', true);
$pdf->SetFont('Arial', '', 12); 
$pdf->SetTextColor(0,0,0);
$pdf->Cell(95, 10, 'Total Article Reads', 1); $pdf->Cell(95, 10, $summary['total_reads'], 1, 1, 'C');
$pdf->Cell(95, 10, 'Guest Reader Reads', 1); $pdf->Cell(95, 10, $summary['guest_reads'], 1, 1, 'C');
$pdf->Cell(95, 10, 'Total Comments', 1); $pdf->Cell(95, 10, $summary['total_comments'], 1, 1, 'C');
$pdf->Ln(10);

// CHART SECTION 
if (file_exists($tempChartPath)) {
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(81, 45, 168);
    $pdf->Cell(0, 10, 'Content Balance (By Category)', 0, 1, 'L');
    $pdf->Image($tempChartPath, 55, $pdf->GetY(), 100);
    $pdf->SetY($pdf->GetY() + 65); 
    $pdf->Ln(10);
}

// AUTHOR RANKINGS TABLE
$pdf->SetFont('Arial', 'B', 14); 
$pdf->SetTextColor(81, 45, 168);
$pdf->Cell(0, 10, 'Author Contribution Rankings', 0, 1, 'L');
$pdf->SetFont('Arial', 'B', 10); 
$pdf->SetFillColor(209, 196, 233);
$pdf->Cell(20, 10, 'Rank', 1, 0, 'C', true);
$pdf->Cell(120, 10, 'Author Name', 1, 0, 'L', true);
$pdf->Cell(50, 10, 'Articles Written', 1, 1, 'C', true);
$pdf->SetFont('Arial', '', 10); 
$rank = 1;
foreach($author_rankings as $author) {
    $pdf->Cell(20, 10, '#' . $rank++, 1, 0, 'C');
    $pdf->Cell(120, 10, $author['username'], 1, 0, 'L');
    $pdf->Cell(50, 10, $author['article_count'], 1, 1, 'C');
}
$pdf->Ln(15);

// TOP 3 TRENDING TABLE 
$pdf->SetFont('Arial', 'B', 14); 
$pdf->SetTextColor(81, 45, 168);
$pdf->Cell(0, 10, 'Top 3 Trending Articles', 0, 1);
$pdf->SetFont('Arial', 'B', 10); 
$pdf->SetFillColor(255, 204, 188); // Light Orange
$pdf->Cell(100, 10, 'Article Title', 1, 0, 'L', true);
$pdf->Cell(25, 10, 'Reads', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Comments', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Popularity Score', 1, 1, 'C', true);
$pdf->SetFont('Arial', '', 9); 
foreach($trending_articles as $art) {
    $title = (strlen($art['title']) > 50) ? substr($art['title'],0,47).'...' : $art['title'];
    $pdf->Cell(100, 10, $title, 1);
    $pdf->Cell(25, 10, $art['click_count'], 1, 0, 'C');
    $pdf->Cell(25, 10, $art['comment_count'], 1, 0, 'C');
    $pdf->Cell(40, 10, ($art['click_count'] + $art['comment_count']), 1, 1, 'C');
}

// --- 4. OUTPUT ---
$pdf->Output('F', $report_file); 
if (file_exists($tempChartPath)) { unlink($tempChartPath); }
header("Location: index.php?report_generated=true");
exit;