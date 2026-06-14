<?php
// previewPptx.php
// Minimal PPTX/PPT/ODP -> HTML preview endpoint using PHPPresentation.
// Falls back to ZIP/XML extraction if the PHPPresentation reader crashes on images.
if(!defined('__ROOT__')){define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);}
require_once __ROOT__ . '/vendor/autoload.php';

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

$path = $_GET['url'] ?? $_GET['path'] ?? '';
if (!$path) {
    http_response_code(400);
    echo 'Missing path or url parameter';
    exit;
}

$path = urldecode($path);
$path = str_replace('\\', '/', $path);

if (strpos($path, '/') === 0) {
    $filePath = rtrim(__ROOT__, '/\\') . $path;
} else {
    $filePath = rtrim(__ROOT__, '/\\') . '/' . ltrim($path, '/');
}

if (!file_exists($filePath) || !is_readable($filePath)) {
    http_response_code(404);
    echo 'File not found: ' . $filePath;
    exit;
}

$ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
$allowedExts = ['pptx', 'ppt', 'odp'];
if (!in_array($ext, $allowedExts)) {
    http_response_code(400);
    echo 'Unsupported file type';
    exit;
}

// --- Try PHPPresentation reader first ---
$allSlidesHtml = '';
$slideCount = 0;
$readSuccess = false;

try {
    $phPresentation = @\PhpOffice\PhpPresentation\IOFactory::load($filePath);
    if ($phPresentation) {
        $slideCount = $phPresentation->getSlideCount();
        $slideNum = 0;

        foreach ($phPresentation->getAllSlides() as $slide) {
            $slideNum++;
            $shapesHtml = '';

            foreach ($slide->getShapeCollection() as $shape) {
                if ($shape instanceof \PhpOffice\PhpPresentation\Shape\RichText) {
                    $text = '';
                    foreach ($shape->getParagraphs() as $paragraph) {
                        foreach ($paragraph->getRichTextElements() as $element) {
                            if ($element instanceof \PhpOffice\PhpPresentation\Shape\RichText\RunElement ||
                                $element instanceof \PhpOffice\PhpPresentation\Shape\RichText\TextElement) {
                                $text .= htmlspecialchars($element->getText());
                            } elseif ($element instanceof \PhpOffice\PhpPresentation\Shape\RichText\BreakElement) {
                                $text .= "\n";
                            }
                        }
                        $text .= "\n";
                    }
                    if (trim($text) !== '') {
                        $shapesHtml .= '<p>' . nl2br(trim($text)) . '</p>';
                    }

                } elseif ($shape instanceof \PhpOffice\PhpPresentation\Shape\Table) {
                    $shapesHtml .= '<table>';
                    foreach ($shape->getRows() as $row) {
                        $shapesHtml .= '<tr>';
                        foreach ($row->getCells() as $cell) {
                            $cellText = '';
                            foreach ($cell->getParagraphs() as $p) {
                                foreach ($p->getRichTextElements() as $e) {
                                    if ($e instanceof \PhpOffice\PhpPresentation\Shape\RichText\TextElement ||
                                        $e instanceof \PhpOffice\PhpPresentation\Shape\RichText\RunElement) {
                                        $cellText .= $e->getText();
                                    }
                                }
                            }
                            $shapesHtml .= '<td>' . htmlspecialchars($cellText) . '</td>';
                        }
                        $shapesHtml .= '</tr>';
                    }
                    $shapesHtml .= '</table>';

                } elseif ($shape instanceof \PhpOffice\PhpPresentation\Shape\Drawing ||
                          $shape instanceof \PhpOffice\PhpPresentation\Shape\Drawing\File) {
                    try {
                        $path_drawing = $shape->getPath();
                        // Only render if the image file actually exists
                        if ($path_drawing && file_exists($path_drawing)) {
                            $mimeType = $shape->getMimeType();
                            $imageContents = file_get_contents($path_drawing);
                            $base64 = base64_encode($imageContents);
                            $imgDesc = htmlspecialchars($shape->getName() ?: 'Image');
                            $shapesHtml .= '<div class="slide-image">';
                            $shapesHtml .= '<img src="data:' . $mimeType . ';base64,' . $base64 . '" alt="' . $imgDesc . '">';
                            $shapesHtml .= '</div>';
                        } else {
                            $shapesHtml .= '<p style="color:#999;font-style:italic;">(Image: ' . htmlspecialchars($shape->getName() ?: 'unknown') . ')</p>';
                        }
                    } catch (Exception $e) {
                        $shapesHtml .= '<p style="color:#999;font-style:italic;">(Image: ' . htmlspecialchars($shape->getName() ?: 'unknown') . ')</p>';
                    }
                } elseif ($shape instanceof \PhpOffice\PhpPresentation\Shape\Media) {
                    $mediaName = htmlspecialchars($shape->getName() ?: 'Media');
                    $shapesHtml .= '<p style="color:#999;font-style:italic;">[Media: ' . $mediaName . ']</p>';
                }
            }

            if (empty(trim(strip_tags($shapesHtml)))) {
                $shapesHtml = '<p style="color:#888;font-style:italic;">(Empty slide)</p>';
            }

            $allSlidesHtml .= '<div class="slide-wrapper">';
            $allSlidesHtml .= $shapesHtml;
            $allSlidesHtml .= '</div>';
        }
        $readSuccess = true;
    }
} catch (\Throwable $e) {
    // PHPPresentation reader failed (likely corrupted image in PPTX)
    // Fall through to ZIP fallback
}

