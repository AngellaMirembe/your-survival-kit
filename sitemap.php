<?php
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/xml; charset=utf-8');

$base = rtrim(BASE_URL, '/');
$pages = [
    ['',        'weekly', '1.0'],
    ['about',    'monthly', '0.8'],
    ['what-we-do', 'monthly', '0.8'],
    ['gallery',  'weekly',  '0.6'],
    ['news',     'daily',   '0.9'],
    ['events',   'weekly',  '0.8'],
    ['donate',   'monthly', '0.7'],
    ['volunteer','monthly', '0.7'],
    ['contact',  'monthly', '0.6'],
];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach ($pages as $p) {
    echo "  <url><loc>" . e($base . '/' . $p[0]) . "</loc><changefreq>{$p[1]}</changefreq><priority>{$p[2]}</priority></url>\n";
}

$posts = $pdo->query('SELECT slug, updated_at FROM news WHERE status="published" ORDER BY published_at DESC')->fetchAll();
foreach ($posts as $post) {
    echo '  <url><loc>' . e($base . '/news/' . $post['slug']) . '</loc>'
        . '<lastmod>' . e(date('Y-m-d', strtotime($post['updated_at']))) . '</lastmod>'
        . '<changefreq>monthly</changefreq><priority>0.7</priority></url>' . "\n";
}
echo '</urlset>' . "\n";
