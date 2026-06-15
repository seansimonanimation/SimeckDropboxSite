<?php
// previewText.php
// Text/code file preview endpoint with highlight.js syntax highlighting, 
// encoding detection, line-number pagination (reuses user's log_rows_per_page).
// Accepts ?url= (elFinder file URL) and optionally ?page=N&lines=N.
if(!defined('__ROOT__')){define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);}
require_once __ROOT__ . '/libraries/session.php';
require_once __ROOT__ . '/libraries/db.php';
require_once __ROOT__ . '/libraries/logging.php';
include_once __ROOT__ . '/libraries/elfinderLibs/elfinderlib.php';

// ─── Theme support ───────────────────────────────────────────────────
$themeId = $_SESSION['theme'] ?? 'dark-boo';
$themeClass = 'theme-' . $themeId;


// ─── Parameter parsing ───────────────────────────────────────────────
$filePath = ResolvePreviewFilePath();

// ─── Encoding detection ──────────────────────────────────────────────
$raw = file_get_contents($filePath);
if ($raw === false) {
    http_response_code(500);
    echo 'Failed to read file.';
    exit;
}

$encoding = 'UTF-8';
// Check BOM
if (strncmp($raw, "\xEF\xBB\xBF", 3) === 0) {
    $raw = substr($raw, 3);
    $encoding = 'UTF-8 BOM';
} elseif (strncmp($raw, "\xFF\xFE", 2) === 0) {
    $raw = iconv('UTF-16LE', 'UTF-8//IGNORE', substr($raw, 2));
    $encoding = 'UTF-16 LE';
} elseif (strncmp($raw, "\xFE\xFF", 2) === 0) {
    $raw = iconv('UTF-16BE', 'UTF-8//IGNORE', substr($raw, 2));
    $encoding = 'UTF-16 BE';
} else {
    $detected = mb_detect_encoding($raw, ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII'], true);
    if ($detected && $detected !== 'UTF-8') {
        $converted = iconv($detected, 'UTF-8//IGNORE', $raw);
        if ($converted !== false) {
            $raw = $converted;
            $encoding = $detected;
        }
    }
}

// ─── Pagination ──────────────────────────────────────────────────────
$lines = preg_split('/\r\n|\r|\n/', $raw);
$totalLines = count($lines);

// Get user's saved preference, or default to 50
$perPage = isset($_GET['lines']) ? (int)$_GET['lines'] : 0;
$validPerPage = [10, 25, 50, 100, 200, 500];
if (!in_array($perPage, $validPerPage, true)) {
    $username = $_SESSION['username'] ?? '';
    $perPage = $username ? GetUserLogRowsPerPage($username, 50) : 50;
}

$currentPage = max(1, (int)($_GET['page'] ?? 1));
$totalPages  = max(1, (int)ceil($totalLines / $perPage));
if ($currentPage > $totalPages) $currentPage = $totalPages;

$startLine = ($currentPage - 1) * $perPage;
$pageLines = array_slice($lines, $startLine, $perPage);

// ─── Language detection (extension → highlight.js class) ─────────────
$ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
$fileName = basename($filePath);
$langMap = [
    // Code
    'php'       => 'php',
    'js'        => 'javascript',
    'jsx'       => 'javascript',
    'mjs'       => 'javascript',
    'cjs'       => 'javascript',
    'ts'        => 'typescript',
    'tsx'       => 'typescript',
    'mts'       => 'typescript',
    'cts'       => 'typescript',
    'py'        => 'python',
    'rb'        => 'ruby',
    'java'      => 'java',
    'c'         => 'c',
    'cpp'       => 'cpp',
    'cc'        => 'cpp',
    'cxx'       => 'cpp',
    'h'         => 'c',
    'hpp'       => 'cpp',
    'hh'        => 'cpp',
    'hxx'       => 'cpp',
    'cs'        => 'csharp',
    'go'        => 'go',
    'rs'        => 'rust',
    'swift'     => 'swift',
    'kt'        => 'kotlin',
    'kts'       => 'kotlin',
    'scala'     => 'scala',
    'pl'        => 'perl',
    'pm'        => 'perl',
    'lua'       => 'lua',
    'r'         => 'r',
    'sql'       => 'sql',
    // Web
    'html'      => 'html',
    'htm'       => 'html',
    'css'       => 'css',
    'scss'      => 'scss',
    'sass'      => 'sass',
    'less'      => 'less',
    'vue'       => 'vue',
    'svelte'    => 'svelte',
    // Config / markup
    'xml'       => 'xml',
    'json'      => 'json',
    'yaml'      => 'yaml',
    'yml'       => 'yaml',
    'toml'      => 'ini',
    'ini'       => 'ini',
    'cfg'       => 'ini',
    'conf'      => 'ini',
    'md'        => 'markdown',
    'tex'       => 'latex',
    'latex'     => 'latex',
    // Shell / scripting
    'sh'        => 'bash',
    'bash'      => 'bash',
    'zsh'       => 'bash',
    'bat'       => 'dos',
    'cmd'       => 'dos',
    'ps1'       => 'powershell',
    'psm1'      => 'powershell',
    'coffee'    => 'coffeescript',
    'clj'       => 'clojure',
    'cljs'      => 'clojure',
    'edn'       => 'clojure',
    'erl'       => 'erlang',
    'ex'        => 'elixir',
    'exs'       => 'elixir',
    'gradle'    => 'gradle',
    'dockerfile'=> 'dockerfile',
    // Plain text
    'txt'       => 'plaintext',
    'log'       => 'plaintext',
    'rtf'       => 'plaintext',
    'gitignore' => 'plaintext',
    'env'       => 'plaintext',
    'editorconfig' => 'plaintext',
];

// Special filename handling
$lang = 'plaintext';
if (strcasecmp($fileName, 'Dockerfile') === 0) {
    $lang = 'dockerfile';
} elseif (strcasecmp($fileName, 'Makefile') === 0 || strcasecmp($fileName, 'makefile') === 0) {
    $lang = 'makefile';
} elseif (isset($langMap[$ext])) {
    $lang = $langMap[$ext];
}

// ─── RTF handling ────────────────────────────────────────────────────
if ($ext === 'rtf') {
    // Strip RTF control words — crude but effective for text extraction
    $text = preg_replace('/\\\\([a-z]+)(-?\d+)?[ ]?/', '', $raw);
    $text = preg_replace('/\{|\}|\\\\[^\s]/', '', $text);
    $text = preg_replace('/\n{3,}/', "\n\n", trim($text));
    $lines = preg_split('/\r\n|\r|\n/', $text);
    $totalLines = count($lines);
    $totalPages = max(1, (int)ceil($totalLines / $perPage));
    if ($currentPage > $totalPages) $currentPage = $totalPages;
    $startLine = ($currentPage - 1) * $perPage;
    $pageLines = array_slice($lines, $startLine, $perPage);
}

// ─── Assemble page content ───────────────────────────────────────────
$contentHtml = '';
foreach ($pageLines as $i => $line) {
    $lineNum = $startLine + $i + 1;
    $escaped = htmlspecialchars($line, ENT_NOQUOTES, 'UTF-8');
    $contentHtml .= $escaped . "\n";
}

$displayName = htmlspecialchars($fileName);
$fileSize = filesize($filePath);
$sizeFormatted = formatBytes($fileSize);
$encDisplay = htmlspecialchars($encoding);

// ─── Helper ──────────────────────────────────────────────────────────
function formatBytes($bytes) {
    if ($bytes === 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes, 1024));
    return round($bytes / pow(1024, $i), 1) . ' ' . $units[$i];
}

