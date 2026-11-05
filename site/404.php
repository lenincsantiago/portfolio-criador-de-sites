<?php
declare(strict_types=1);
require __DIR__ . '/includes/dados.php';

// Página mostrada pelo servidor para qualquer endereço que não existe (ErrorDocument no .htaccess)
http_response_code(404);

$site = carregar_dados();
$servicos = array_values(array_filter((array) ($site['servicos'] ?? []), 'is_array'));
$frases = [];

$titulo = 'Página não encontrada | Sua Marca';
$descricao = descricao_meta((string) ($site['seo']['descricao'] ?? ''), t('serv_descricao_extra'));
$pagina = '404';
$indexar = false;
$canonico = 'index.php';
$baseRaiz = true;
include __DIR__ . '/includes/cabecalho.php';
?>
    <main class="pagina" id="conteudo" tabindex="-1">
        <section class="pagina-topo" aria-labelledby="titulo-404">
            <p class="rotulo">Erro 404</p>
            <h1 id="titulo-404">Página não encontrada</h1>
            <p class="lead">Este endereço não existe ou mudou de lugar. Veja os serviços abaixo ou volte para a página inicial.</p>
            <?php if ($servicos): ?>
            <ul class="links-servicos">
                <?php foreach ($servicos as $s): if (empty($s['slug'])) continue; ?>
                <li><a href="<?= e(url_servico((string) $s['slug'])) ?>"><?= e($s['titulo'] ?? '') ?> <span aria-hidden="true">→</span></a></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            <p class="voltar"><a href="./">← <?= e(t('voltar_inicio')) ?></a></p>
        </section>
    </main>
<?php include __DIR__ . '/includes/rodape.php'; ?>
