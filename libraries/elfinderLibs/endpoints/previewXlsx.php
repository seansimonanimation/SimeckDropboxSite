<?php
// previewXlsx.php
// Minimal XLSX/XLS/CSV -> HTML preview endpoint using PHPSpreadsheet.
// Accepts ?hash=... (elfinder hash) or ?path=/absolute/path/to/file.xlsx
// Caching: stores converted HTML in sys_get_temp_dir() by default.
if(!defined('__ROOT__')){define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);}
require_once __ROOT__ . '/vendor/autoload.php';
include_once __ROOT__ . '/libraries/elfinderLibs/elfinderlib.php';

// Suppress PHPSpreadsheet deprecation notices if any
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
// ─── Parameter parsing (hash or url) ─────────────────────────────
$hash = $_GET['hash'] ?? '';
if ($hash) {
    $elfinderOptions = GetRoleElfinderOptions();
    $filePath = DecodeElfinderHash($hash, $elfinderOptions);
    if ($filePath === null) {
        http_response_code(400);
        echo 'Invalid file hash.';
        exit;
    }
} else {
    $filePath = ResolvePreviewFilePath();
}


$ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
$allowedExts = ['xlsx', 'xls', 'csv', 'ods'];
if (!in_array($ext, $allowedExts)) {
    http_response_code(400);
    echo 'Unsupported file type';
    exit;
}

try {
    // Load the spreadsheet
    $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($filePath);
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

    // For large files, only load data (no formatting) — speeds things up
    if ($ext === 'csv') {
        $reader->setDelimiter(',');
        $reader->setEnclosure('"');
        $reader->setLineEnding("\r\n");
        $reader->setSheetIndex(0);
    }

    /** @var \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet */
    $spreadsheet = $reader->load($filePath);

    // Create HTML writer
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Html($spreadsheet);
    $writer->setUseInlineCss(true);
    $writer->setPreCalculateFormulas(false);

    // Disable sheet navigation if only one sheet — cleaner output
    $sheetCount = $spreadsheet->getSheetCount();
    if ($sheetCount <= 1) {
        $writer->setGenerateSheetNavigationBlock(false);
    }

    // Capture the output
    ob_start();
    $writer->save('php://output');
    $fullHtml = ob_get_clean();

    // Extract just the body content (strip <html>/<head>/<body> wrappers)
    preg_match('/<body[^>]*>(.*?)<\/body>/is', $fullHtml, $matches);
    $tableHtml = $matches[1] ?? $fullHtml;

    // Also capture any <style> blocks the writer generated
    preg_match_all('/<style[^>]*>.*?<\/style>/is', $fullHtml, $styleMatches);
    $extraStyles = implode("\n", $styleMatches[0] ?? []);

} catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
    http_response_code(500);
    echo 'Spreadsheet error: ' . htmlspecialchars($e->getMessage());
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo 'Error: ' . htmlspecialchars($e->getMessage());
    exit;
}

// --- HTML Output ---
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Nunito', Arial, sans-serif;
            line-height: 1.6;
            padding: 20px;
            background: #aaaaaa;
            color: #111;
        }
        body, body * {
            color: #111 !important;
        }
        /* Spreadsheet-specific table overrides */
        table {
            border-collapse: collapse;
            margin: 16px 0;
            background: #fff;
            font-size: 13px;
            width: auto;
            min-width: 50%;
        }
        th, td {
            border: 1px solid #666 !important;
            padding: 4px 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #e8e8e8 !important;
            font-weight: 700;
        }
        tr:nth-child(even) td {
            background: #f4f4f4 !important;
        }
        /* Sheet navigation tabs */
        .phpspreadsheet-navigation {
            margin-bottom: 12px;
            padding: 8px 0;
            border-bottom: 2px solid #888;
        }
        .phpspreadsheet-navigation a {
            display: inline-block;
            padding: 4px 12px;
            margin-right: 4px;
            background: #ddd;
            color: #333 !important;
            text-decoration: none;
            border-radius: 4px 4px 0 0;
            font-size: 13px;
        }
        .phpspreadsheet-navigation a.selected {
            background: #fff;
            font-weight: 700;
            border: 1px solid #888;
            border-bottom: 2px solid #fff;
            margin-bottom: -2px;
        }
        /* Inline row/column headers from PHPSpreadsheet */
        .column-headers td,
        .column-headers th,
        .row-headers td,
        .row-headers th {
            background: #e0e0e0 !important;
            font-weight: 600;
            color: #333 !important;
        }
        /* Hyperlinks */
        a { color: #1a73e8 !important; }
        a:hover { color: #90caf9 !important; }
        /* Scroll for wide tables */
        .spreadsheet-wrapper {
            overflow-x: auto;
            overflow-y: auto;
            max-height: 80vh;
        }
        /* Responsive */
        @media (max-width: 600px) {
            body { padding: 8px; }
            table { font-size: 11px; }
            th, td { padding: 2px 4px; }
        }
    </style>
    <?php echo $extraStyles; ?>
</head>
<body>
    <?php
    // Show filename as a header
    $displayName = htmlspecialchars(basename($filePath));
    echo '<h1 style="font-size:1.2rem;margin-bottom:8px;color:#333 !important;">' . $displayName . '</h1>';

    // Inform about multiple sheets
    if ($sheetCount > 1) {
        $activeSheet = $spreadsheet->getActiveSheet()->getTitle();
        echo '<p style="font-size:0.85rem;color:#555 !important;margin-bottom:12px;">';
        echo $sheetCount . ' sheets · Currently viewing: <strong>' . htmlspecialchars($activeSheet) . '</strong>';
        echo '</p>';
    }
    ?>
    <div class="spreadsheet-wrapper">
        <?php echo $tableHtml; ?>
    </div>
</body>
</html>