// ─── Generate pagination HTML (matching log pagination style) ────────
function renderTextPagination($currentPage, $totalPages, $perPage) {
    if ($totalPages <= 1) return '';

    $html = '<div class="text-pagination">';

    // Build base query string (preserve url)
    $qs = 'url=' . urlencode($_GET['url'] ?? '');
    $qs .= '&lines=' . $perPage;

    // Previous
    if ($currentPage > 1) {
        $html .= '<a href="?' . $qs . '&page=' . ($currentPage - 1) . '" class="page-link">&laquo; Prev</a> ';
    } else {
        $html .= '<span class="page-link disabled">&laquo; Prev</span> ';
    }

    // Window around current
    $window = 5;
    $pages = [];
    $pages[] = 1;
    $start = max(2, $currentPage - $window);
    $end   = min($totalPages - 1, $currentPage + $window);
    if ($start > 2) $pages[] = '…';
    for ($i = $start; $i <= $end; $i++) $pages[] = $i;
    if ($end < $totalPages - 1) $pages[] = '…';
    if ($totalPages > 1) $pages[] = $totalPages;

    foreach ($pages as $p) {
        if ($p === '…') {
            $html .= '<span class="page-ellipsis">&hellip;</span> ';
            continue;
        }
        if ($p == $currentPage) {
            $html .= '<span class="page-link current">' . $p . '</span> ';
        } else {
            $html .= '<a href="?' . $qs . '&page=' . $p . '" class="page-link">' . $p . '</a> ';
        }
    }

    // Next
    if ($currentPage < $totalPages) {
        $html .= '<a href="?' . $qs . '&page=' . ($currentPage + 1) . '" class="page-link">Next &raquo;</a>';
    } else {
        $html .= '<span class="page-link disabled">Next &raquo;</span>';
    }

    $html .= '</div>';
    return $html;
}

