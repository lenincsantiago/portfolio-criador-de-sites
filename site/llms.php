<?php
declare(strict_types=1);
// Resumo do site para robôs de IA. Servido em /llms.txt pela regra do .htaccess.
// Montado com os mesmos dados do painel, como o sitemap: muda sozinho quando um serviço muda.
require __DIR__ . '/includes/dados.php';

$site = carregar_dados();
$base = url_base();
$linha = static fn(string $texto): string => trim((string) preg_replace('/\s+/u', ' ', $texto));

// WhatsApp no formato +55 00 00000-0000
$numero = preg_replace('/\D/', '', (string) ($site['whatsapp'] ?? ''));
$whatsapp = strlen($numero) >= 12
    ? '+' . substr($numero, 0, 2) . ' ' . substr($numero, 2, 2) . ' ' . substr($numero, 4, -4) . '-' . substr($numero, -4)
    : '';
$cargo = (string) ($site['cargo'] ?? '');
$cargo = $cargo !== '' ? mb_strtoupper(mb_substr($cargo, 0, 1)) . mb_strtolower(mb_substr($cargo, 1)) : '';
$contatos = array_filter([$whatsapp !== '' ? 'WhatsApp ' . $whatsapp : '', (string) ($site['email'] ?? '')]);

header('Content-Type: text/plain; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

echo '# ' . $linha((string) ($site['marca'] ?? $site['nome'] ?? '')) . "\n\n";
echo '> ' . $linha((string) ($site['seo']['descricao'] ?? '')) . "\n\n";
echo ($cargo !== '' ? $cargo . ': ' : '') . $linha((string) ($site['nome'] ?? '')) . '.'
    . ($contatos ? ' Contato: ' . implode(', ', $contatos) . '.' : '') . "\n\n";

echo "## Serviços\n";
foreach ((array) ($site['servicos'] ?? []) as $s) {
    if (is_array($s) && !empty($s['slug']) && is_string($s['slug'])) {
        echo '- [' . $linha((string) ($s['titulo'] ?? '')) . '](' . $base . url_servico($s['slug']) . ")\n";
    }
}

echo "\n## Mais\n";
// Projetos só entram quando existem (sem eles a página fica com noindex)
if (array_filter((array) ($site['projetos'] ?? []), 'is_array')) {
    echo '- [Projetos de demonstração](' . $base . "projetos.php)\n";
}
echo '- [Política de privacidade](' . $base . "politica-de-privacidade.php)\n";
