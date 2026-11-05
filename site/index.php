<?php
declare(strict_types=1);
require __DIR__ . '/includes/dados.php';
// O formulário leva um token com validade (12 h): o navegador pode guardar a página, mas confere com o
// servidor antes de reaproveitá-la, e caches compartilhados (de hospedagem ou provedor) não a guardam
header('Cache-Control: private, no-cache');

$site = carregar_dados();
$lista = static fn(string $k): array => array_values(array_filter((array) ($site[$k] ?? []), 'is_array'));
$servicos = $lista('servicos');
$segmentos = array_values(array_filter($lista('segmentos'), fn($g) => trim((string) ($g['titulo'] ?? '')) !== ''));
$processo = $lista('processo');
$depoimentos = $lista('depoimentos');
$faq = $lista('faq');
$projetos = $lista('projetos');
$frases = array_values(array_filter((array) ($site['frases'] ?? []), fn($f) => is_string($f) && $f !== ''));
$fraseFinal = $frases ? $frases[count($frases) - 1] : '';
$wa = wa_link($site, (string) ($site['msg_wa'] ?? ''));

$grafo = [];
if ($faq) {
    $grafo[] = [
        '@type'      => 'FAQPage',
        'mainEntity' => array_map(fn($f) => [
            '@type'          => 'Question',
            'name'           => (string) ($f['pergunta'] ?? ''),
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => (string) ($f['resposta'] ?? '')],
        ], $faq),
    ];
}

// Aviso do formulário: só valores da lista abaixo são aceitos (o texto vem do catálogo, nunca da URL)
$status = get_txt('contato', 10);
$msgContato = [
    'ok'      => ['sucesso', t('aviso_ok')],
    'erro'    => ['falha', t('aviso_erro')],
    'campos'  => ['falha', t('aviso_campos')],
    'limite'  => ['falha', t('aviso_limite')],
    'expirou' => ['falha', t('aviso_expirou')],
];
$aviso = $msgContato[$status] ?? null;

