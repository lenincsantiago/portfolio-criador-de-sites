<?php
declare(strict_types=1);
// Mapa do site para o Google. Servido em /sitemap.xml pela regra do .htaccess.
// Lista só páginas indexáveis; páginas de erro ficam de fora.
require __DIR__ . '/includes/dados.php';

$site = carregar_dados();
$base = url_base();

/** Data da última mudança real: o arquivo da página ou o conteúdo salvo, o que for mais recente. */
$mudou = static function (string ...$arquivos): string {
    $ts = 0;
    foreach ($arquivos as $arq) {
        $ts = max($ts, (int) @filemtime(RAIZ . '/' . $arq));
    }
    return date('Y-m-d', $ts ?: time());
};

$paginas = [
    ['', $mudou('index.php', 'data/site.json', 'includes/textos.php')],
    ['politica-de-privacidade.php', $mudou('politica-de-privacidade.php', 'includes/textos.php')],
];
// A página de projetos só entra quando tiver projetos (sem eles ela fica com noindex)
if (array_filter((array) ($site['projetos'] ?? []), 'is_array')) {
    $paginas[] = ['projetos.php', $mudou('projetos.php', 'data/site.json')];
}
foreach ($site['servicos'] ?? [] as $s) {
    if (!empty($s['slug']) && is_string($s['slug'])) {
        $paginas[] = [url_servico($s['slug']), $mudou('servico.php', 'data/site.json')];
    }
}

header('Content-Type: application/xml; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($paginas as [$pagina, $data]) {
    echo "    <url>\n";
    echo '        <loc>' . htmlspecialchars($base . $pagina, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</loc>\n";
    echo '        <lastmod>' . $data . "</lastmod>\n";
    echo "    </url>\n";
}
echo "</urlset>\n";