// ─── Output ──────────────────────────────────────────────────────────
header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="/css/elfinderThemes/highlightjs-custom.css">
<link rel="stylesheet" href="/css/siteThemes/<?= htmlspecialchars($themeId) ?>.css">
<script src="/libraries/highlightjs/highlight.min.js"></script>
<title><?= $displayName ?> — Preview</title>
</head>
<body class="text-preview-body <?= $themeClass ?>">


<div class="text-info-bar">
    <span class="text-info-filename"><?= $displayName ?></span>
    <span class="text-info-sep">·</span>
    <span class="text-info-size"><?= $sizeFormatted ?></span>
    <span class="text-info-sep">·</span>
    <span class="text-info-encoding"><?= $encDisplay ?></span>
    <span class="text-info-sep">·</span>
    <span class="text-info-lines"><?= number_format($totalLines) ?> lines</span>
    <span class="text-info-sep">·</span>
    <span class="text-info-page">Page <?= $currentPage ?> / <?= $totalPages ?></span>
    <span class="text-info-sep">·</span>
    <span class="text-info-lang"><?= htmlspecialchars($lang) ?></span>
</div>

<pre><code class="language-<?= htmlspecialchars($lang) ?>"><?php
// Output content with trailing newline preserved
echo rtrim($contentHtml, "\n");
?></code></pre>

<?php
$paginationHtml = renderTextPagination($currentPage, $totalPages, $perPage);
if ($paginationHtml) {
    echo $paginationHtml;
}
?>

<style>
/* ── Line numbers (CSS counters) ───────────────────────────────── */
pre {
    counter-reset: linenum <?= $startLine ?>;
}
code {
    display: block;
}
code .hljs-ln-line {
    display: block;
    min-height: 1.4em;
}
code .hljs-ln-line::before {
    counter-increment: linenum;
    content: counter(linenum);
    display: inline-block;
    width: 3.5em;
    padding-right: 1em;
    text-align: right;
    color: var(--hljs-linenum, #555);
    user-select: none;
    opacity: 0.6;
}
</style>

<script>
(function() {
    // Apply line-number spans after highlight.js runs
    // Wait for hljs to finish, then wrap each line
    document.addEventListener('DOMContentLoaded', function() {
        hljs.highlightAll();

        // Post-process: wrap each line in a span for CSS counter
        var codeBlocks = document.querySelectorAll('code');
        codeBlocks.forEach(function(code) {
            var html = code.innerHTML;
            var lines = html.split('\n');
            // Only wrap if not already wrapped by hljs in <span> per line
            var wrapped = lines.map(function(line) {
                return '<span class="hljs-ln-line">' + (line || '') + '</span>';
            }).join('\n');
            code.innerHTML = wrapped;
        });
    });
})();
</script>

</body>
</html>
