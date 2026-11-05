<?php
declare(strict_types=1);
// Servido em /robots.txt pela regra do .htaccess.
require __DIR__ . '/includes/dados.php';
$base = url_base();

header('Content-Type: text/plain; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /contato.php\n";
echo "Disallow: /evento.php\n";
echo "Disallow: /exemplos/\n\n";
echo 'Sitemap: ' . $base . "sitemap.xml\n";
