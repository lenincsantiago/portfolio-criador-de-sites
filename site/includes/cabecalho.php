<?php
// Variáveis esperadas: $site, $titulo, $descricao, $pagina, $indexar (opcional), $grafo (opcional, lista de nós JSON-LD),
// $migalhas (opcional, lista de [nome, caminho] para o BreadcrumbList), $waMensagem (opcional)
// e $canonico (opcional, ex.: 'servicos/design-responsivo').
// $baseRaiz (opcional): a página pode ser aberta num endereço com pasta (/servicos/nome, /pasta/nao-existe).
cabecalhos_seguranca();
$indexar = $indexar ?? true;
$grafo = $grafo ?? [];
$migalhas = $migalhas ?? [];
$wa = wa_link($site, $waMensagem ?? (string) ($site['msg_wa'] ?? ''));

// Endereço canônico: evita páginas duplicadas para o Google
$base = url_base();
// Marca (logo, títulos, rodapé): "Sua Marca", o mesmo nome que o domínio soletra.
// O nome completo ($site['nome']) fica para a pessoa: política de privacidade, © e dados de autor.
$marca = (string) ($site['marca'] ?? $site['nome'] ?? '');
$dominioMarca = (string) parse_url($base, PHP_URL_HOST);
$linkedin = validar_url($site['linkedin'] ?? '');
$perfis = $linkedin !== null && $linkedin !== '#' ? [$linkedin] : [];
$caminhoCanonico = $canonico ?? basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
$urlCanonica = $caminhoCanonico === 'index.php' ? $base : $base . $caminhoCanonico;
$urlImagem = $base . 'assets/img/og-image.png';
$verificacaoGoogle = (string) (config()['google_verificacao'] ?? '');

// Dados estruturados: o negócio aparece em todas as páginas; cada página acrescenta os seus nós
$idNegocio = $base . '#negocio';
$nos = [[
    '@type'       => 'ProfessionalService',
    '@id'         => $idNegocio,
    'name'        => $marca,
    'alternateName' => array_values(array_unique(array_filter([(string) ($site['nome'] ?? ''), $dominioMarca], fn($n) => $n !== '' && $n !== $marca))),
    'url'         => $base,
    'description' => $site['seo']['descricao'] ?? '',
    'image'       => $urlImagem,
    'logo'        => $base . 'assets/img/icone-512.png',
    'telephone'   => '+' . preg_replace('/\D/', '', (string) ($site['whatsapp'] ?? '')),
    'email'       => $site['email'] ?? '',
    'areaServed'  => ['@type' => 'Country', 'name' => 'Brasil'],
    'founder'     => array_filter(['@type' => 'Person', 'name' => $site['nome'] ?? '', 'jobTitle' => $site['cargo'] ?? '', 'image' => $base . 'assets/img/perfil-480.webp', 'sameAs' => $perfis]),
]];
if ($perfis) {
    $nos[0]['sameAs'] = $perfis;
}
// Cidade base (sem rua nem número): ajuda nas buscas da região sem deixar de atender o Brasil todo
$cidadeBase = (string) ($site['seo']['cidade'] ?? '');
if ($cidadeBase !== '') {
    $ufBase = (string) ($site['seo']['uf'] ?? '');
    $nos[0]['address'] = array_filter(['@type' => 'PostalAddress', 'addressLocality' => $cidadeBase, 'addressRegion' => $ufBase, 'addressCountry' => 'BR']);
    $nos[0]['areaServed'] = [['@type' => 'City', 'name' => $cidadeBase], ['@type' => 'Country', 'name' => 'Brasil']];
}
if (($pagina ?? '') === 'inicio') {
    // O site em si (nome e idioma), só na página inicial
    $nos[] = ['@type' => 'WebSite', '@id' => $base . '#site', 'url' => $base, 'name' => $marca, 'alternateName' => $dominioMarca, 'inLanguage' => 'pt-BR', 'publisher' => ['@id' => $idNegocio]];
    // Lista de serviços com o link de cada página: ajuda o Google a entender o que o negócio oferece
    $ofertas = [];
    foreach ((array) ($site['servicos'] ?? []) as $s) {
        if (is_array($s) && !empty($s['slug']) && is_string($s['slug'])) {
            $ofertas[] = ['@type' => 'Offer', 'itemOffered' => [
                '@type' => 'Service',
                'name'  => (string) ($s['titulo'] ?? ''),
                'url'   => $base . url_servico($s['slug']),
            ]];
        }
    }
    if ($ofertas) {
        $nos[0]['hasOfferCatalog'] = ['@type' => 'OfferCatalog', 'name' => 'Criação de sites para empresas e profissionais', 'itemListElement' => $ofertas];
    }
}
if ($migalhas) {
    $itens = [['@type' => 'ListItem', 'position' => 1, 'name' => t('menu_inicio'), 'item' => $base]];
    foreach ($migalhas as $i => [$nomeMigalha, $caminho]) {
        $itens[] = ['@type' => 'ListItem', 'position' => $i + 2, 'name' => $nomeMigalha, 'item' => $base . $caminho];
    }
    $nos[] = ['@type' => 'BreadcrumbList', 'itemListElement' => $itens];
}
$aqui = !empty($baseRaiz) ? (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) : '';
$jsonld = $indexar
    ? json_encode(['@context' => 'https://schema.org', '@graph' => array_merge($nos, $grafo)], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE)
    : '';
