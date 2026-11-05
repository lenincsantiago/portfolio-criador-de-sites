(function () {
    'use strict';
    // Assistente de perguntas e respostas, sem IA e sem enviar dados para fora do navegador.
    // Compara a pergunta com as palavras de cada assunto e responde até 4 dúvidas de uma vez.

    const LIMITE_RESPOSTAS = 4;
    const LIMIAR_PALAVRAS = 2.2;
    const RESPOSTA_PADRAO = 'Essa eu não sei responder por aqui. Mande a sua dúvida para {email} ou pelo WhatsApp, e eu respondo pessoalmente.';
    const AVISO_VARIAS = 'Respondi as principais dúvidas. Se faltou alguma, pergunte de novo, uma de cada vez.';

    function normalizar(texto) {
        return String(texto || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[̀-ͯ]/g, '')
            // "e-mail" e "e mail" viram "email", para casar com as palavras cadastradas
            .replace(/\be[- ]mail/g, 'email')
            .replace(/[^a-z0-9]+/g, ' ')
            .trim();
    }

    function preparar(itens) {
        return (itens || [])
            .filter(i => i && i.palavras)
            .map(item => ({
                item,
                social: String(item.id || '').indexOf('social_') === 0,
                // O texto do botão e o título do assunto também contam como palavras,
                // assim a própria pergunta do botão sempre encontra a sua resposta
                palavras: [...String(item.palavras).split(','), item.rotulo || '', item.nome || '']
                    .map(normalizar).filter(p => p.length >= 2),
            }))
            .filter(e => e.palavras.length);
    }

    // Pontua um assunto: frase exata vale mais que palavra solta; palavras no plural ou com variação também contam
    function pontuar(mensagem, entrada) {
        const texto = normalizar(mensagem);
        const frase = ' ' + texto + ' ';
        const tokens = texto.split(' ').filter(Boolean);
        let pontos = 0;
        entrada.palavras.forEach(palavra => {
            const partes = palavra.split(' ').length;
            if (frase.indexOf(' ' + palavra + ' ') !== -1) {
                pontos += partes * 2;
            } else if (partes === 1 && palavra.length >= 4) {
                const variacao = tokens.some(t => t.indexOf(palavra) === 0 && t.length - palavra.length <= 3);
                if (variacao) pontos += 1;
            }
        });
        return pontos;
    }

    // Palavras que não ajudam a diferenciar uma pergunta de outra
    const PARADAS = new Set(('a o as os de da do das dos e em no na nos nas um uma uns umas que pra para por com se eu voce voces vc '
        + 'meu minha meus minhas seu sua seus suas tem ter como qual quais quanto quantos quantas posso pode podem faz fazem '
        + 'vai vou ao aos isso isto esse essa este esta mais muito ja sim nao aqui ai so tambem sobre depois quando onde sou '
        + 'estou ser seria entao ou mas mesmo algum alguma outro outra ola oi tudo bem').split(' '));
    function conteudo(texto) {
        return normalizar(texto).split(' ').filter(t => t.length >= 3 && !PARADAS.has(t));
    }

    // Segunda tentativa, só quando nenhum assunto casou pelas frases cadastradas.
    // Cada palavra da pergunta pesa mais quando aparece em poucos assuntos (peso IDF).
    function pontuarPorPalavras(mensagem, entradas) {
        const normais = entradas.map(e => new Set(conteudo(e.item.palavras + ' ' + (e.item.nome || ''))));
        const df = {};
        normais.forEach(conj => conj.forEach(t => { df[t] = (df[t] || 0) + 1; }));
        const N = entradas.length || 1;
        const tokens = conteudo(mensagem);
        return entradas.map((e, i) => {
            let pontos = 0;
            let casou = 0;
            tokens.forEach(t => {
                const achou = [...normais[i]].some(k => k === t || (t.length >= 5 && k.indexOf(t) === 0) || (k.length >= 5 && t.indexOf(k) === 0));
                if (achou && df[t]) {
                    pontos += Math.log(N / df[t]) + 0.2;
                    casou++;
                }
            });
            // Exige pelo menos duas palavras diferentes do mesmo assunto, para não responder por acaso
            return { e, pontos, casou };
        }).filter(r => r.casou >= 2 && r.pontos >= LIMIAR_PALAVRAS).sort((a, b) => b.pontos - a.pontos);
    }

    function textoFinal(texto, email) {
        return String(texto || '').split('{email}').join(email || '');
    }

    function respostaDe(item, email) {
        return {
            tipo: 'resposta',
            texto: textoFinal(item.resposta, email),
            link: item.link || '',
            rotulo: item.rotulo || '',
            id: item.id,
        };
    }

    // Retorna uma lista de respostas: { tipo: 'resposta' | 'padrao' | 'aviso', texto, link, rotulo, id }
    function responder(mensagem, itens, email, textos) {
        const padrao = (textos && textos.padrao) || RESPOSTA_PADRAO;
        const entradas = preparar(itens);
        const normal = normalizar(mensagem);
        if (!normal) {
            return [{ tipo: 'padrao', texto: textoFinal(padrao, email) }];
        }

        const principais = entradas
            .filter(e => !e.social)
            .map(e => ({ e, pontos: pontuar(mensagem, e) }))
            .filter(r => r.pontos > 0)
            .sort((a, b) => b.pontos - a.pontos);

        // Só responde assuntos que chegam perto do melhor: evita trazer respostas sem relação
        const filtrar = (lista) => lista.length ? lista.filter(r => r.pontos >= lista[0].pontos * 0.5) : lista;

        let escolhidas = filtrar(principais);
        if (!principais.length) {
            // Saudações e agradecimentos só são respondidos quando não há outra dúvida
            escolhidas = entradas
                .filter(e => e.social)
                .map(e => ({ e, pontos: pontuar(mensagem, e) }))
                .filter(r => r.pontos > 0)
                .sort((a, b) => b.pontos - a.pontos);
        }
        if (!escolhidas.length) {
            // Última tentativa: palavras da pergunta que aparecem nos assuntos
            escolhidas = filtrar(pontuarPorPalavras(mensagem, entradas.filter(e => !e.social)));
        }

        if (!escolhidas.length) {
            return [{ tipo: 'padrao', texto: textoFinal(padrao, email) }];
        }

        const respostas = escolhidas.slice(0, LIMITE_RESPOSTAS).map(r => respostaDe(r.e.item, email));
        if (escolhidas.length > LIMITE_RESPOSTAS) {
            respostas.push({ tipo: 'aviso', texto: (textos && textos.varias) || AVISO_VARIAS });
        }
        return respostas;
    }

    // Botão rápido: responde sempre o assunto do próprio botão, sem depender da busca por palavras
    function responderBotao(id, itens, email) {
        const item = (itens || []).find(i => i && i.id === id);
        return item ? [respostaDe(item, email)] : [];
    }

    // ----- Interface -----
    function montar(raiz) {
        let itens = [];
        try {
            itens = JSON.parse(raiz.dataset.itens || '[]');
        } catch (e) {
            itens = [];
        }
        let textos = {};
        try {
            textos = JSON.parse(raiz.dataset.textos || '{}') || {};
        } catch (e) {
            textos = {};
        }
        const txt = (chave, padrao) => (typeof textos[chave] === 'string' && textos[chave]) ? textos[chave] : padrao;
        const email = raiz.dataset.email || '';
        const wa = raiz.dataset.wa || '';
        const politica = raiz.dataset.politica || 'politica-de-privacidade.php';
        const semMovimento = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        // Abaixo de 720px o painel cobre a página: vira janela modal (o Tab não sai dele)
        const telaPequena = window.matchMedia ? window.matchMedia('(max-width: 719px)') : { matches: false };
        // Toque: não focar o campo ao abrir, senão o teclado do celular sobe e cobre o painel
        const toque = window.matchMedia ? window.matchMedia('(pointer: coarse)') : { matches: false };

        const el = (tag, classe, texto) => {
            const n = document.createElement(tag);
            if (classe) n.className = classe;
            if (texto !== undefined) n.textContent = texto;
            return n;
        };
        const linkSeguro = (href) => /^(https?:\/\/|[a-z0-9_-]+\.php|#)/i.test(href || '');
        const rotuloLink = (href) => {
            if (href.indexOf('politica') !== -1) return 'Abrir a Política de Privacidade';
            if (href.charAt(0) === '#') return 'Ver na página';
            return 'Abrir página';
        };

        const botao = el('button', 'chat-abrir');
        botao.type = 'button';
        botao.setAttribute('aria-expanded', 'false');
        botao.setAttribute('aria-controls', 'chat-painel');
        const icone = el('span', 'chat-abrir-icone', '?');
        icone.setAttribute('aria-hidden', 'true');
        botao.appendChild(icone);
        botao.appendChild(el('span', 'chat-abrir-texto', txt('botao', 'Dúvidas')));

        const painel = el('section', 'chat-painel');
        painel.id = 'chat-painel';
        painel.setAttribute('role', 'dialog');
        painel.setAttribute('aria-labelledby', 'chat-titulo');
        painel.hidden = true;

        const cabeca = el('header', 'chat-cabeca');
        const titulo = el('strong', '', txt('titulo', 'Assistente do site'));
        titulo.id = 'chat-titulo';
        cabeca.appendChild(titulo);
        const fechar = el('button', 'chat-fechar', '×');
        fechar.type = 'button';
        fechar.setAttribute('aria-label', 'Fechar assistente');
        cabeca.appendChild(fechar);

        const msgs = el('div', 'chat-msgs');
        msgs.setAttribute('aria-live', 'polite');

        const form = el('form', 'chat-form');
        const campo = el('input', '');
        campo.type = 'text';
        campo.id = 'chat-campo';
        campo.name = 'duvida';
        campo.placeholder = txt('campo', 'Digite sua dúvida…');
        campo.maxLength = 200;
        // Nome fixo para leitores de tela: o texto de exemplo (com reticências) muda pelo painel e é lido mal
        campo.setAttribute('aria-label', 'Sua dúvida');
        campo.autocomplete = 'off';
        campo.setAttribute('enterkeyhint', 'send');
        const enviar = el('button', 'btn btn-sol chat-enviar', txt('enviar', 'Enviar'));
        enviar.type = 'submit';
        form.appendChild(campo);
        form.appendChild(enviar);

        painel.appendChild(cabeca);
        painel.appendChild(msgs);
        painel.appendChild(form);
        raiz.appendChild(botao);
        raiz.appendChild(painel);
        raiz.hidden = false;

        const rolar = () => { msgs.scrollTop = msgs.scrollHeight; };
        const bolha = (tipo, texto) => {
            const b = el('div', 'chat-bolha chat-' + tipo);
            b.appendChild(el('p', '', texto));
            msgs.appendChild(b);
            rolar();
            return b;
        };
        const linkBotao = (bolhaRef, href, rotulo, nova) => {
            const a = el('a', 'chat-link', rotulo);
            a.href = href;
            if (nova) { a.target = '_blank'; a.rel = 'noopener noreferrer'; }
            bolhaRef.appendChild(a);
        };

        let saudado = false;
        const saudar = () => {
            if (saudado) return;
            saudado = true;
            bolha('bot', txt('saudacao', 'Olá! Escreva a sua dúvida abaixo.'));
        };

        const mostrarRespostas = (pergunta, respostas) => {
            respostas.forEach(r => {
                if (r.tipo === 'padrao') {
                    const b = bolha('bot', r.texto);
                    // E-mail abre o programa de e-mail direto (nova aba ficaria em branco); sem e-mail, sem botão
                    if (email) linkBotao(b, 'mailto:' + email + '?subject=' + encodeURIComponent('Dúvida pelo site') + '&body=' + encodeURIComponent(pergunta), 'Enviar e-mail', false);
                    if (wa) linkBotao(b, wa, 'Chamar no WhatsApp', true);
                    return;
                }
                if (r.tipo === 'aviso') {
                    bolha('bot', r.texto);
                    return;
                }
                const b = bolha('bot', r.texto);
                if (r.link && linkSeguro(r.link)) {
                    // Links para a política sempre usam o endereço atual da página de privacidade
                    let destino = r.link.indexOf('politica') !== -1 ? politica : r.link;
                    // Seção que só existe na página inicial (ex.: #processo aberto em servico.php)
                    if (destino.charAt(0) === '#' && !document.getElementById(destino.slice(1))) destino = './' + destino;
                    linkBotao(b, destino, rotuloLink(destino), false);
                }
            });
        };

        const perguntar = (texto, id) => {
            const limpo = String(texto || '').trim().slice(0, 200);
            if (!limpo) return;
            saudar();
            bolha('usuario', limpo);
            const doBotao = id ? responderBotao(id, itens, email) : [];
            mostrarRespostas(limpo, doBotao.length ? doBotao : responder(limpo, itens, email, textos));
        };

        // Outros botões que abrem o assistente (ex.: no fim das perguntas frequentes). Ao fechar,
        // o foco volta para quem abriu; no celular o botão flutuante fica escondido pelo CSS.
        const atalhos = [...document.querySelectorAll('[data-abrir-assistente]')];
        let origem = botao;
        const devolverFoco = () => {
            const alvo = origem && origem.getClientRects().length ? origem : botao;
            alvo.focus();
        };

        const alternar = (aberto) => {
            painel.hidden = !aberto;
            botao.setAttribute('aria-expanded', String(aberto));
            atalhos.forEach(a => a.setAttribute('aria-expanded', String(aberto)));
            botao.hidden = aberto;
            document.documentElement.classList.toggle('chat-aberto', aberto);
            painel.setAttribute('aria-modal', String(aberto && telaPequena.matches));
            if (aberto) {
                if (!semMovimento) {
                    painel.classList.remove('abrindo');
                    void painel.offsetWidth; // reinicia a animação de abertura
                    painel.classList.add('abrindo');
                }
                saudar();
                (toque.matches ? fechar : campo).focus({ preventScroll: true });
            }
        };

        botao.addEventListener('click', () => { origem = botao; alternar(true); });
        atalhos.forEach(a => {
            a.hidden = false;
            a.addEventListener('click', (e) => { e.preventDefault(); origem = a; alternar(true); });
        });
        fechar.addEventListener('click', () => { alternar(false); devolverFoco(); });
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            perguntar(campo.value);
            campo.value = '';
        });
        // Clicar fora do painel também fecha. O caminho do clique é lido na hora em que ele acontece: os botões de
        // pergunta são refeitos a cada resposta e, depois disso, já não estariam dentro do painel. Se o gesto
        // começou dentro (selecionar texto e soltar fora) ou foi num botão que abre o assistente, não fecha.
        let comecouDentro = false;
        const dentro = (e) => e.composedPath().some(n => n === painel || n === botao || atalhos.includes(n));
        document.addEventListener('pointerdown', (e) => { comecouDentro = dentro(e); }, true);
        document.addEventListener('click', (e) => {
            const ignorar = painel.hidden || comecouDentro || dentro(e);
            comecouDentro = false; // um clique pelo teclado não tem pointerdown: não herda o gesto anterior
            if (ignorar) return;
            alternar(false); // o foco fica onde a pessoa clicou, não volta para o botão
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !painel.hidden) { alternar(false); devolverFoco(); }
            if (e.key === 'Tab' && !painel.hidden && telaPequena.matches) {
                const focaveis = [...painel.querySelectorAll('button, a[href], input')].filter(x => x.getClientRects().length);
                if (!focaveis.length) return;
                const primeiro = focaveis[0];
                const ultimo = focaveis[focaveis.length - 1];
                const atual = document.activeElement;
                if (!painel.contains(atual)) { e.preventDefault(); primeiro.focus(); }
                else if (e.shiftKey && atual === primeiro) { e.preventDefault(); ultimo.focus(); }
                else if (!e.shiftKey && atual === ultimo) { e.preventDefault(); primeiro.focus(); }
            }
        });
    }

    if (typeof module !== 'undefined' && module.exports) {
        module.exports = { normalizar, responder, responderBotao, preparar, pontuar };
    }
    if (typeof document !== 'undefined') {
        const raiz = document.getElementById('assistente');
        if (raiz) montar(raiz);
    }
})();
