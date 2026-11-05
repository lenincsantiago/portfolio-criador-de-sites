<?php
// Mostrado quando ainda não há projetos cadastrados. Usa $wa e $nivelTitulo (2 ou 3).
$h = ($nivelTitulo ?? 3) === 2 ? 'h2' : 'h3';
?>
<div class="vazio">
    <div class="vazio-texto">
        <span class="vazio-tag"><span aria-hidden="true">● </span><?= e(t('proj_vazio_tag')) ?></span>
        <<?= $h ?>><?= e(t('proj_vazio_titulo')) ?></<?= $h ?>>
        <p><?= e(t('proj_vazio_texto')) ?></p>
        <a href="<?= e($wa) ?>" class="btn btn-sol" <?= LINK_EXTERNO ?> data-evento="whatsapp_contato"><?= e(t('proj_vazio_botao')) ?></a>
    </div>
</div>
