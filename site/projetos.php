<?php
declare(strict_types=1);
require __DIR__ . '/includes/dados.php';

$site = carregar_dados();
$projetos = array_values(array_filter((array) ($site['projetos'] ?? []), 'is_array'));
$frases = [];

$grafo = [];
if ($projetos) {
    $itens = [];
    foreach ($projetos as $i => $p) {
        $url = validar_url($p['url'] ?? '') ?? '#';
        $itens[] = array_filter([
            '@type'       => 'ListItem',
            'position'    => $i + 1,
            'name'        => (string) ($p['nome'] ?? ''),
            'description' => (string) ($p['descricao'] ?? ''),
            'url'         => $url !== '#' ? $url : null,
        ], fn($v) => $v !== null && $v !== '');
    }
    $grafo[] = ['@type' => 'ItemList', 'name' => t('sec_projetos'), 'itemListElement' => $itens];
}

$titulo = t('sec_projetos') . ' | Sua Marca';
$descricao = descricao_meta(t('proj_descricao_seo'), t('serv_descricao_extra'));
$pagina = 'projetos';
// Sem projetos a página fica quase vazia: o Google não a mostra até existir conteúdo
$indexar = (bool) $projetos;
$migalhas = [[t('menu_projetos'), 'projetos.php']];
include __DIR__ . '/includes/cabecalho.php';
?>
    <main class="pagina" id="conteudo" tabindex="-1">
        <section class="pagina-topo" aria-labelledby="titulo-pagina">
            <p class="rotulo"><?= e(t('sec_projetos')) ?></p>
            <h1 id="titulo-pagina"><?= e(t('proj_titulo')) ?></h1>
            <p class="lead"><?= e(t($projetos ? 'proj_intro' : 'proj_intro_vazio')) ?></p>
        </section>

        <section class="secao" aria-label="<?= e(t('sec_projetos')) ?>">
            <?php $nivelTitulo = 2; ?>
            <?php if (!$projetos): ?>
                <?php include __DIR__ . '/includes/projetos-vazio.php'; ?>
            <?php else: ?>
            <?php include __DIR__ . '/includes/projetos-lista.php'; ?>
            <?php endif; ?>
            <p class="voltar"><a href="./">← <?= e(t('voltar_inicio')) ?></a></p>
        </section>
    </main>
<?php include __DIR__ . '/includes/rodape.php'; ?>