$titulo = 'Criação de sites para empresas em todo o Brasil | Sua Marca';
$descricao = descricao_meta((string) ($site['seo']['descricao'] ?? ''), t('serv_descricao_extra'));
$pagina = 'inicio';
$canonico = 'index.php';
include __DIR__ . '/includes/cabecalho.php';
?>
    <main id="conteudo" tabindex="-1">
        <section id="inicio" class="topo-hero" aria-labelledby="titulo-principal">
            <div class="hero-texto">
                <h1 id="titulo-principal"><?= e(t('hero_titulo')) ?></h1>
                <p class="hero-resumo"><?= e(t('hero_texto')) ?></p>
                <div class="botoes">
                    <a href="<?= e($wa) ?>" <?= LINK_EXTERNO ?> class="btn btn-sol" data-evento="whatsapp_topo"><?= e(t('btn_hero')) ?></a>
                    <?php // Sem projetos ainda, o segundo botão leva a "Como funciona" em vez de uma seção vazia ?>
                    <?php if ($projetos || !$processo): ?>
                    <a href="#projetos" class="btn btn-linha"><?= e(t('btn_ver_projetos')) ?></a>
                    <?php else: ?>
                    <a href="#processo" class="btn btn-linha"><?= e(t('btn_como_funciona')) ?></a>
                    <?php endif; ?>
                </div>
                <?php // Cada parte separada por "·" no painel vira um item da lista, com um ✓ na frente ?>
                <ul class="hero-nota"><?php foreach (array_filter(array_map('trim', explode('·', t('hero_nota')))) as $parte): ?><li><?= e($parte) ?></li><?php endforeach; ?></ul>
            </div>

            <?php // Exemplos de sites de negócios de vários ramos, num iPhone com a barra do Safari: cada exemplo é uma mini-página
                  // de verdade (pasta exemplos/) mostrada num iframe reduzido. Só o primeiro iframe tem src; os outros guardam o
                  // endereço em data-src e o main.js carrega quando for a vez deles. Sem JavaScript, fica só a padaria, parada. ?>
            <div class="celular-exemplo" role="group" aria-roledescription="carrossel" aria-label="Exemplos de sites para empresas e profissionais de vários ramos">
                <div class="ce-moldura">
                    <div class="ce-iphone">
                        <div class="ce-tela">
                            <div class="ce-status" aria-hidden="true">
                                <span class="ce-hora"><?= e(date('H:i')) ?></span>
                                <span class="ce-icones">
                                    <svg class="ce-i-sinal" viewBox="0 0 18 12" focusable="false"><rect x="0" y="8" width="3" height="4" rx="1"/><rect x="5" y="5.5" width="3" height="6.5" rx="1"/><rect x="10" y="3" width="3" height="9" rx="1"/><rect x="15" y="0" width="3" height="12" rx="1"/></svg>
                                    <svg class="ce-i-wifi" viewBox="0 0 16 12" focusable="false"><path d="M8 2.6c2.3 0 4.4.9 6 2.4l1.2-1.2A10.2 10.2 0 0 0 8 .9C5.2.9 2.6 2 .8 3.8L2 5a8.5 8.5 0 0 1 6-2.4Z"/><path d="M8 6.1c1.3 0 2.5.5 3.5 1.4l1.2-1.2A6.7 6.7 0 0 0 8 4.4c-1.8 0-3.5.7-4.7 1.9l1.2 1.2c1-.9 2.2-1.4 3.5-1.4Z"/><path d="M8 9.6 10 7.9a3.2 3.2 0 0 0-4 0Z"/></svg>
                                    <svg class="ce-i-bateria" viewBox="0 0 27 13" focusable="false"><rect x=".5" y=".5" width="23" height="12" rx="3.6" fill="none" stroke="currentColor" opacity=".4"/><rect x="2" y="2" width="20" height="9" rx="2.2"/><path d="M25 4.5v4c.8-.3 1.4-1.1 1.4-2s-.6-1.7-1.4-2Z" opacity=".45"/></svg>
                                </span>
                            </div>
                            <span class="ce-ilha" aria-hidden="true"></span>
                            <div class="ce-chrome" aria-hidden="true">
                                <span class="ce-campo">
                                    <svg class="ce-i-busca" viewBox="0 0 16 16" focusable="false"><path d="M2 4.2h6.2M2 8h4.4M2 11.8h6.2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M12.2 2.6l.7 1.6 1.6.7-1.6.7-.7 1.6-.7-1.6-1.6-.7 1.6-.7z" fill="currentColor"/></svg>
                                    <span class="ce-endereco" translate="no">fornadadourada.com</span>
                                    <svg class="ce-i-compartilhar" viewBox="0 0 16 16" focusable="false"><path d="M8 10V1.8M5.2 4.4L8 1.6l2.8 2.8" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M5.2 6.4H4.3a1 1 0 0 0-1 1v5.5a1 1 0 0 0 1 1h7.4a1 1 0 0 0 1-1V7.4a1 1 0 0 0-1-1h-.9" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                            </div>

                            <?php // Grade de abas (como no Chrome): o main.js monta os cartões e a usa só na troca de exemplo ?>
                            <div class="ce-abas" aria-hidden="true">
                                <div class="ce-abas-topo">
                                    <svg class="ce-i-lupa" viewBox="0 0 16 16" focusable="false"><circle cx="7" cy="7" r="4.6" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M10.6 10.6L14 14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                                    <span class="ce-seg">
                                        <span class="ce-seg-item"><svg viewBox="0 0 16 16" focusable="false"><path d="M2.4 8.6h11.2M4.6 8.6l1.1-4a1 1 0 0 1 1-.7h2.6a1 1 0 0 1 1 .7l1.1 4" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/><circle cx="5" cy="11.4" r="1.6" fill="none" stroke="currentColor" stroke-width="1.3"/><circle cx="11" cy="11.4" r="1.6" fill="none" stroke="currentColor" stroke-width="1.3"/></svg></span>
                                        <span class="ce-seg-item ce-seg-ativo"><span class="ce-abas-n ce-abas-n-grade">7</span></span>
                                        <span class="ce-seg-item"><svg viewBox="0 0 16 16" focusable="false"><rect x="2.6" y="2.6" width="4.4" height="4.4" rx="1" fill="none" stroke="currentColor" stroke-width="1.4"/><rect x="9" y="2.6" width="4.4" height="4.4" rx="1" fill="none" stroke="currentColor" stroke-width="1.4"/><rect x="2.6" y="9" width="4.4" height="4.4" rx="1" fill="none" stroke="currentColor" stroke-width="1.4"/><rect x="9" y="9" width="4.4" height="4.4" rx="1" fill="none" stroke="currentColor" stroke-width="1.4"/></svg></span>
                                    </span>
                                    <svg class="ce-i-mais" viewBox="0 0 16 16" focusable="false"><circle cx="3" cy="8" r="1.3" fill="currentColor"/><circle cx="8" cy="8" r="1.3" fill="currentColor"/><circle cx="13" cy="8" r="1.3" fill="currentColor"/></svg>
                                </div>
                                <div class="ce-abas-janela"><div class="ce-abas-lista"></div></div>
                                <div class="ce-abas-baixo">
                                    <span class="ce-abas-novo"><svg viewBox="0 0 16 16" focusable="false"><path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>
                                    <span class="ce-abas-ok">Concluído</span>
                                </div>
                            </div>

                            <div class="ce-site ce-padaria ativo" data-endereco="fornadadourada.com" role="img" aria-label="Exemplo de site da padaria Fornada Dourada no celular: horário das fornadas, aberto agora, produtos mais pedidos e botão para pedir pelo WhatsApp.">
                                <span class="ce-espera" aria-hidden="true"><b>Fornada Dourada</b><small>fornadadourada.com</small></span>
                                <iframe class="ce-quadro" title="Site de exemplo da padaria Fornada Dourada" loading="lazy" tabindex="-1" src="exemplos/fornada-dourada.html" data-src="exemplos/fornada-dourada.html"></iframe>
                            </div>

                            <div class="ce-site ce-advocacia" data-endereco="prumoadvocacia.com" role="img" aria-hidden="true" aria-label="Exemplo de site do escritório Prumo Advocacia no celular: frase de apresentação, áreas de atuação, atendimento presencial e online e botão para agendar consulta.">
                                <span class="ce-espera" aria-hidden="true"><b>Prumo Advocacia</b><small>prumoadvocacia.com</small></span>
                                <iframe class="ce-quadro" title="Site de exemplo do escritório Prumo Advocacia" loading="lazy" tabindex="-1" data-src="exemplos/prumo-advocacia.html"></iframe>
                            </div>

                            <div class="ce-site ce-clinica" data-endereco="clinicaalecrim.com" role="img" aria-hidden="true" aria-label="Exemplo de site da Clínica Alecrim no celular: agendamento online, especialidades, horários de atendimento e botão para marcar consulta.">
                                <span class="ce-espera" aria-hidden="true"><b>Clínica Alecrim</b><small>clinicaalecrim.com</small></span>
                                <iframe class="ce-quadro" title="Site de exemplo da Clínica Alecrim" loading="lazy" tabindex="-1" data-src="exemplos/clinica-alecrim.html"></iframe>
                            </div>

                            <div class="ce-site ce-barbearia" data-endereco="dombigode.com" role="img" aria-hidden="true" aria-label="Exemplo de site da Barbearia Dom Bigode no celular: horário, tabela de preços dos serviços e botão para agendar horário.">
                                <span class="ce-espera" aria-hidden="true"><b>Dom Bigode</b><small>dombigode.com</small></span>
                                <iframe class="ce-quadro" title="Site de exemplo da Barbearia Dom Bigode" loading="lazy" tabindex="-1" data-src="exemplos/dom-bigode.html"></iframe>
                            </div>

                            <div class="ce-site ce-pet" data-endereco="lambeijopet.com" role="img" aria-hidden="true" aria-label="Exemplo de site do pet shop Lambeijo no celular: banho, tosa, veterinário, leva e traz grátis e botão para agendar banho.">
                                <span class="ce-espera" aria-hidden="true"><b>Lambeijo Pet</b><small>lambeijopet.com</small></span>
                                <iframe class="ce-quadro" title="Site de exemplo do pet shop Lambeijo" loading="lazy" tabindex="-1" data-src="exemplos/lambeijo-pet.html"></iframe>
                            </div>

                            <div class="ce-site ce-salao" data-endereco="cachoecor.com" role="img" aria-hidden="true" aria-label="Exemplo de site do salão Cacho &amp; Cor no celular: serviços com duração, escolha do dia na agenda e botão para escolher horário.">
                                <span class="ce-espera" aria-hidden="true"><b>Cacho &amp; Cor</b><small>cachoecor.com</small></span>
                                <iframe class="ce-quadro" title="Site de exemplo do salão Cacho &amp; Cor" loading="lazy" tabindex="-1" data-src="exemplos/cacho-e-cor.html"></iframe>
                            </div>

                            <div class="ce-site ce-oficina" data-endereco="chavemestra.com" role="img" aria-hidden="true" aria-label="Exemplo de site da oficina Chave Mestra no celular: orçamento antes do conserto, serviços, horário e botão para pedir orçamento.">
                                <span class="ce-espera" aria-hidden="true"><b>Chave Mestra</b><small>chavemestra.com</small></span>
                                <iframe class="ce-quadro" title="Site de exemplo da oficina Chave Mestra" loading="lazy" tabindex="-1" data-src="exemplos/chave-mestra.html"></iframe>
                            </div>

                            <div class="ce-rodape" aria-hidden="true">
                                <svg viewBox="0 0 20 20" focusable="false"><path d="M16 10H4.5M9.5 4.6L4.2 10l5.3 5.4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <svg class="ce-i-fraco" viewBox="0 0 20 20" focusable="false"><path d="M4 10h11.5M10.5 4.6L15.8 10l-5.3 5.4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <span class="ce-rodape-novo"><svg viewBox="0 0 16 16" focusable="false"><path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>
                                <span class="ce-abas-n ce-abas-n-barra">7</span>
                                <svg viewBox="0 0 20 20" focusable="false"><circle cx="4" cy="10" r="1.5" fill="currentColor"/><circle cx="10" cy="10" r="1.5" fill="currentColor"/><circle cx="16" cy="10" r="1.5" fill="currentColor"/></svg>
                            </div>

                            <span class="ce-inicio" aria-hidden="true"></span>
                        </div>
                    </div>
                </div>
                <div class="ce-pontos" role="group" aria-label="Escolher exemplo de site">
                    <button type="button" class="ce-ponto" aria-label="Padaria"></button>
                    <button type="button" class="ce-ponto" aria-label="Advocacia"></button>
                    <button type="button" class="ce-ponto" aria-label="Clínica"></button>
                    <button type="button" class="ce-ponto" aria-label="Barbearia"></button>
                    <button type="button" class="ce-ponto" aria-label="Pet shop"></button>
                    <button type="button" class="ce-ponto" aria-label="Salão de beleza"></button>
                    <button type="button" class="ce-ponto" aria-label="Oficina mecânica"></button>
                    <?php // O botão de pausa já vem no HTML para aparecer junto com as bolinhas; o main.js só liga o clique ?>
                    <button type="button" class="ce-pausa" aria-pressed="false" aria-label="Pausar a troca dos exemplos"><svg class="i-pausar" viewBox="0 0 16 16" aria-hidden="true"><rect x="3" y="2" width="3.5" height="12"/><rect x="9.5" y="2" width="3.5" height="12"/></svg><svg class="i-tocar" viewBox="0 0 16 16" aria-hidden="true"><rect x="4" y="2" width="3" height="12"/><rect x="7" y="4" width="3" height="8"/><rect x="10" y="6" width="2.5" height="4"/></svg></button>
                </div>
            </div>
        </section>

        <?php // O problema do cliente: a frase de destaque é o título de verdade ?>
        <section class="secao reveal" aria-labelledby="t-problema">
            <h2 class="destaque" id="t-problema"><?= e(t('dor_titulo')) ?></h2>
            <p class="lead"><?= e(t('dor_texto')) ?></p>
            <div class="comparativo">
                <?php foreach (['ruim', 'bom'] as $lado): ?>
                <div class="comp-coluna comp-<?= $lado ?>">
                    <h3><?= e(t('dor_' . $lado . '_titulo')) ?></h3>
                    <ul>
                        <?php foreach (array_filter(array_map('trim', explode('·', t('dor_' . $lado . '_itens')))) as $item): ?>
                        <li><svg aria-hidden="true" viewBox="0 0 20 20" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?= $lado === 'bom' ? '<path d="M4 10.5l4 4 8-9"/>' : '<path d="M5 5l10 10M15 5L5 15"/>' ?></svg><span><?= e($item) ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <?php // Quem faz o site: foto e apresentação ?>
        <section id="sobre" class="secao reveal" aria-labelledby="t-sobre">
            <h2 id="t-sobre"><?= e(t('sobre_titulo')) ?></h2>
            <div class="sobre-autor">
                <figure class="autor-foto">
                    <img src="assets/img/perfil-480.webp" srcset="assets/img/perfil-160.webp 160w, assets/img/perfil-480.webp 480w" sizes="(min-width: 720px) 260px, 70vw" alt="<?= e(($site['nome'] ?? '') . ', ' . mb_strtolower($site['cargo'] ?? '') . ' em ' . ($site['seo']['cidade'] ?? '')) ?>" width="260" height="260" loading="lazy" decoding="async">
                    <figcaption><strong class="autor-cargo"><?= e($site['nome'] ?? '') ?></strong><span class="autor-funcao"><?= e($site['cargo'] ?? '') ?></span></figcaption>
                </figure>
                <div class="sobre-texto">
                    <p class="lead" data-sobre><?= e($site['sobre'] ?? '') ?></p>
                    <ul class="sobre-promessas">
                        <?php foreach (array_filter(array_map('trim', explode('·', t('sobre_carta')))) as $promessa):
                            [$destaque, $frase] = array_pad(explode(':', $promessa, 2), -2, ''); ?>
                        <li><?php if (trim($destaque) !== ''): ?><strong><?= e(trim($destaque)) ?>:</strong><?php endif; ?><?= e(trim($frase)) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <p class="sobre-cta botoes">
                        <a href="<?= e($wa) ?>" <?= LINK_EXTERNO ?> class="btn btn-sol" data-evento="whatsapp_contato"><?= e(t('btn_hero')) ?></a>
                        <?php if ($projetos): ?><a href="#projetos" class="btn btn-linha"><?= e(t('btn_ver_projetos')) ?></a><?php endif; ?>
                    </p>
                </div>
            </div>
        </section>

        <?php if ($segmentos): ?>
        <section id="para-quem" class="secao reveal" aria-labelledby="t-para-quem">
            <h2 id="t-para-quem"><?= e(t('sec_para_quem')) ?></h2>
            <p class="lead"><?= e(t('seg_intro')) ?></p>
            <?php // Lista simples para quem está sem JavaScript; o main.js a esconde e monta o seletor a partir dela ?>
            <div class="seg" data-seg data-grupo="<?= e(t('seg_grupo')) ?>" data-botao="<?= e(t('seg_botao')) ?>">
                <ul class="seg-lista">
                    <?php foreach ($segmentos as $g):
                        $ramo = mb_strtolower(trim((string) ($g['titulo'] ?? ''))); ?>
                    <li data-ramo="<?= e($ramo) ?>" data-wa="<?= e(wa_link($site, t('seg_msg', ['ramo' => $ramo]))) ?>">
                        <strong><?= e((string) ($g['titulo'] ?? '')) ?></strong>
                        <span><?= e((string) ($g['texto'] ?? '')) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>
        <?php endif; ?>

        <section id="servicos" class="secao reveal" aria-labelledby="t-servicos">
            <h2 id="t-servicos"><?= e(t('sec_servicos')) ?></h2>
            <ol class="linha-tempo">
                <?php foreach ($servicos as $s):
                    $tituloServico = (string) ($s['titulo'] ?? '');
                ?>
                <li>
                    <h3><?= e($tituloServico) ?></h3>
                    <div class="servico-corpo">
                        <p><?= e($s['texto'] ?? '') ?></p>
                        <?php if (!empty($s['slug'])): ?>
                        <a class="mais-servico" href="<?= e(url_servico((string) $s['slug'])) ?>"><?= e(t('btn_detalhes_servico')) ?><span class="sr-only">: <?= e($tituloServico) ?></span></a>
                        <?php endif; ?>
                    </div>
                </li>
                <?php endforeach; ?>
            </ol>
        </section>

        <section id="contratar" class="secao reveal" aria-labelledby="t-contratar">
            <h2 id="t-contratar"><?= e(t('sec_contratar')) ?></h2>
            <p class="lead"><?= e(t('contratar_intro')) ?></p>
            <div class="modelos">
                <div class="modelo">
                    <p class="modelo-nome"><?= e(t('mod1_nome')) ?></p>
                    <p><?= e(t('mod1_texto')) ?></p>
                </div>
                <div class="modelo">
                    <p class="modelo-nome"><?= e(t('mod2_nome')) ?></p>
                    <p><?= e(t('mod2_texto')) ?></p>
                </div>
            </div>
            <div class="inclui">
                <h3><?= e(t('inclui_titulo')) ?></h3>
                <ul class="incluso">
                    <?php foreach (array_filter(array_map('trim', explode("\n", t('inclui_lista')))) as $item): ?>
                    <li><?= e($item) ?></li>
                    <?php endforeach; ?>
                </ul>
                <p class="regras"><?= e(t('regras_texto')) ?></p>
            </div>
        </section>

        <?php if ($processo): ?>
        <section id="processo" class="secao reveal" aria-labelledby="t-processo">
            <h2 id="t-processo"><?= e(t('sec_processo')) ?></h2>
            <ol class="etapas">
                <?php foreach ($processo as $i => $etapa): ?>
                <li class="etapa">
                    <span class="etapa-num" aria-hidden="true"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                    <h3><?= e($etapa['titulo'] ?? '') ?></h3>
                    <p><?= e($etapa['texto'] ?? '') ?></p>
                </li>
                <?php endforeach; ?>
            </ol>
        </section>
        <?php endif; ?>

        <section id="projetos" class="secao reveal" aria-labelledby="t-projetos">
            <h2 id="t-projetos"><?= e(t('sec_projetos')) ?></h2>
            <?php $nivelTitulo = 3; ?>
            <?php if ($projetos): ?>
            <?php // Na home, só os 3 primeiros; a lista completa fica em projetos.php
            $todosProjetos = $projetos;
            $projetos = array_slice($projetos, 0, 3);
            include __DIR__ . '/includes/projetos-lista.php';
            $projetos = $todosProjetos; ?>
            <?php if (count($projetos) > 1): ?>
                <p class="projetos-todos"><a href="projetos.php" class="btn btn-linha"><?= e(t('btn_ver_todos')) ?> →</a></p>
            <?php endif; ?>
            <?php else: ?>
                <?php include __DIR__ . '/includes/projetos-vazio.php'; ?>
            <?php endif; ?>
        </section>

        <?php if ($depoimentos): ?>
        <section id="depoimentos" class="secao reveal" aria-labelledby="t-depoimentos">
            <h2 id="t-depoimentos"><?= e(t('sec_depoimentos')) ?></h2>
            <div class="depoimentos">
                <?php foreach ($depoimentos as $d): ?>
                <figure class="depoimento">
                    <blockquote>“<?= e($d['texto'] ?? '') ?>”</blockquote>
                    <figcaption><strong><?= e($d['nome'] ?? '') ?></strong><?php if (!empty($d['negocio'])): ?> · <?= e($d['negocio']) ?><?php endif; ?></figcaption>
                </figure>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($faq): ?>
        <section id="faq" class="secao reveal" aria-labelledby="t-faq">
            <h2 id="t-faq"><?= e(t('sec_faq')) ?></h2>
            <div class="faq">
                <?php foreach ($faq as $f): ?>
                <details data-animar>
                    <summary><?= e($f['pergunta'] ?? '') ?></summary>
                    <div class="faq-corpo" data-corpo><p><?= e($f['resposta'] ?? '') ?></p></div>
                </details>
                <?php endforeach; ?>
            </div>
            <?php // Abre o assistente; nasce escondido e o assistente.js mostra (no celular é o único jeito de abrir) ?>
            <p class="faq-ajuda"><button type="button" class="link-assistente" data-abrir-assistente aria-controls="chat-painel" aria-expanded="false" hidden><?= e(t('chat_link_faq')) ?></button></p>
        </section>
        <?php endif; ?>

        <?php // O terminal saiu do topo (lá está o celular de exemplo) e fica perto do contato, como um extra para explorar ?>
        <section id="conheca" class="secao secao-terminal reveal" aria-labelledby="t-terminal">
            <div class="terminal-texto">
                <h2 id="t-terminal"><?= e(t('terminal_titulo')) ?></h2>
                <p class="lead"><?= e(t('terminal_texto')) ?></p>
            </div>

            <div class="terminal" id="terminal" translate="no"
                 data-nome="<?= e($site['nome'] ?? '') ?>"
                 data-wa="<?= e($wa) ?>"
                 data-email="<?= e($site['email'] ?? '') ?>">
                <div class="term-barra" aria-hidden="true"><span class="term-janela"><i></i><i></i><i></i></span><span>Terminal do Seu Nome</span><span>digite ajuda</span></div>
                <div class="term-corpo" id="term-corpo" aria-live="polite">
                                        <?php // "assistente do site" vira link que abre o assistente (sem JavaScript, leva às perguntas frequentes) ?>
                    <p class="t-linha destaque"><?= str_replace(e('assistente do site'), '<a href="#faq" class="t-assistente" data-abrir-assistente aria-controls="chat-painel" aria-expanded="false">assistente do site</a>', e(t('terminal_boasvindas'))) ?></p>
                </div>
                <?php // Perguntas prontas escondidas: não aparecem como botões, mas alimentam a demonstração que digita sozinha ?>
                <div class="term-sugestoes" role="group" aria-label="Perguntas prontas" hidden>
                    <?php foreach (array_filter(array_map('trim', explode('·', t('terminal_perguntas')))) as $par):
                        [$perguntaPronta, $comandoPronto] = array_pad(array_map('trim', explode('|', $par, 2)), 2, ''); ?>
                    <button type="button" class="chip" data-cmd="<?= e($comandoPronto !== '' ? $comandoPronto : $perguntaPronta) ?>" data-eco="<?= e($perguntaPronta) ?>"><?= e($perguntaPronta) ?></button>
                    <?php endforeach; ?>
                </div>
                <form class="term-linha" id="term-form" autocomplete="off">
                    <label for="term-input" class="sr-only">Digite um comando</label>
                    <span aria-hidden="true">$</span>
                    <input id="term-input" type="text" name="comando" placeholder="escreva sua pergunta…" spellcheck="false" autocapitalize="off" enterkeyhint="send" maxlength="60">
                    <button type="submit" class="term-enviar" aria-label="Executar comando"><span aria-hidden="true">↵</span></button>
                </form>
            </div>
        </section>

        <section id="contato" class="secao contato reveal" aria-labelledby="t-contato">
            <p class="rotulo"><?= e(t('sec_contato')) ?></p>
            <h2 id="t-contato"><?= e(t('contato_titulo')) ?></h2>
            <p class="lead"><?= e(t('contato_texto')) ?></p>
            <p class="selo"><?= e(t('selo')) ?></p>
            <?php // Quem responde: uma pessoa de verdade, com prazo (a foto é decorativa: o nome está no texto) ?>
            <div class="quem-responde">
                <div>
                    <p class="quem-titulo"><?= e(t('contato_quem_titulo')) ?></p>
                    <p><?= e(t('contato_quem_texto')) ?></p>
                </div>
            </div>

            <?php if ($aviso): ?>
                <p class="aviso aviso-<?= e($aviso[0]) ?>" id="aviso-contato" role="<?= $aviso[0] === 'sucesso' ? 'status' : 'alert' ?>"><?php if ($aviso[0] === 'sucesso'): ?><span class="aviso-icone" aria-hidden="true">✓</span> <?php endif; ?><?= e($aviso[1]) ?></p>
            <?php endif; ?>

            <form class="form-contato" method="post" action="contato.php" data-evento="formulario_envio" data-enviando="<?= e(t('btn_enviando')) ?>"<?= $aviso ? ' aria-describedby="aviso-contato"' : '' ?>>
                <input type="hidden" name="csrf" value="<?= e(token_formulario()) ?>">
                <input type="hidden" name="t" value="<?= time() ?>">
                <div class="campo-mel" aria-hidden="true">
                    <label>Deixe em branco <input type="text" name="confirma_humano" tabindex="-1" autocomplete="off"></label>
                </div>
                <div class="campo">
                    <label for="f-nome"><?= e(t('form_nome')) ?></label>
                    <input type="text" id="f-nome" name="nome" maxlength="100" required autocomplete="name" autocapitalize="words" enterkeyhint="next">
                </div>
                <div class="campo">
                    <label for="f-contato"><?= e(t('form_contato')) ?></label>
                    <input type="text" id="f-contato" name="contato" maxlength="120" required autocomplete="on" autocapitalize="off" spellcheck="false" enterkeyhint="next" placeholder="<?= e(t('form_contato_exemplo')) ?>">
                </div>
                <div class="campo">
                    <label for="f-ramo"><?= e(t('form_ramo')) ?></label>
                    <input type="text" id="f-ramo" name="ramo" maxlength="80" autocomplete="off" enterkeyhint="next" placeholder="<?= e(t('form_ramo_exemplo')) ?>">
                </div>
                <fieldset class="campo campo-opcoes">
                    <legend><?= e(t('form_tem_site')) ?></legend>
                    <label><input type="radio" name="tem_site" value="sim"> <?= e(t('form_sim')) ?></label>
                    <label><input type="radio" name="tem_site" value="nao"> <?= e(t('form_nao')) ?></label>
                </fieldset>
                <div class="campo">
                    <label for="f-mensagem"><?= e(t('form_mensagem')) ?></label>
                    <textarea id="f-mensagem" name="mensagem" rows="4" maxlength="1000" required></textarea>
                </div>
                <button type="submit" class="btn btn-sol"><?= e(t('btn_enviar')) ?></button>
            </form>
            <p class="privacidade-form"><?= e(t('form_privacidade')) ?> <a href="politica-de-privacidade.php"><?= e(t('form_link_politica')) ?></a>.</p>

            <div class="botoes">
                <a href="<?= e($wa) ?>" <?= LINK_EXTERNO ?> class="btn btn-linha" data-evento="whatsapp_contato"><?= e(t('btn_whatsapp')) ?></a>
                <?php if (!empty($site['email'])): ?>
                <a href="mailto:<?= e($site['email']) ?>" class="btn btn-linha" data-evento="email_contato"><?= e(t('btn_email')) ?></a>
                <?php endif; ?>
            </div>
        </section>
    </main>
<?php include __DIR__ . '/includes/rodape.php'; ?>