// --- Fallback: ZIP/XML text extraction ---
if (!$readSuccess) {
    try {
        $zip = new ZipArchive();
        $zipOpen = $zip->open($filePath);
        if ($zipOpen === true) {
            // Count slides by looking for slide XML files
            $slideNum = 0;
            for ($i = 1; $i <= 999; $i++) {
                $slideXml = $zip->getFromName('ppt/slides/slide' . $i . '.xml');
                if ($slideXml === false) break;

                $slideNum++;
                $slideCount = $slideNum;

                // Parse text from <a:t> elements using a simple regex
                preg_match_all('/<a:t[^>]*>([^<]+)<\/a:t>/i', $slideXml, $matches);
                $texts = $matches[1] ?? [];

                $slideHtml = '';
                foreach ($texts as $t) {
                    $decoded = htmlspecialchars_decode($t);
                    $slideHtml .= '<p>' . nl2br(htmlspecialchars(trim($decoded))) . '</p>';
                }

                // Check for images in the slide (look for <p:blipFill> or <a:blip>)
                preg_match('/<a:blip[^>]*r:embed="[^"]*"[^>]*\/?>/i', $slideXml, $imgMatch);
                if (!empty($imgMatch)) {
                    $slideHtml .= '<p style="color:#999;font-style:italic;">[Image present]</p>';
                }

                if (empty(trim(strip_tags($slideHtml)))) {
                    $slideHtml = '<p style="color:#888;font-style:italic;">(Empty slide)</p>';
                }

                $allSlidesHtml .= '<div class="slide-wrapper">';
                $allSlidesHtml .= $slideHtml;
                $allSlidesHtml .= '</div>';
            }
            $zip->close();

            if ($slideCount === 0) {
                $allSlidesHtml = '<div class="slide-wrapper"><p>Could not read presentation content.</p></div>';
            }
        } else {
            $allSlidesHtml = '<div class="slide-wrapper"><p>Failed to open presentation file.</p></div>';
        }
    } catch (\Throwable $e) {
        $allSlidesHtml = '<div class="slide-wrapper"><p>Could not parse presentation: ' . htmlspecialchars($e->getMessage()) . '</p></div>';
    }
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
        body, body * { color: #111 !important; }
        .slide-wrapper {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.15);
            margin: 20px auto;
            padding: 40px;
            max-width: 1000px;
            min-height: 200px;
            position: relative;
            overflow: hidden;
        }
        .slide-wrapper p { margin-bottom: 8px; font-size: 14px; }
        .slide-wrapper .slide-image { text-align: center; margin: 12px 0; }
        .slide-wrapper .slide-image img {
            max-width: 100%; max-height: 500px;
            border-radius: 4px; box-shadow: 0 1px 6px rgba(0,0,0,0.1);
        }
        .slide-number {
            position: absolute; bottom: 12px; right: 16px;
            font-size: 0.8rem; color: #999 !important;
        }
        table { border-collapse: collapse; width: 100%; margin: 8px 0; }
        th, td { border: 1px solid #666; padding: 6px 10px; text-align: left; vertical-align: top; }
        th { background: #e8e8e8; font-weight: 700; }
        .slide-nav { text-align: center; margin: 12px 0; }
        .slide-nav a {
            display: inline-block; padding: 6px 14px; margin: 0 2px;
            background: #ddd; color: #333 !important; text-decoration: none;
            border-radius: 4px; font-size: 13px;
        }
        .slide-nav a.selected { background: #fff; font-weight: 700; border: 1px solid #888; }
        .slide-nav a:hover { background: #ccc; }
        @media (max-width: 600px) {
            body { padding: 8px; }
            .slide-wrapper { padding: 16px; min-height: 150px; }
        }
    </style>
</head>
<body>
    <?php
    $displayName = htmlspecialchars(basename($filePath));
    echo '<h1 style="font-size:1.2rem;margin-bottom:8px;color:#333 !important;">' . $displayName . '</h1>';

    if ($readSuccess) {
        echo '<p style="font-size:0.85rem;color:#555 !important;margin-bottom:12px;">' . $slideCount . ' slide(s)</p>';
    } else {
        echo '<p style="font-size:0.85rem;color:#888 !important;margin-bottom:12px;">' . $slideCount . ' slide(s) &middot; Text-only preview (image data could not be loaded)</p>';
    }
    ?>
    <div class="slide-nav" id="slide-nav"></div>
    <div id="slides-container">
        <?php echo $allSlidesHtml; ?>
    </div>
    <div class="slide-nav" id="slide-nav-bottom"></div>

    <script>
    (function() {
        var container = document.getElementById('slides-container');
        var slides = container.querySelectorAll('.slide-wrapper');
        if (slides.length <= 1) return;

        slides.forEach(function(slide, i) {
            var num = document.createElement('div');
            num.className = 'slide-number';
            num.textContent = (i + 1) + ' / ' + slides.length;
            slide.appendChild(num);
            if (i > 0) slide.style.display = 'none';
        });

        function showSlide(idx) {
            slides.forEach(function(s, i) {
                s.style.display = (i === idx) ? 'block' : 'none';
            });
            document.querySelectorAll('.slide-nav a').forEach(function(a, i) {
                a.className = (i === idx) ? 'selected' : '';
            });
        }

        var navHtml = '';
        for (var i = 0; i < slides.length; i++) {
            navHtml += '<a href="#" data-slide="' + i + '"' + (i === 0 ? ' class="selected"' : '') + '>' + (i + 1) + '</a>';
        }
        document.getElementById('slide-nav').innerHTML = navHtml;
        document.getElementById('slide-nav-bottom').innerHTML = navHtml;

        document.querySelectorAll('.slide-nav a').forEach(function(a) {
            a.addEventListener('click', function(e) {
                e.preventDefault();
                showSlide(parseInt(this.getAttribute('data-slide')));
            });
        });
    })();
    </script>
</body>
</html>
