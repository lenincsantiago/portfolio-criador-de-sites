<?php
// Lista de projetos com imagem 4:3, nome, segmento e botão "Ver site". Usa $projetos, $site e $nivelTitulo (2 ou 3).
$h = ($nivelTitulo ?? 3) === 2 ? 'h2' : 'h3';
?>
<ul class="projetos">
    <?php foreach ($projetos as $p):
        $nomeProjeto = (string) ($p['nome'] ?? '');
        $urlProjeto = validar_url($p['url'] ?? '') ?? '#';
        $temLink = $urlProjeto !== '#';
    ?>
    <li class="projeto">
        <?php if ($temLink): ?>
        <a href="<?= e($urlProjeto) ?>" <?= LINK_EXTERNO ?> class="projeto-imagem" data-evento="ver_projeto" tabindex="-1" aria-hidden="true">
        <?php else: ?>
        <div class="projeto-imagem">
        <?php endif; ?>
            <?php if (!empty($p['imagem'])): ?>
                <img src="<?= e($p['imagem']) ?>" alt="" loading="lazy" decoding="async" width="900" height="675">
            <?php else: ?>
                <span class="projeto-sem-imagem"><?= e(mb_strtoupper(mb_substr($nomeProjeto, 0, 1))) ?></span>
            <?php endif; ?>
        <?= $temLink ? '</a>' : '</div>' ?>
        <div class="projeto-corpo">
            <span class="tipo"><?= e($p['tipo'] ?? '') ?></span>
            <<?= $h ?>><?= e($nomeProjeto) ?></<?= $h ?>>
            <?php if (!empty($p['descricao'])): ?>
            <p><?= e($p['descricao']) ?></p>
            <?php endif; ?>
            <?php if ($temLink): ?>
            <a href="<?= e($urlProjeto) ?>" <?= LINK_EXTERNO ?> class="btn btn-linha projeto-botao" data-evento="ver_projeto"><?= e(t('btn_ver_site')) ?><span class="sr-only">: <?= e($nomeProjeto) ?> (abre em nova aba)</span> <span aria-hidden="true">→</span></a>
            <?php endif; ?>
        </div>
    </li>
    <?php endforeach; ?>
</ul>
