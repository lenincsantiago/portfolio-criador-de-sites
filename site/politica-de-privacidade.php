<?php
declare(strict_types=1);
require __DIR__ . '/includes/dados.php';

$site = carregar_dados();
$email = (string) ($site['email'] ?? '');
$nome = (string) ($site['nome'] ?? '');
$vars = ['nome' => $nome, 'email' => $email];
$frases = [];

// Seções numeradas do catálogo: título, texto e lista opcional
$atuais = textos_atuais();
$opcional = fn(string $chave): string => isset($atuais[$chave]) ? t($chave, $vars) : '';
$secoes = [];
for ($n = 1; $n <= 13; $n++) {
    $secoes[] = [
        'titulo' => t("pol_s{$n}_titulo"),
        'texto'  => t("pol_s{$n}_texto", $vars),
        'lista'  => array_values(array_filter(array_map('trim', explode("\n", $opcional("pol_s{$n}_lista"))))),
        'final'  => $opcional("pol_s{$n}_final"),
    ];
}

$titulo = t('pol_titulo') . ' | Sua Marca';
$descricao = descricao_meta(t('pol_descricao_seo', $vars));
$pagina = 'politica';
$migalhas = [[t('pol_titulo'), 'politica-de-privacidade.php']];
include __DIR__ . '/includes/cabecalho.php';
?>
    <main class="pagina" id="conteudo" tabindex="-1">
        <article class="pagina-topo texto-legal">
            <p class="rotulo"><?= e(t('pol_rotulo')) ?></p>
            <h1><?= e(t('pol_titulo')) ?></h1>
            <p class="ajuda"><?= e(t('pol_atual_rotulo')) ?> <?php $d = DateTime::createFromFormat('!d/m/Y', t('pol_data')); ?><?php if ($d): ?><time datetime="<?= e($d->format('Y-m-d')) ?>"><?= e(t('pol_data')) ?></time><?php else: ?><?= e(t('pol_data')) ?><?php endif; ?></p>
            <p class="pol-intro"><?= e(t('pol_intro')) ?></p>

            <?php foreach ($secoes as $s): ?>
                <h2><?= e($s['titulo']) ?></h2>
                <?php if ($s['texto'] !== ''): ?>
                <p><?= e($s['texto']) ?></p>
                <?php endif; ?>
                <?php if ($s['lista']): ?>
                <ul>
                    <?php foreach ($s['lista'] as $item): ?>
                    <li><?= e($item) ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
                <?php if ($s['final'] !== ''): ?>
                <p><?= e($s['final']) ?></p>
                <?php endif; ?>
            <?php endforeach; ?>

            <p class="voltar"><a href="./">← <?= e(t('voltar_inicio_pol')) ?></a></p>
        </article>
    </main>
<?php include __DIR__ . '/includes/rodape.php'; ?>