?>
<!DOCTYPE html>
<?php /* "flutuantes-espera" já no HTML: os botões flutuantes nascem escondidos e não piscam antes do main.js rodar */ ?>
<html lang="pt-BR" class="flutuantes-espera">
<head>
    <meta charset="UTF-8">
    <?php if (!empty($baseRaiz)): ?>
    <!-- Página aberta num endereço com pasta (ex.: /servicos/nome ou /pasta/nao-existe): os links e arquivos partem da raiz do site -->
    <base href="<?= e(parse_url(url_base(), PHP_URL_PATH) ?: '/') ?>">
    <?php endif; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= e($titulo) ?></title>
    <meta name="description" content="<?= e($descricao) ?>">
    <meta name="robots" content="<?= $indexar ? 'index, follow, max-image-preview:large' : 'noindex, follow' ?>">
    <?php if ($indexar): ?>
    <link rel="canonical" href="<?= e($urlCanonica) ?>">
    <?php endif; ?>
    <meta name="theme-color" content="#e9e1d3">
    <meta name="color-scheme" content="light">
    <?php if ($verificacaoGoogle !== ''): ?>
    <meta name="google-site-verification" content="<?= e($verificacaoGoogle) ?>">
    <?php endif; ?>

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= e($marca) ?>">
    <meta property="og:title" content="<?= e($titulo) ?>">
    <meta property="og:description" content="<?= e($descricao) ?>">
    <meta property="og:url" content="<?= e($urlCanonica) ?>">
    <meta property="og:image" content="<?= e($urlImagem) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?= e(($site['nome'] ?? '') . ', ' . ($site['cargo'] ?? '')) ?>">
    <meta property="og:locale" content="pt_BR">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($titulo) ?>">
    <meta name="twitter:description" content="<?= e($descricao) ?>">
    <meta name="twitter:image" content="<?= e($urlImagem) ?>">
    <meta name="twitter:image:alt" content="<?= e(($site['nome'] ?? '') . ', ' . ($site['cargo'] ?? '')) ?>">

    <link rel="icon" href="assets/img/favicon.svg" type="image/svg+xml">
    <link rel="icon" href="assets/img/favicon-32.png" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="assets/img/apple-touch-icon.png" sizes="180x180">
    <link rel="manifest" href="site.webmanifest">
    <?php // A fonte do título é baixada logo no começo: o título não aparece com outra fonte e depois "troca" ?>
    <link rel="preload" href="assets/fonts/inter-latin.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= (int) @filemtime(RAIZ . '/assets/css/style.css') ?>">
    <script src="assets/js/cedo.js?v=<?= (int) @filemtime(RAIZ . '/assets/js/cedo.js') ?>"></script>
    <?php if ($jsonld): ?>
    <script type="application/ld+json"><?= $jsonld ?></script>
    <?php endif; ?>
</head>
<body>
    <a class="pular" href="<?= e($aqui) ?>#conteudo"><?= e(t('pular_conteudo')) ?></a>
    <div class="progresso" aria-hidden="true"><span></span></div>

    <header class="barra">
        <?php // No celular a marca vem em duas linhas ("Sua" / "Marca"); no computador, em uma ?>
        <a href="./" class="marca" translate="no" aria-label="<?= e($marca . ': ' . t('menu_inicio')) ?>"><img class="marca-simbolo" src="assets/img/logo-simbolo.svg" alt="" width="30" height="31"><span class="marca-nome"><?php foreach (preg_split('/\s+(?=\S+$)/u', $marca, 2) ?: [$marca] as $i => $parteMarca): ?><?= $i ? ' ' : '' ?><span><?= e($parteMarca) ?></span><?php endforeach; ?></span></a>
        <?php // A ordem no HTML segue a ordem na tela (marca, menu, botão): quem usa só o teclado percorre na mesma sequência que vê ?>
        <?php
        // Menu: leva às seções da página inicial. Na própria página inicial o link é só a âncora (sem recarregar).
        // Seções que dependem de conteúdo (processo, projetos, perguntas) só entram no menu quando existem.
        $temItens = static fn(string $k): bool => (bool) array_filter((array) ($site[$k] ?? []), 'is_array');
        $ancora = static fn(string $id): string => (($pagina ?? '') === 'inicio' ? '' : './') . '#' . $id;
        ?>
        <nav class="nav" id="nav" aria-label="Principal">
            <a href="<?= e($ancora('sobre')) ?>"><?= e(t('menu_sobre')) ?></a>
            <a href="<?= e($ancora('servicos')) ?>"><?= e(t('menu_servicos')) ?></a>
            <?php if ($temItens('processo')): ?>
            <a href="<?= e($ancora('processo')) ?>"><?= e(t('menu_processo')) ?></a>
            <?php endif; ?>
            <?php if ($temItens('projetos')): ?>
            <?php // Leva à seção de projetos da página inicial (lá fica o link "Ver todos os projetos") ?>
            <a href="<?= e($ancora('projetos')) ?>"><?= e(t('menu_projetos')) ?></a>
            <?php endif; ?>
            <?php if ($temItens('faq')): ?>
            <a href="<?= e($ancora('faq')) ?>"><?= e(t('menu_faq')) ?></a>
            <?php endif; ?>
            <a href="<?= e($ancora('contato')) ?>"><?= e(t('menu_contato')) ?></a>
        </nav>
        <div class="barra-direita">
            <a href="<?= e($wa) ?>" class="btn-topo" <?= LINK_EXTERNO ?> data-evento="whatsapp_topo"><?= e(t('btn_orcamento')) ?></a>
            <a href="<?= e($aqui) ?>#nav" class="hamb" id="hamb" role="button" aria-label="<?= e(t('menu_abrir')) ?>" aria-expanded="false" aria-controls="nav"><i></i><i></i></a>
        </div>
    </header>
