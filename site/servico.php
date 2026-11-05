<?php
declare(strict_types=1);
require __DIR__ . '/includes/dados.php';

$site = carregar_dados();
$servicos = array_values(array_filter((array) ($site['servicos'] ?? []), 'is_array'));
$slug = get_txt('s', 80);
$frases = [];

$atual = null;
foreach ($servicos as $s) {
    if ($slug !== '' && ($s['slug'] ?? '') === $slug) {
        $atual = $s;
        break;
    }
}
if (!$atual) {
    http_response_code(404);
}
// Aberta como /servicos/nome: os links e arquivos da página partem da raiz do site
$baseRaiz = true;

$grafo = [];
$migalhas = [];
if ($atual) {
    $tituloServico = (string) ($atual['titulo'] ?? '');
    $titulo = $tituloServico . ' · Criação de sites | Sua Marca';
    // Descrição própria para o Google (campo do painel); sem ela, usa a descrição curta do cartão
    $descricaoSeo = trim((string) ($atual['seo'] ?? ''));
    $descricao = $descricaoSeo !== ''
        ? descricao_meta($descricaoSeo)
        : descricao_meta((string) ($atual['texto'] ?? '') ?: (string) ($site['seo']['descricao'] ?? ''), t('serv_descricao_extra'));
    $canonico = url_servico((string) $atual['slug']);
    $grafo[] = [
        '@type'       => 'Service',
        'name'        => $tituloServico,
        'description' => (string) ($atual['texto'] ?? ''),
        'serviceType' => $tituloServico,
        'url'         => url_base() . $canonico,
        'provider'    => ['@id' => url_base() . '#negocio'],
        'areaServed'  => ['@type' => 'Country', 'name' => 'Brasil'],
    ];
    $migalhas = [[$tituloServico, $canonico]];
    $waMensagem = t('wa_servico', ['servico' => $tituloServico]);
} else {
    $titulo = t('serv_404_titulo') . ' | Sua Marca';
    $descricao = descricao_meta((string) ($site['seo']['descricao'] ?? ''), t('serv_descricao_extra'));
}
$indexar = (bool) $atual;
$pagina = 'servico';
include __DIR__ . '/includes/cabecalho.php';
?>
    <main class="pagina" id="conteudo" tabindex="-1">
        <?php if ($atual): ?>
        <nav class="migalhas" aria-label="Você está em">
            <a href="./"><?= e(t('menu_inicio')) ?></a> <span aria-hidden="true">/</span>
            <span aria-current="page"><?= e($tituloServico) ?></span>
        </nav>
        <article class="pagina-topo">
            <p class="rotulo"><?= e(t('serv_rotulo')) ?> <?= e($atual['numero'] ?? '') ?></p>
            <h1><?= e($tituloServico) ?></h1>
            <p class="lead"><?= e($atual['texto'] ?? '') ?></p>
            <div class="botoes">
                <a href="<?= e($wa) ?>" <?= LINK_EXTERNO ?> class="btn btn-sol" data-evento="whatsapp_servico"><?= e(t('serv_orcamento')) ?></a>
                <a href="./#contato" class="btn btn-linha"><?= e(t('serv_contato')) ?></a>
            </div>
        </article>

        <section class="secao detalhe-pagina" aria-label="<?= e('Detalhes: ' . $tituloServico) ?>">
            <?php if (!empty($atual['para_quem'])): ?>
            <h2><?= e(t('serv_para_quem')) ?></h2>
            <p><?= e($atual['para_quem']) ?></p>
            <?php endif; ?>
            <?php $incluso = array_filter(array_map('trim', explode("\n", (string) ($atual['incluso'] ?? '')))); ?>
            <?php if ($incluso): ?>
            <h2><?= e(t('serv_incluso')) ?></h2>
            <ul class="incluso">
                <?php foreach ($incluso as $item): ?>
                <li><?= e($item) ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            <?php if (!empty($atual['resultado'])): ?>
            <p class="resultado"><strong><?= e(t('serv_resultado')) ?>:</strong> <?= e($atual['resultado']) ?></p>
            <?php endif; ?>
            <div class="botoes">
                <a href="<?= e($wa) ?>" <?= LINK_EXTERNO ?> class="btn btn-sol" data-evento="whatsapp_servico"><?= e(t('serv_orcamento')) ?></a>
            </div>
        </section>

        <section class="secao" aria-labelledby="t-outros">
            <h2 id="t-outros"><?= e(t('serv_outros')) ?></h2>
            <ul class="links-servicos">
                <?php foreach ($servicos as $s): if (($s['slug'] ?? '') === $slug || empty($s['slug'])) continue; ?>
                <li><a href="<?= e(url_servico((string) $s['slug'])) ?>"><?= e($s['titulo'] ?? '') ?> <span aria-hidden="true">→</span></a></li>
                <?php endforeach; ?>
            </ul>
            <p class="voltar"><a href="./#servicos">← <?= e(t('serv_voltar')) ?></a></p>
        </section>
        <?php else: ?>
        <section class="pagina-topo" aria-labelledby="titulo-404">
            <h1 id="titulo-404"><?= e(t('serv_404_titulo')) ?></h1>
            <p class="lead"><?= e(t('serv_404_texto')) ?></p>
            <?php if ($servicos): ?>
            <ul class="links-servicos">
                <?php foreach ($servicos as $s): if (empty($s['slug'])) continue; ?>
                <li><a href="<?= e(url_servico((string) $s['slug'])) ?>"><?= e($s['titulo'] ?? '') ?> <span aria-hidden="true">→</span></a></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            <p class="voltar"><a href="./">← <?= e(t('voltar_inicio')) ?></a></p>
        </section>
        <?php endif; ?>
    </main>
<?php include __DIR__ . '/includes/rodape.php'; ?>
