<?php
// Usa $site, $wa e opcionalmente $frases (lista do topo da página inicial).
// Os botões fixos (WhatsApp, topo e assistente) ficam dentro do <footer> para pertencerem a uma região da página.
?>
    <footer class="rodape">
        <?php if (($pagina ?? '') !== 'inicio'): ?>
        <div class="rodape-cta">
            <div>
                <p class="rodape-cta-titulo"><?= e(t('rodape_cta_titulo')) ?></p>
                <p><?= e(t('rodape_cta_texto')) ?></p>
            </div>
            <a href="<?= e($wa) ?>" class="btn btn-sol" <?= LINK_EXTERNO ?> data-evento="whatsapp_contato"><?= e(t('rodape_cta_botao')) ?></a>
        </div>
        <?php endif; ?>

        <div class="rodape-grade">
            <div>
                <p class="marca-rodape"><strong><?= e($marca ?? $site['nome'] ?? '') ?></strong></p>
                <p><?= e(t('rodape_descricao')) ?></p>
            </div>
            <nav aria-labelledby="rodape-nav">
                <h2 class="rodape-titulo" id="rodape-nav"><?= e(t('rodape_col_navegacao')) ?></h2>
                <a href="./"><?= e(t('menu_inicio')) ?></a>
                <?php if (isset($temItens) && $temItens('projetos')): // como no menu do topo: só com projetos publicados ?>
                <a href="projetos.php"><?= e(t('menu_projetos')) ?></a>
                <?php endif; ?>
                <a href="politica-de-privacidade.php"><?= e(t('menu_politica')) ?></a>
            </nav>
            <div>
                <h2 class="rodape-titulo"><?= e(t('rodape_col_contato')) ?></h2>
                <a href="<?= e($wa) ?>" <?= LINK_EXTERNO ?> data-evento="whatsapp_contato">WhatsApp</a>
                <?php if (!empty($site['email'])): ?>
                <a href="mailto:<?= e($site['email']) ?>" data-evento="email_contato"><?= e($site['email']) ?></a>
                <?php endif; ?>
                <?php foreach ($perfis ?? [] as $perfil): ?>
                <a href="<?= e($perfil) ?>" <?= LINK_EXTERNO ?>>LinkedIn<span class="sr-only"> (abre em nova aba)</span></a>
                <?php endforeach; ?>
                <a href="https://github.com/seu-usuario" <?= LINK_EXTERNO ?>>GitHub<span class="sr-only"> (abre em nova aba)</span></a>
            </div>
        </div>

        <div class="rodape-base">
            <span>© <?= date('Y') ?> <?= e($site['nome'] ?? '') ?></span>
        </div>

    <a href="<?= e($wa) ?>" class="wa-flutuante" id="wa-flutuante" <?= LINK_EXTERNO ?> data-evento="whatsapp_flutuante" aria-label="<?= e(t('btn_flutuante')) ?>">
        <svg class="wa-icone" viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg> <span class="wa-texto"><?= e(t('btn_flutuante_curto')) ?></span>
    </a>

    <button type="button" class="topo-btn" id="topo-btn" aria-label="<?= e(t('btn_topo')) ?>" hidden><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 19V5M5.5 11.5 12 5l6.5 6.5"/></svg></button>

    <?php
    // Base do assistente de perguntas: só os campos usados pelo navegador
    $baseChat = array_map(
        fn($i) => array_intersect_key($i, array_flip(['id', 'rotulo', 'palavras', 'resposta', 'link', 'nome'])),
        array_filter($site['assistente'] ?? [], 'is_array')
    );
    $textosChat = [
        'botao' => t('chat_botao'), 'titulo' => t('chat_titulo'), 'saudacao' => t('chat_saudacao'),
        'padrao' => t('chat_padrao'), 'campo' => t('chat_campo'), 'enviar' => t('chat_enviar'), 'varias' => t('chat_varias'),
    ];
    ?>
    <div id="assistente" class="assistente" hidden
         data-itens="<?= e(json_encode(array_values($baseChat), JSON_UNESCAPED_UNICODE)) ?>"
         data-textos="<?= e(json_encode($textosChat, JSON_UNESCAPED_UNICODE)) ?>"
         data-email="<?= e($site['email'] ?? '') ?>"
         data-wa="<?= e($wa) ?>"
         data-politica="politica-de-privacidade.php"></div>
    </footer>
    <script src="assets/js/assistente.js?v=<?= (int) @filemtime(RAIZ . '/assets/js/assistente.js') ?>" defer></script>
    <script src="assets/js/main.js?v=<?= (int) @filemtime(RAIZ . '/assets/js/main.js') ?>" defer></script>
    <script src="assets/js/movimento.js?v=<?= (int) @filemtime(RAIZ . '/assets/js/movimento.js') ?>" defer></script>
</body>
</html>
