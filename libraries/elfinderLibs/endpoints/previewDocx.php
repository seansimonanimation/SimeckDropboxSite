<?php
// previewDocx.php
// Minimal DOCX -> HTML preview endpoint using PHPWord.
// Accepts ?hash=... (elfinder hash) or ?path=/absolute/path/to/file.docx
// Caching: stores converted HTML in sys_get_temp_dir() by default.
if(!defined('__ROOT__')){define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);}
require_once __ROOT__ . '/vendor/autoload.php';
include_once __ROOT__ . '/libraries/elfinderLibs/elfinderlib.php';
$filePath = ResolvePreviewFilePath();

$ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
if (!in_array($ext, ['docx', 'doc'])) {
    http_response_code(400);
    echo 'Unsupported file type';
    exit;
}

$phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);
$writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');

ob_start();
$writer->save('php://output');
$bodyHtml = ob_get_clean();

header('Content-Type: text/html; charset=utf-8');
?>
<?php
$theme = $_SESSION['theme'] ?? 'dark-boo';
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . '://' . $host;
?>
<?php
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
            background: #BBB
            color: #111;
        }
        body, body * {
            color: #111 !important;
        }
        h1, h2, h3, h4, h5, h6 { 
            color: #fff; 
            font-weight: 700;
            margin-top: 16px;
            margin-bottom: 12px;
        }
        h1 { font-size: 1.80rem; }
        h2 { font-size: 1.40rem; }
        h3 { font-size: 1.15rem; }
        p { margin-bottom: 12px; }
        strong, b { font-weight: 700; }
        a {color: #1a73e8;}
        a:hover { color: #90caf9; }
        img { max-width: 100%; height: auto; margin: 16px 0; border-radius: 8px; }
        ul, ol { margin: 12px 0 12px 24px; }
        li { margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th, td { border: 1px solid #333; padding: 8px 12px; text-align: left; }
        th { background: #262640; font-weight: 700; }
        code { background: rgba(0,0,0,0.35); color: #64b5f6; font-family: monospace; padding: 2px 6px; border-radius: 4px; }
        blockquote {border-left-color: #1a73e8;color: #444;}
    </style>
</head>
<body>
<?php echo $bodyHtml; ?>
</body>
</html>