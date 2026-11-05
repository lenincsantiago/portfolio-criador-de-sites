(function () {
    'use strict';

    const raiz = document.documentElement;
    raiz.classList.add('js');
    const semMovimento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    // Visitante que pausou as animações pelo botão do rodapé (movimento.js) em uma visita anterior.
    // A classe é aplicada aqui também, porque este arquivo roda antes e assim nada chega a se mexer.
    let pausouAntes = false;
    try { pausouAntes = !semMovimento && localStorage.getItem('animacoes') === 'pausadas'; } catch (e) { /* armazenamento bloqueado */ }
    raiz.classList.toggle('animacoes-pausadas', pausouAntes);
    const pausadas = () => raiz.classList.contains('animacoes-pausadas');
    const temObservador = 'IntersectionObserver' in window;
    // Timers e observadores criados aqui, para serem limpos quando a página for descartada
    const timers = new Set();
    const esperar = (fn, ms) => {
        const id = setTimeout(() => { timers.delete(id); fn(); }, ms);
        timers.add(id);
        return id;
    };
    // Cancela um timer criado por esperar() e tira da lista (senão a lista cresce a cada pausa do carrossel)
    const cancelar = (id) => { clearTimeout(id); timers.delete(id); };
    window.addEventListener('pagehide', () => { timers.forEach(clearTimeout); timers.clear(); });

    // No iPhone, :active só funciona no toque se houver um ouvinte de toque na página
    document.addEventListener('touchstart', () => {}, { passive: true });

    // Abas ocultas: pausa tudo o que é contínuo
    document.addEventListener('visibilitychange', () => {
        raiz.classList.toggle('aba-oculta', document.hidden);
    });

    /* ---------- Rolagem: um único ouvinte, com requestAnimationFrame ---------- */
    const aoRolar = [];
    let rolagemPendente = false;
    const processarRolagem = () => {
        rolagemPendente = false;
        const y = window.scrollY;
        aoRolar.forEach(fn => fn(y));
    };
    window.addEventListener('scroll', () => {
        if (!rolagemPendente) {
            rolagemPendente = true;
            requestAnimationFrame(processarRolagem);
        }
    }, { passive: true });

    // Cabeçalho ganha sombra depois de rolar
    const barra = document.querySelector('.barra');
    if (barra) aoRolar.push(y => barra.classList.toggle('rolou', y > 20));

    // Barra de progresso: o CSS cuida dela onde há suporte a animation-timeline; aqui fica o plano B
    const progresso = document.querySelector('.progresso span');
    const progressoPorCss = window.CSS && CSS.supports && CSS.supports('animation-timeline: scroll()');
    if (progresso && !progressoPorCss && !semMovimento) {
        aoRolar.push(y => {
            const total = raiz.scrollHeight - window.innerHeight;
            progresso.style.transform = 'scaleX(' + (total > 0 ? Math.min(1, y / total) : 0).toFixed(4) + ')';
        });
    }

    /* ---------- Menu do celular ---------- */
    const hamb = document.getElementById('hamb');
    const nav = document.getElementById('nav');
    if (hamb && nav) {
        const rotuloAbrir = hamb.getAttribute('aria-label') || 'Abrir menu';
        const definir = (aberto) => {
            nav.classList.toggle('aberto', aberto);
            hamb.classList.toggle('ativo', aberto);
            hamb.setAttribute('aria-expanded', String(aberto));
            hamb.setAttribute('aria-label', aberto ? 'Fechar menu' : rotuloAbrir);
        };
        hamb.addEventListener('click', (e) => {
            e.preventDefault();
            definir(!nav.classList.contains('aberto'));
            // O menu vem antes do botão no HTML: ao abrir, o foco vai para o primeiro link
            if (nav.classList.contains('aberto')) nav.querySelector('a')?.focus();
        });
        hamb.addEventListener('keydown', (e) => {
            if (e.key === ' ') {
                e.preventDefault();
                definir(!nav.classList.contains('aberto'));
                if (nav.classList.contains('aberto')) nav.querySelector('a')?.focus();
            }
        });
        nav.addEventListener('click', (e) => { if (e.target.closest('a')) definir(false); });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && nav.classList.contains('aberto')) { definir(false); hamb.focus(); }
        });
        document.addEventListener('click', (e) => {
            if (nav.classList.contains('aberto') && !e.target.closest('.barra')) definir(false);
        });
        // Tab saiu do menu (para o conteúdo): fecha, senão o menu aberto fica por cima da página
        document.querySelector('.barra')?.addEventListener('focusout', (e) => {
            if (nav.classList.contains('aberto') && e.relatedTarget && !e.relatedTarget.closest('.barra')) definir(false);
        });
    }

    /* ---------- Terminal interativo (o texto vem do próprio site; nada é inserido como HTML) ---------- */
    const term = document.getElementById('terminal');
    const corpo = document.getElementById('term-corpo');
    const formTerm = document.getElementById('term-form');
    const campo = document.getElementById('term-input');
    if (term && corpo && formTerm && campo) {
        const historico = [];
        let aoLimpar = null; // a demonstração em ciclo (mais abaixo) registra aqui o que fazer depois do comando "limpar"
        let posicao = -1;
        let ordem = 0; // posição da linha dentro de uma mesma resposta, para o atraso curto entre linhas

        const rolar = () => { corpo.scrollTop = corpo.scrollHeight; };
        const novaLinha = (classe) => {
            const p = document.createElement('p');
            p.className = 't-linha nova' + (classe ? ' ' + classe : '');
            p.style.setProperty('--i', String(Math.min(ordem++, 8)));
            corpo.appendChild(p);
            return p;
        };
        const linha = (texto, classe) => {
            novaLinha(classe).textContent = texto;
            rolar();
        };
        const link = (rotulo, href, nova) => {
            const a = document.createElement('a');
            a.className = 't-link';
            a.href = href;
            a.textContent = rotulo;
            if (nova) { a.target = '_blank'; a.rel = 'noopener noreferrer'; }
            novaLinha().appendChild(a);
            rolar();
        };
        // Botão de comando dentro da tela: o clique cai no mesmo tratamento dos chips de baixo
        const chip = (cmd) => {
            const botao = document.createElement('button');
            botao.type = 'button';
            botao.className = 'chip';
            botao.dataset.cmd = cmd;
            botao.textContent = cmd;
            return botao;
        };

        // Preço e prazo usam a mesma resposta do assistente (editada no painel), para os dois nunca divergirem
        const baseAssistente = (() => {
            try {
                const dados = document.getElementById('assistente');
                return JSON.parse((dados && dados.dataset.itens) || '[]').filter(i => i && i.resposta);
            } catch (e) { return []; }
        })();
        const mostrarItem = (item) => {
            if (item) linha(item.resposta);
            else link('ver as perguntas frequentes →', '#faq');
            if (term.dataset.wa) link('pedir orçamento no WhatsApp →', term.dataset.wa, true);
        };
        const respostaDoAssistente = (id) => mostrarItem(baseAssistente.find(i => i.id === id));

        const comandos = {
            ajuda: () => {
                const inicio = corpo.lastElementChild; // a linha "$ ajuda"
                linha('Este terminal é um jeito diferente de conhecer meu trabalho.', 'destaque');
                linha('Digite o nome de um comando ou escreva a sua pergunta e aperte Enter:');
                [
                    ['serviços', 'o que eu faço'],
                    ['modelos', 'os dois jeitos de contratar'],
                    ['mensalidade', 'o que a mensalidade inclui'],
                    ['fidelidade', 'prazo mínimo e cancelamento'],
                    ['suporte', 'atendimento e garantia'],
                    ['prazo', 'quanto tempo leva'],
                    ['contato', 'falar comigo no WhatsApp'],
                    ['limpar', 'apaga a tela'],
                ].forEach(([nome, descricao]) => {
                    const p = novaLinha('t-ajuda');
                    // Só o nome, em texto: os botões de comando ficam na faixa abaixo da tela
                    const comando = document.createElement('span');
                    comando.className = 't-nome';
                    comando.textContent = nome;
                    p.appendChild(comando);
                    const span = document.createElement('span');
                    span.textContent = descricao;
                    p.appendChild(span);
                });
                linha('Dica: no computador, as setas ↑ e ↓ repetem os comandos que você já usou e Tab completa o nome.');
                // A ajuda é longa: a tela para no começo dela, não no fim, para o título ficar à vista
                corpo.scrollTop += inicio.getBoundingClientRect().top - corpo.getBoundingClientRect().top - 8;
            },
            sobre: () => {
                const texto = document.querySelector('[data-sobre]');
                linha(texto ? texto.textContent : 'Sem texto de apresentação.');
            },
            servicos: () => {
                const nomes = [...document.querySelectorAll('#servicos .linha-tempo h3')].map(h => h.textContent);
                if (!nomes.length) { linha('Nenhum serviço cadastrado ainda.'); return; }
                nomes.forEach((n, i) => linha('  ' + String(i + 1).padStart(2, '0') + '. ' + n));
                link('ver os serviços →', '#servicos');
            },
            projetos: () => link('abrir a seção de projetos →', '#projetos'),
            preco: () => respostaDoAssistente('preco'),
            prazo: () => respostaDoAssistente('prazo'),
            modelos: () => respostaDoAssistente('contrato'),
            mensalidade: () => respostaDoAssistente('manutencao_inclui'),
            fidelidade: () => respostaDoAssistente('cancelamento'),
            suporte: () => respostaDoAssistente('suporte'),
            dominio: () => respostaDoAssistente('dominio'),
            contato: () => {
                linha('Vamos conversar. Eu respondo pelo WhatsApp:');
                if (term.dataset.wa) link('WhatsApp →', term.dataset.wa, true);
                if (term.dataset.email) link('e-mail → ' + term.dataset.email, 'mailto:' + term.dataset.email);
                link('ou use o formulário →', '#contato');
            },
            whoami: () => linha('visitante (e o site é do ' + (term.dataset.nome || 'Seu Nome') + ')'),
            limpar: () => {
                while (corpo.children.length > 1) corpo.removeChild(corpo.lastElementChild);
                // A tela nunca fica vazia: a linha de boas-vindas (primeira) continua no lugar
                ordem = 0;
                if (aoLimpar) aoLimpar();
            },
        };
        const apelidos = { help: 'ajuda', clear: 'limpar', cls: 'limpar', 'serviços': 'servicos', ls: 'ajuda', 'preço': 'preco', valor: 'preco', quanto: 'preco', tempo: 'prazo', 'domínio': 'dominio', garantia: 'suporte', contrato: 'modelos' };

        /* "Você quis dizer": compara o que foi digitado (sem acentos) com os comandos conhecidos
           e sugere o mais parecido, se a diferença for de até 2 letras */
        const conhecidos = ['ajuda', 'sobre', 'serviços', 'preço', 'prazo', 'projetos', 'contato', 'modelos', 'mensalidade', 'fidelidade', 'suporte', 'domínio', 'limpar'];
        const semAcento = (s) => s.normalize('NFD').replace(/[̀-ͯ]/g, '');
        const distancia = (a, b) => {
            let ant = Array.from({ length: b.length + 1 }, (_, j) => j);
            for (let i = 1; i <= a.length; i++) {
                const atual = [i];
                for (let j = 1; j <= b.length; j++) {
                    atual[j] = Math.min(ant[j] + 1, atual[j - 1] + 1, ant[j - 1] + (a[i - 1] === b[j - 1] ? 0 : 1));
                }
                ant = atual;
            }
            return ant[b.length];
        };
        const parecido = (cmd) => {
            const alvo = semAcento(cmd);
            let melhor = null;
            let menor = 3;
            conhecidos.forEach(c => {
                const d = distancia(alvo, semAcento(c));
                if (d < menor) { menor = d; melhor = c; }
            });
            return melhor;
        };

        /* Pergunta livre: procura nas palavras-chave do assistente (as mesmas do botão "Dúvidas").
           Vale a resposta cujas palavras-chave mais aparecem no texto; sem nenhuma, não inventa resposta. */
        const buscarNoAssistente = (texto) => {
            const alvo = ' ' + semAcento(texto).toLowerCase().replace(/[^a-z0-9 ]/g, ' ').replace(/\s+/g, ' ') + ' ';
            let melhor = null;
            let maior = 0;
            baseAssistente.forEach(item => {
                const palavras = String(item.palavras || '').split(',').map(w => semAcento(w).toLowerCase().trim()).filter(w => w.length >= 3);
                const pontos = palavras.reduce((soma, w) => soma + (alvo.includes(' ' + w + ' ') || (w.length >= 5 && alvo.includes(w)) ? w.length : 0), 0);
                if (pontos > maior) { maior = pontos; melhor = item; }
            });
            return maior >= 3 ? melhor : null;
        };

        const executar = (texto, eco, semHistorico) => {
            const cmd = texto.trim().toLowerCase().replace(/\s+/g, ' ');
            if (!cmd) return;
            ordem = 0;
            linha(eco || cmd, 'cmd'); // o botão de pergunta mostra a pergunta; o comando que roda por baixo é outro
            if (!semHistorico) { // a demonstração não entra no histórico: a seta para cima não deve lembrar algo que a pessoa não digitou
                historico.unshift(cmd);
                if (historico.length > 30) historico.pop();
            }
            posicao = -1;
            const nome = apelidos[cmd] || cmd;
            if (cmd === 'sudo contratar') {
                linha('Permissão concedida. Me conte sobre o seu negócio e eu respondo com uma proposta.', 'destaque');
                if (term.dataset.wa) link('chamar no WhatsApp →', term.dataset.wa, true);
            } else if (Object.prototype.hasOwnProperty.call(comandos, nome)) {
                comandos[nome]();
            } else {
                const achado = buscarNoAssistente(cmd);
                const sugestao = achado ? null : parecido(cmd);
                if (achado) {
                    mostrarItem(achado);
                } else if (sugestao) {
                    linha('comando não encontrado: ' + cmd + '. Você quis dizer:');
                    novaLinha().appendChild(chip(sugestao));
                    rolar();
                } else {
                    linha('comando não encontrado: ' + cmd + '. Digite ajuda.');
                }
            }
            // Guarda só as últimas linhas (a primeira é a instrução): a página pode ficar aberta por horas
            while (corpo.children.length > 40) corpo.removeChild(corpo.children[1]);
        };

        formTerm.addEventListener('submit', (e) => {
            e.preventDefault();
            executar(campo.value);
            campo.value = '';
        });

        // Setas para cima e para baixo repetem comandos anteriores
        campo.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowUp' && historico.length) {
                posicao = Math.min(posicao + 1, historico.length - 1);
                campo.value = historico[posicao];
                e.preventDefault();
            } else if (e.key === 'ArrowDown') {
                posicao = Math.max(posicao - 1, -1);
                campo.value = posicao >= 0 ? historico[posicao] : '';
                e.preventDefault();
            } else if (e.key === 'Tab' && !e.shiftKey && campo.value.trim()) {
                // Tab completa o nome do comando quando só um combina; senão o Tab segue normal
                const inicio = semAcento(campo.value.trim().toLowerCase());
                const opcoes = conhecidos.filter(c => semAcento(c).startsWith(inicio));
                if (opcoes.length === 1 && semAcento(opcoes[0]) !== inicio) { campo.value = opcoes[0]; e.preventDefault(); }
            } else if (e.key === 'Escape' && campo.value) {
                campo.value = '';
                e.preventDefault();
            }
        });

        term.addEventListener('click', (e) => {
            const botao = e.target.closest('.chip[data-cmd]');
            if (!botao) return;
            executar(botao.dataset.cmd, botao.dataset.eco);
            // No toque não foca o campo: senão o teclado do celular sobe a cada comando
            if (!(window.matchMedia && window.matchMedia('(pointer: coarse)').matches)) campo.focus({ preventScroll: true });
        });

        /* Demonstração em ciclo: enquanto o terminal está na tela, ele digita sozinho uma pergunta pronta, mostra a
           resposta, espera o tempo de ler, limpa e passa para a próxima; depois da última, volta à primeira.
           Qualquer toque, tecla ou foco no terminal encerra o ciclo para sempre (a tela fica como está).
           Fora da tela, com a aba escondida ou com as animações pausadas, o ciclo espera.
           Com "menos movimento" mostra só a primeira resposta, de uma vez, sem digitar nem repetir. */
        const prontas = [...term.querySelectorAll('.chip[data-cmd]')].map(c => ({ cmd: c.dataset.cmd, eco: c.dataset.eco || c.dataset.cmd }));
        if (prontas.length && temObservador) {
            let encerrada = false;
            let digitando = false;
            let idDemo = null;
            let visivel = false;
            let vez = 0;
            const base = corpo.textContent.length; // só a linha de boas-vindas: o que passa disso é a pergunta e a resposta
            const aguardar = (fn, ms) => { idDemo = esperar(fn, ms); };
            // Enquanto roda sozinha, a demonstração não é anunciada pelo leitor de tela (a pessoa não pediu nada)
            const falar = (sim) => corpo.setAttribute('aria-live', sim ? 'polite' : 'off');
            const cancelarDemo = () => {
                if (encerrada) return;
                encerrada = true;
                cancelar(idDemo);
                falar(true);
                if (digitando) { campo.value = ''; digitando = false; }
            };
            ['pointerdown', 'keydown', 'focusin'].forEach(tipo => term.addEventListener(tipo, cancelarDemo, true));
            window.addEventListener('pagehide', cancelarDemo); // voltar pelo histórico não deve mostrar uma pergunta pela metade
            const limparTela = () => { while (corpo.children.length > 1) corpo.removeChild(corpo.lastElementChild); ordem = 0; };

            const responder = (pronta) => {
                if (encerrada) return;
                digitando = false;
                campo.value = '';
                executar(pronta.cmd, pronta.eco, true);
                vez = (vez + 1) % prontas.length;
                // Tempo de ler: cresce com o tamanho da resposta
                const leitura = Math.min(7500, 2200 + (corpo.textContent.length - base) * 14);
                aguardar(proxima, leitura);
            };
            const digitar = (pronta, n) => {
                if (encerrada) return;
                digitando = true;
                campo.value = pronta.eco.slice(0, n);
                if (n < pronta.eco.length) aguardar(() => digitar(pronta, n + 1), 45 + Math.random() * 30);
                else aguardar(() => responder(pronta), 450);
            };
            function proxima() {
                if (encerrada) return;
                if (!visivel || document.hidden || pausadas()) { aguardar(proxima, 600); return; }
                limparTela();
                digitar(prontas[vez], 1);
            }

            // "limpar": se a pessoa não mexer mais no terminal, o ciclo volta sozinho depois de alguns segundos
            // (qualquer toque, tecla ou foco nesse intervalo cancela de novo, como antes)
            aoLimpar = () => {
                if (semMovimento || !encerrada) return;
                encerrada = false;
                digitando = false;
                vez = 0;
                falar(false);
                aguardar(proxima, 4000);
            };

            new IntersectionObserver((entradas) => {
                const antes = visivel;
                visivel = entradas[entradas.length - 1].isIntersecting;
                if (!visivel || antes || encerrada) return;
                if (semMovimento || pausadas()) { // uma resposta só, de uma vez
                    encerrada = true;
                    executar(prontas[0].cmd, prontas[0].eco, true);
                    return;
                }
                if (idDemo === null) { falar(false); aguardar(proxima, 500); }
            }, { threshold: 0.4 }).observe(term);
        }

    }

    /* ---------- Botão "voltar ao topo" e WhatsApp flutuante ---------- */
    const topoBtn = document.getElementById('topo-btn');
    if (topoBtn) {
        topoBtn.hidden = false;
        aoRolar.push(y => {
            const mostrar = y > 600;
            topoBtn.classList.toggle('mostrar', mostrar);
            topoBtn.tabIndex = mostrar ? 0 : -1;
        });
        topoBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: semMovimento ? 'auto' : 'smooth' });
            const alvo = document.getElementById('conteudo');
            if (alvo) alvo.focus({ preventScroll: true });
        });
    }

    // WhatsApp flutuante e botão de dúvidas: entram depois de rolar um pouco, para não cobrir os botões
    // e o texto da primeira tela (que já tem o seu próprio botão de contato). O WhatsApp sai quando
    // o formulário de contato ou o rodapé estão na tela.
    raiz.classList.add('flutuantes-espera');
    const waFlutuante = document.getElementById('wa-flutuante');
    if (waFlutuante) {
        let liberado = false;
        let perto = false;
        let chamou = false;
        const atualizar = () => {
            const visivel = liberado && !perto;
            waFlutuante.classList.toggle('espera', !visivel);
            waFlutuante.tabIndex = visivel ? 0 : -1;
            if (visivel && !chamou && !semMovimento) {
                chamou = true;
                waFlutuante.classList.add('chama');
                esperar(() => waFlutuante.classList.remove('chama'), 1000);
            }
        };
        // Página curta, que quase não rola: os botões ficam sempre à vista
        const curta = document.documentElement.scrollHeight - window.innerHeight < 400;
        const liberar = (sim) => {
            if (sim === liberado) return;
            liberado = sim;
            raiz.classList.toggle('flutuantes-espera', !sim);
            atualizar();
        };
        waFlutuante.classList.add('espera');
        // Voltando ao topo, eles saem de novo e deixam a primeira tela livre
        aoRolar.push(y => liberar(curta || y > 320));
        liberar(curta);
        if (temObservador) {
            const alvos = document.querySelectorAll('#contato, .rodape-cta');
            const naTela = new Set();
            const obs = new IntersectionObserver((entradas) => {
                entradas.forEach(en => { if (en.isIntersecting) naTela.add(en.target); else naTela.delete(en.target); });
                perto = naTela.size > 0;
                // Com o formulário na tela, os botões de dúvidas e de topo também saem, para não cobrir o botão de enviar
                raiz.classList.toggle('perto-contato', [...naTela].some(a => a.id === 'contato'));
                atualizar();
            }, { threshold: 0.15 });
            alvos.forEach(a => obs.observe(a));
        }
    }

    /* ---------- Contagem anônima de cliques (elementos com data-evento) ---------- */
    const enviarEvento = (nome) => {
        const dados = new FormData();
        dados.append('evento', nome);
        if (navigator.sendBeacon) {
            navigator.sendBeacon('evento.php', dados);
        } else {
            fetch('evento.php', { method: 'POST', body: dados, keepalive: true }).catch(() => {});
        }
    };
    document.addEventListener('click', (e) => {
        const el = e.target.closest('[data-evento]');
        if (el && el.tagName !== 'FORM') enviarEvento(el.dataset.evento);
    });

    /* ---------- Formulário de contato: estado de envio ---------- */
    document.querySelectorAll('.form-contato').forEach(form => {
        form.addEventListener('submit', () => {
            if (form.dataset.evento) enviarEvento(form.dataset.evento);
            const botao = form.querySelector('button[type="submit"]');
            form.classList.add('enviando');
            form.setAttribute('aria-busy', 'true');
            if (botao) {
                botao.textContent = form.dataset.enviando || 'Enviando…';
                // Desativa depois do envio começar, para não bloquear o próprio envio
                esperar(() => { botao.disabled = true; }, 0);
            }
        });
    });
    /* ---------- Rascunho do formulário: só no navegador, só nesta aba (sessionStorage) ----------
       Se o envio falhar (sessão expirada, limite, campo vazio), a página volta com o texto preenchido.
       Envio aceito (?contato=ok) apaga o rascunho. A política de privacidade conta isso na seção 5. */
    const CHAVE_RASCUNHO = 'rascunho-contato';
    document.querySelectorAll('.form-contato').forEach(form => {
        const campos = [...form.querySelectorAll('input[name="nome"], input[name="contato"], input[name="ramo"], textarea[name="mensagem"]')];
        if (new URLSearchParams(location.search).get('contato') === 'ok') {
            try { sessionStorage.removeItem(CHAVE_RASCUNHO); } catch (e) { /* armazenamento bloqueado */ }
            return;
        }
        let rascunho = null;
        try { rascunho = JSON.parse(sessionStorage.getItem(CHAVE_RASCUNHO) || 'null'); } catch (e) { rascunho = null; }
        if (rascunho && typeof rascunho === 'object') {
            campos.forEach(c => { if (!c.value && typeof rascunho[c.name] === 'string') c.value = rascunho[c.name]; });
        }
        form.addEventListener('input', () => {
            const dados = Object.fromEntries(campos.map(c => [c.name, c.value]));
            try { sessionStorage.setItem(CHAVE_RASCUNHO, JSON.stringify(dados)); } catch (e) { /* só não guarda */ }
        });
    });

    // Ao voltar pelo botão "voltar" do navegador, o formulário volta ao estado normal
    window.addEventListener('pageshow', (e) => {
        if (!e.persisted) return;
        document.querySelectorAll('.form-contato.enviando').forEach(form => {
            form.classList.remove('enviando');
            form.removeAttribute('aria-busy');
            const botao = form.querySelector('button[type="submit"]');
            if (botao) botao.disabled = false;
        });
    });

    /* ---------- Acordeões (FAQ e "Saiba mais"): abre e fecha com altura animada ---------- */
    document.querySelectorAll('details[data-animar]').forEach(det => {
        const resumo = det.querySelector('summary');
        const conteudo = det.querySelector('[data-corpo]');
        if (!resumo || !conteudo || semMovimento || !conteudo.animate) return;
        let animacao = null;
        // Estado desejado (aberto/fechado): det.open só muda no fim do fechamento, então não serve
        // para saber o que fazer num clique dado no meio da animação
        let aberto = det.open;
        det.addEventListener('toggle', () => { if (!animacao) aberto = det.open; });
        resumo.addEventListener('click', (e) => {
            e.preventDefault();
            // Clique no meio da animação: parte da altura em que o conteúdo está agora
            let inicio = null;
            if (animacao) {
                inicio = conteudo.getBoundingClientRect().height;
                animacao.onfinish = null;
                animacao.cancel();
                animacao = null;
            }
            const abrindo = !aberto;
            aberto = abrindo;
            if (abrindo) det.open = true;
            const altura = conteudo.scrollHeight;
            const de = (inicio !== null ? inicio : (abrindo ? 0 : altura)) + 'px';
            const quadros = abrindo
                ? [{ height: de, opacity: inicio !== null ? 1 : 0 }, { height: altura + 'px', opacity: 1 }]
                : [{ height: de, opacity: 1 }, { height: '0px', opacity: 0 }];
            // Durações do site: abrir no tempo padrão (0,4 s), fechar no rápido (0,15 s): o que sai some mais depressa
            const atual = conteudo.animate(quadros, { duration: abrindo ? 400 : 150, easing: 'cubic-bezier(.16, 1, .3, 1)' });
            animacao = atual;
            atual.onfinish = () => {
                if (animacao === atual) animacao = null;
                if (!abrindo) det.open = false;
            };
        });
    });

    /* ---------- Rolagem do terminal: cada seção "executa" ao entrar na tela ---------- */
    const secoes = [...document.querySelectorAll('.reveal')];
    // Número de caracteres do rótulo, para a digitação andar uma letra por passo
    const prepararRotulo = (secao) => {
        secao.querySelectorAll('.rotulo').forEach(r => r.style.setProperty('--n', String(Math.max(4, r.textContent.trim().length))));
    };
    const numerar = (secao) => {
        secao.querySelectorAll('.etiquetas > li, .linha-tempo > li, .etapas > li, .faq > details, .campo, .form-contato .btn').forEach(el => {
            const irmaos = [...el.parentElement.children].filter(c => c.matches('li, details, .campo, .btn'));
            el.style.setProperty('--i', String(Math.min(Math.max(0, irmaos.indexOf(el)), 7))); // até 7: o último começa em 0,4 s
        });
    };
    if (temObservador && !semMovimento) {
        // Só esconde (classe "aguarda") o que ainda está abaixo da tela: o que já aparece não pisca
        const limite = window.innerHeight * 0.92;
        secoes.forEach(secao => {
            if (secao.getBoundingClientRect().top > limite) {
                prepararRotulo(secao);
                numerar(secao);
                secao.classList.add('aguarda');
            }
        });
        const obsSecoes = new IntersectionObserver((entradas) => {
            entradas.forEach(en => {
                if (en.isIntersecting) {
                    en.target.classList.add('visivel');
                    obsSecoes.unobserve(en.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
        secoes.forEach(s => { if (s.classList.contains('aguarda')) obsSecoes.observe(s); });

        // Teclado: se o foco chega a uma seção que ainda não apareceu, ela aparece na hora
        // (sem esperar a rolagem), para o item focado nunca ficar invisível
        document.addEventListener('focusin', (e) => {
            const secao = e.target.closest && e.target.closest('.aguarda:not(.visivel)');
            if (!secao) return;
            secao.classList.add('visivel');
            obsSecoes.unobserve(secao);
        });

        // Segurança: se algo impedir o observador de disparar, nada fica escondido
        window.addEventListener('beforeprint', () => {
            secoes.forEach(s => s.classList.add('visivel'));
        });
    }

    /* ---------- Celular de exemplo no topo: troca o site a cada 10 s ----------
       Cada exemplo é uma mini-página de verdade (pasta exemplos/) num iframe reduzido. Na vez de cada exemplo, a
       mini-página rola sozinha do topo ao rodapé, devagar, e o seguinte entra. O iframe só ganha endereço (src) quando
       chega a vez dele: o atual e o seguinte carregam antes, os outros esperam.
       As bolinhas escolhem o exemplo e a contagem recomeça dali, sem parar. A troca e a rolagem param com o mouse
       sobre o celular, quando a pessoa rola ou toca a mini-página (voltam 8 s depois do último gesto), com o foco de
       teclado nas bolinhas (clique e toque não contam), com a aba escondida, com o celular fora da tela e com as
       animações pausadas (botão do rodapé ou botão ao lado das bolinhas). Com "menos movimento" no sistema não troca
       nem rola sozinho: as bolinhas e a rolagem com o dedo ou a roda do mouse continuam funcionando. */
    const vitrine = document.querySelector('.celular-exemplo');
    if (vitrine) {
        const sites = [...vitrine.querySelectorAll('.ce-site')];
        const pontos = [...vitrine.querySelectorAll('.ce-ponto')];
        const tela = vitrine.querySelector('.ce-tela');
        const endereco = vitrine.querySelector('.ce-endereco');

        // Relógio do celular: sempre a hora de Brasília, qualquer que seja o fuso do computador de quem vê.
        // Atualiza na virada de cada minuto e ao voltar para a aba (timers de abas escondidas atrasam).
        const relogio = vitrine.querySelector('.ce-hora');
        let formatoHora = null;
        try {
            formatoHora = new Intl.DateTimeFormat('pt-BR', { timeZone: 'America/Sao_Paulo', hour: '2-digit', minute: '2-digit', hourCycle: 'h23' });
        } catch (e) { /* sem suporte a fuso horário: fica a hora que veio no HTML */ }
        if (relogio && formatoHora) {
            const mostrarHora = () => { relogio.textContent = formatoHora.format(new Date()); };
            const proximoMinuto = () => {
                mostrarHora();
                esperar(proximoMinuto, 60000 - (Date.now() % 60000) + 50);
            };
            document.addEventListener('visibilitychange', () => { if (!document.hidden) mostrarHora(); });
            proximoMinuto();
        }
        const INTERVALO = 10000;      // igual a --ce-duracao (barrinha ce-enche) no CSS
        const ATRASO_ROLAGEM = 800;   // a rolagem começa um pouco depois de o exemplo entrar
        const FOLGA_FINAL = 1200;     // e chega ao rodapé um pouco antes da troca, para o rodapé ser visto
        const ESPERA_GESTO = 8000;    // depois do último gesto na mini-página, volta a trocar sozinho
        const pausas = new Set();     // motivos de pausa ativos: mouse, foco, aba, botao, pausa-botao, toque, rolagem, fora
        let atual = 0;
        let timer = null;
        let inicio = 0;           // quando a contagem atual começou
        let restante = INTERVALO; // quanto falta para a próxima troca

        const quadroDe = (site) => site.querySelector('.ce-quadro');
        // Documento da mini-página, ou null se o navegador não deixar ler (outro domínio ou cabeçalho que bloqueia)
        const documentoDe = (site) => {
            try { return quadroDe(site)?.contentDocument || null; } catch (e) { return null; }
        };

        /* Escala exata: a mini-página tem 390 px de largura e é reduzida para a largura da tela do celular.
           O CSS já tem um valor aproximado; aqui grava o exato e refaz quando o tamanho muda. */
        // A largura vem com casas decimais (clientWidth arredonda e deixaria uma fresta de meio pixel)
        const ajustarEscala = (largura) => {
            if (!tela) return;
            if (!(largura > 0)) largura = tela.clientWidth;
            if (largura > 0) tela.style.setProperty('--escala', (largura / 390).toFixed(5));
        };
        // Só o --escala muda (a largura da tela não depende dele), então o observador não entra em ciclo.
        // O observador também roda uma vez assim que começa a observar.
        if (tela && 'ResizeObserver' in window) {
            new ResizeObserver((entradas) => {
                ajustarEscala(entradas[0].contentRect.width);
                if (quadroRolagem) planejarRolagem(); // girou o celular no meio da rolagem: mede de novo e segue dali
            }).observe(tela);
        } else {
            ajustarEscala();
        }

        /* Rolagem automática da mini-página, do ponto onde ela está até o rodapé, em "duracao" ms.
           Mede a altura uma vez no começo e depois só chama scrollTo a cada quadro (nada de ler medidas no meio).
           Curva ease-in-out (seno): sai devagar, anda no meio e pousa devagar no rodapé, como alguém lendo. */
        let quadroRolagem = 0;    // id do requestAnimationFrame da rolagem em andamento
        let timerRolagem = null;  // espera antes de começar a rolar
        const pararRolagem = () => {
            cancelAnimationFrame(quadroRolagem);
            quadroRolagem = 0;
            cancelar(timerRolagem);
            timerRolagem = null;
        };
        const suave = (t) => -(Math.cos(Math.PI * t) - 1) / 2;
        const rolarAuto = (site, duracao) => {
            pararRolagem();
            const janela = quadroDe(site)?.contentWindow;
            const doc = documentoDe(site);
            if (!janela || !doc || !doc.documentElement || duracao <= 0) return;
            const de = janela.scrollY;
            const ate = Math.max(0, doc.documentElement.scrollHeight - janela.innerHeight);
            const distancia = ate - de;
            if (distancia < 2) return;
            let comeco = 0;
            const passo = (agora) => {
                if (!comeco) comeco = agora;
                const t = Math.min(1, (agora - comeco) / duracao);
                janela.scrollTo(0, de + distancia * suave(t));
                // Para sozinho no fim; pararRolagem() também interrompe (pausa, troca de exemplo, gesto da pessoa)
                quadroRolagem = t < 1 ? requestAnimationFrame(passo) : 0;
            };
            quadroRolagem = requestAnimationFrame(passo);
        };

        // Planeja a rolagem do exemplo atual de acordo com o tempo que falta para a troca:
        // começa 800 ms depois da entrada e termina 1,2 s antes da troca (vale também ao retomar no meio)
        const planejarRolagem = () => {
            pararRolagem();
            if (semMovimento || pausas.size) return;
            const site = sites[atual];
            // Tempo que falta de verdade: com a contagem correndo, "restante" é o valor de quando ela começou
            const falta = timer ? Math.max(0, restante - (performance.now() - inicio)) : restante;
            const atraso = Math.max(0, falta - (INTERVALO - ATRASO_ROLAGEM));
            const duracao = falta - atraso - FOLGA_FINAL;
            if (duracao < 600) return; // pouco tempo: não vale a pena começar a rolar
            timerRolagem = esperar(() => { timerRolagem = null; rolarAuto(site, duracao); }, atraso);
        };

        /* Gestos da pessoa dentro da mini-página (roda do mouse, toque, clique, teclado): a rolagem automática para
           na hora, para não brigar com ela, e a troca espera 8 s depois do último gesto. */
        let timerGesto = null;
        let preso = false; // clique de mouse dentro da mini-página: a troca só volta com um clique fora do celular
        const aoGesto = (e) => {
            pararRolagem();
            pausar('rolagem');
            cancelar(timerGesto);
            timerGesto = null;
            if (e && e.type === 'pointerdown' && e.pointerType === 'mouse') {
                preso = true;
                return;
            }
            if (!preso) timerGesto = esperar(() => retomar('rolagem'), ESPERA_GESTO);
        };
        // Clique (ou toque) fora do celular: a troca volta na hora, com a contagem inteira de novo.
        // Solta todas as pausas feitas pela pessoa (clique, mouse, foco, toque), não só a do clique: se uma delas
        // ficasse presa (por exemplo, o mouse saiu do iframe sem a página principal perceber), a troca nunca voltaria.
        // Fase de captura: vale mesmo que algum elemento da página segure o evento. Cliques dentro do iframe não chegam
        // aqui (são de outro documento); os da moldura e das bolinhas são ignorados.
        const PAUSAS_DA_PESSOA = ['rolagem', 'mouse', 'foco', 'toque'];
        const soltarPausasDaPessoa = () => {
            preso = false;
            cancelar(timerGesto);
            timerGesto = null;
            cancelar(timerToque);
            PAUSAS_DA_PESSOA.forEach(motivo => pausas.delete(motivo));
            restante = INTERVALO;
            if (pausas.size) return; // ainda pausado por outro motivo (botão de pausa, aba escondida)
            vitrine.classList.remove('pausado');
            const ponto = pontos[atual];
            if (ponto) { ponto.classList.remove('ativo'); void ponto.offsetWidth; ponto.classList.add('ativo'); } // a barrinha recomeça do zero
            agendar();
        };
        document.addEventListener('pointerdown', (e) => {
            if (vitrine.contains(e.target)) return;
            if (!preso && !PAUSAS_DA_PESSOA.some(motivo => pausas.has(motivo))) return;
            soltarPausasDaPessoa();
        }, { passive: true, capture: true });
        // O mouse saiu da tela do celular depois de a pessoa ter clicado ou rolado lá dentro: a troca volta na hora,
        // sem precisar de um clique fora. Só passar o mouse por cima (sem gesto) segue o caminho normal, pelo retomar('mouse').
        const mouseSaiu = () => {
            if (preso || pausas.has('rolagem')) soltarPausasDaPessoa();
        };

        const documentosOuvidos = new WeakSet();
        const ouvirGestos = (doc) => {
            if (documentosOuvidos.has(doc)) return;
            documentosOuvidos.add(doc);
            ['wheel', 'touchstart', 'pointerdown', 'keydown'].forEach(tipo => doc.addEventListener(tipo, aoGesto, { passive: true }));
            // O ponteiro que entra direto no iframe não é visto pela página principal: o próprio documento avisa
            doc.addEventListener('pointermove', (e) => { if (e.pointerType === 'mouse' && !pausas.has('mouse')) pausar('mouse'); }, { passive: true });
            // O mouse sair da tela (para a moldura ou para fora do celular) é visto pelo próprio documento da mini-página
            doc.documentElement.addEventListener('pointerleave', (e) => { if (e.pointerType === 'mouse') mouseSaiu(); }, { passive: true });
        };

        // Mini-página pronta: confere se dá para ler o documento; se não der, fica o fundo de espera (nome e endereço)
        const aoCarregar = (site) => {
            const doc = documentoDe(site);
            if (!doc || !doc.body || !doc.body.childElementCount) {
                site.classList.add('sem-quadro');
                return;
            }
            site.classList.remove('sem-quadro');
            ouvirGestos(doc);
            // Se é a vez dela e o tempo já está correndo, começa a rolar agora que dá para medir a altura
            if (site === sites[atual] && timer) planejarRolagem();
        };

        // Passa o data-src para o src do iframe, uma vez só
        const preparados = new WeakSet();
        const carregar = (site) => {
            const quadro = site && quadroDe(site);
            if (!quadro || preparados.has(site)) return;
            preparados.add(site);
            quadro.addEventListener('load', () => aoCarregar(site));
            if (!quadro.getAttribute('src') && quadro.dataset.src) {
                quadro.setAttribute('src', quadro.dataset.src);
            } else if (documentoDe(site)?.readyState === 'complete' && documentoDe(site).URL !== 'about:blank') {
                aoCarregar(site); // o primeiro já vem com src no HTML e pode ter carregado antes deste arquivo
            }
        };
        const carregarVez = () => {
            carregar(sites[atual]);
            carregar(sites[(atual + 1) % sites.length]);
        };

        /* Troca de exemplo como troca de aba no Chrome do iPhone (cerca de 1,2 s):
           1. a página atual encolhe até virar um cartão numa grade escura;
           2. o contorno azul passa para o cartão do próximo exemplo (a grade rola se for preciso);
           3. o cartão do próximo exemplo cresce até ocupar a tela.
           Só transform, opacity e clip-path, pela Web Animations API. Sem ela, ou com "menos movimento", a troca é direta. */
        const abas = vitrine.querySelector('.ce-abas');
        const listaAbas = abas && abas.querySelector('.ce-abas-lista');
        const barraCima = vitrine.querySelector('.ce-chrome');
        const barraBaixo = vitrine.querySelector('.ce-rodape');
        const podeAnimar = !!(tela && listaAbas && barraCima && barraBaixo && typeof tela.animate === 'function'
            && window.CSS && CSS.supports && CSS.supports('clip-path', 'inset(0px 0px 0px 0px round 4px)'));
        const DURACAO_TROCA = 1240;
        const NS_SVG = 'http://www.w3.org/2000/svg';
        vitrine.querySelectorAll('.ce-abas-n').forEach(n => { n.textContent = String(sites.length); });

        // Um cartão por exemplo: cor do negócio (a mesma classe do exemplo), nome e endereço
        const cartoes = [];
        let selecaoAba = null;
        if (podeAnimar) {
            sites.forEach((site) => {
                const nomeNegocio = site.querySelector('.ce-espera b')?.textContent || '';
                const aba = document.createElement('div');
                aba.className = 'ce-aba ' + [...site.classList].filter(c => c.startsWith('ce-') && c !== 'ce-site').join(' ');
                const cab = document.createElement('div');
                cab.className = 'ce-aba-cab';
                const icone = document.createElement('span');
                icone.className = 'ce-aba-icone';
                const titulo = document.createElement('span');
                titulo.className = 'ce-aba-titulo';
                titulo.textContent = nomeNegocio;
                const x = document.createElementNS(NS_SVG, 'svg');
                x.setAttribute('viewBox', '0 0 8 8');
                x.setAttribute('class', 'ce-aba-x');
                const tracos = document.createElementNS(NS_SVG, 'path');
                tracos.setAttribute('d', 'M1 1l6 6M7 1L1 7');
                tracos.setAttribute('stroke', 'currentColor');
                tracos.setAttribute('stroke-width', '1.2');
                tracos.setAttribute('stroke-linecap', 'round');
                x.appendChild(tracos);
                cab.append(icone, titulo, x);
                const corpo = document.createElement('div');
                corpo.className = 'ce-aba-corpo';
                const nome = document.createElement('b');
                nome.textContent = nomeNegocio;
                const dominio = document.createElement('small');
                dominio.textContent = site.dataset.endereco || '';
                corpo.append(nome, dominio);
                aba.append(cab, corpo);
                listaAbas.appendChild(aba);
                cartoes.push({ aba, corpo });
            });
            selecaoAba = document.createElement('span');
            selecaoAba.className = 'ce-aba-selecao';
            listaAbas.appendChild(selecaoAba);
        }

        let troca = null; // troca em andamento: { id, animacoes }
        let idTroca = 0;
        const limparTroca = () => {
            if (abas) abas.classList.remove('aberta');
            sites.forEach((site, i) => {
                site.classList.remove('animando');
                site.style.zIndex = '';
                site.classList.toggle('ativo', i === atual);
                site.setAttribute('aria-hidden', String(i !== atual));
            });
            if (endereco) endereco.textContent = sites[atual].dataset.endereco || '';
        };
        // Termina a troca já começada no estado final (o exemplo de destino na tela), por exemplo quando
        // a pessoa clica numa bolinha no meio da animação
        const encerrarTroca = () => {
            if (!troca) return;
            const t = troca;
            troca = null;
            t.animacoes.forEach(a => a.cancel());
            limparTroca();
        };

        const trocarAba = (de, para) => {
            const origem = sites[de];
            const destino = sites[para];
            const D = DURACAO_TROCA;
            const sai = 'cubic-bezier(.2, 0, 0, 1)';       // desacelera: encolher e crescer
            const meio = 'cubic-bezier(.4, 0, .2, 1)';      // contorno e rolagem da grade
            // Mede com a grade aberta e sem transformações; tudo em px relativos à tela
            abas.classList.add('aberta');
            const ref = tela.getBoundingClientRect();
            const rel = (el) => { const r = el.getBoundingClientRect(); return { x: r.left - ref.left, y: r.top - ref.top, w: r.width, h: r.height }; };
            const cheio = rel(origem);
            const baixoGrade = abas.querySelector('.ce-abas-baixo').offsetHeight;
            const vistaBaixo = tela.clientHeight - baixoGrade;
            const folga = parseFloat(getComputedStyle(listaAbas).rowGap) || 8;
            const fimLista = Math.max(...cartoes.map(c => rel(c.aba).y + rel(c.aba).h));
            const rolagemMax = Math.max(0, fimLista + folga - vistaBaixo);
            const rolagemPara = (i) => Math.max(0, Math.min(rolagemMax, rel(cartoes[i].aba).y + rel(cartoes[i].aba).h + folga - vistaBaixo));
            const offDe = rolagemPara(de);
            const offPara = rolagemPara(para);
            const raio = parseFloat(getComputedStyle(cartoes[de].corpo).borderTopLeftRadius) || 0;
            // A mini-página reduzida até o tamanho do corpo do cartão (a parte de baixo é cortada, como no Chrome)
            const noCartao = (i, off) => {
                const g = rel(cartoes[i].corpo);
                const esc = g.w / cheio.w;
                return {
                    transform: 'translate(' + (g.x - cheio.x) + 'px, ' + (g.y - off - cheio.y) + 'px) scale(' + esc + ')',
                    clipPath: 'inset(0px 0px ' + Math.max(0, cheio.h - g.h / esc) + 'px 0px round ' + (raio / esc) + 'px)'
                };
            };
            const naTela = { transform: 'translate(0px, 0px) scale(1)', clipPath: 'inset(0px 0px 0px 0px round 0px)' };
            const abaDe = cartoes[de].aba;
            const abaPara = cartoes[para].aba;
            Object.assign(selecaoAba.style, { left: abaDe.offsetLeft + 'px', top: abaDe.offsetTop + 'px', width: abaDe.offsetWidth + 'px', height: abaDe.offsetHeight + 'px' });
            const dxSel = abaPara.offsetLeft - abaDe.offsetLeft;
            const dySel = abaPara.offsetTop - abaDe.offsetTop;

            origem.classList.add('animando');
            origem.style.zIndex = '2';
            destino.classList.add('animando');
            destino.style.zIndex = '3';
            const animacoes = [];
            const nova = (el, quadros, opcoes) => { const a = el.animate(quadros, opcoes); animacoes.push(a); return a; };
            // Grade: aparece junto com o encolher, some no fim do crescer
            nova(abas, [{ opacity: 0, offset: 0 }, { opacity: 1, offset: 200 / D }, { opacity: 1, offset: 960 / D }, { opacity: 0, offset: 1 }], { duration: D, fill: 'both' });
            // Barras do Chrome saem logo no começo e voltam no fim
            [barraCima, barraBaixo].forEach(b => nova(b, [{ opacity: 1, offset: 0 }, { opacity: 0, offset: 140 / D }, { opacity: 0, offset: 960 / D }, { opacity: 1, offset: 1 }], { duration: D, fill: 'both' }));
            // 1. a página atual encolhe até o cartão; quando a grade rola, ela vai junto (o mesmo movimento da grade)
            // e some logo depois, deixando à mostra o cartão dela. Se ficasse parada, apareceria em cima do cartão errado.
            nova(origem, [
                Object.assign({ offset: 0, easing: sai }, naTela),
                Object.assign({ offset: 400 / D, easing: meio }, noCartao(de, offDe)),
                Object.assign({ offset: 720 / D }, noCartao(de, offPara)),
                Object.assign({ offset: 1 }, noCartao(de, offPara))
            ], { duration: D, fill: 'both' });
            nova(origem, [
                { opacity: 1, offset: 0 },
                { opacity: 1, offset: 400 / D },
                { opacity: 0, offset: 520 / D },
                { opacity: 0, offset: 1 }
            ], { duration: D, fill: 'both' });
            // 2. o contorno azul passa para o cartão do próximo, e a grade rola junto
            nova(selecaoAba, [
                { opacity: 0, transform: 'translate(0px, 0px)', offset: 0 },
                { opacity: 1, transform: 'translate(0px, 0px)', offset: 260 / D },
                { opacity: 1, transform: 'translate(0px, 0px)', offset: 400 / D, easing: meio },
                { opacity: 1, transform: 'translate(' + dxSel + 'px, ' + dySel + 'px)', offset: 720 / D },
                { opacity: 1, transform: 'translate(' + dxSel + 'px, ' + dySel + 'px)', offset: 800 / D },
                { opacity: 0, transform: 'translate(' + dxSel + 'px, ' + dySel + 'px)', offset: 1 }
            ], { duration: D, fill: 'both' });
            nova(listaAbas, [
                { transform: 'translateY(' + (-offDe) + 'px)', offset: 0 },
                { transform: 'translateY(' + (-offDe) + 'px)', offset: 400 / D, easing: meio },
                { transform: 'translateY(' + (-offPara) + 'px)', offset: 720 / D },
                { transform: 'translateY(' + (-offPara) + 'px)', offset: 1 }
            ], { duration: D, fill: 'both' });
            // 3. o próximo cresce do cartão até a tela (invisível até a hora dele)
            nova(destino, [
                Object.assign({ opacity: 0, offset: 0 }, noCartao(para, offPara)),
                Object.assign({ opacity: 1, offset: .001 }, noCartao(para, offPara)),
                Object.assign({ opacity: 1, offset: 1 }, naTela)
            ], { delay: 780, duration: 420, easing: sai, fill: 'both' });

            const id = ++idTroca;
            troca = { id, animacoes };
            // O endereço muda quando as barras estão escondidas
            esperar(() => { if (troca && troca.id === id && endereco) endereco.textContent = destino.dataset.endereco || ''; }, 500);
            Promise.all(animacoes.map(a => a.finished)).then(() => {
                if (!troca || troca.id !== id) return;
                troca = null;
                animacoes.forEach(a => a.cancel());
                limparTroca();
            }).catch(() => { /* animação cancelada por uma nova troca: quem cancelou já arrumou o estado */ });
        };

        const atualizar = (parcial) => {
            if (!parcial) {
                sites.forEach((site, i) => {
                    const ativo = i === atual;
                    site.classList.toggle('ativo', ativo);
                    site.setAttribute('aria-hidden', String(!ativo));
                });
            }
            pontos.forEach((ponto, i) => {
                ponto.classList.remove('ativo');
                if (i === atual) {
                    void ponto.offsetWidth; // reinicia a barrinha do zero
                    ponto.classList.add('ativo');
                }
                ponto.setAttribute('aria-current', String(i === atual));
            });
            if (endereco && !parcial) endereco.textContent = sites[atual].dataset.endereco || '';
        };

        const mostrar = (i) => {
            const novo = (i + sites.length) % sites.length;
            pararRolagem();
            encerrarTroca(); // uma troca ainda em andamento termina na hora, no exemplo de destino
            const anterior = atual;
            atual = novo;
            // O exemplo que entra começa sempre do topo
            documentoDe(sites[atual])?.defaultView?.scrollTo(0, 0);
            if (vistaPronta) carregarVez();
            // Sem animar: "menos movimento" no sistema, animações pausadas no rodapé, aba escondida ou tela sem tamanho (nada a medir)
            if (novo !== anterior && podeAnimar && !semMovimento && !pausadas() && !document.hidden && tela.clientWidth > 0 && tela.clientHeight > 0) {
                atualizar(true);
                trocarAba(anterior, novo);
            } else {
                atualizar();
            }
        };

        const agendar = () => {
            cancelar(timer);
            timer = null;
            pararRolagem();
            if (semMovimento || pausas.size) return;
            inicio = performance.now();
            timer = esperar(() => {
                restante = INTERVALO;
                mostrar(atual + 1);
                agendar();
            }, restante);
            planejarRolagem();
        };

        const pausar = (motivo) => {
            if (!pausas.size && timer) {
                cancelar(timer);
                timer = null;
                restante = Math.max(0, restante - (performance.now() - inicio));
            }
            pararRolagem();
            pausas.add(motivo);
            vitrine.classList.add('pausado');
        };
        const retomar = (motivo) => {
            if (!pausas.delete(motivo) || pausas.size) return;
            vitrine.classList.remove('pausado');
            agendar();
        };

        // Toque no celular: pausa a troca enquanto a pessoa olha e retoma 8 s depois do último toque
        let timerToque = null;
        vitrine.addEventListener('pointerdown', (e) => {
            if (e.pointerType !== 'touch') return;
            pausar('toque');
            cancelar(timerToque);
            timerToque = esperar(() => retomar('toque'), ESPERA_GESTO);
        });

        pontos.forEach((ponto, i) => ponto.addEventListener('click', () => {
            restante = INTERVALO;
            mostrar(i);
            agendar();
        }));

        const moldura = vitrine.querySelector('.ce-moldura');
        // Só o mouse de verdade pausa: no celular, tocar no aparelho não deve travar a troca
        if (moldura) {
            moldura.addEventListener('pointerenter', (e) => { if (e.pointerType === 'mouse') pausar('mouse'); });
            moldura.addEventListener('pointerleave', (e) => {
                if (e.pointerType !== 'mouse') return;
                if (preso || pausas.has('rolagem')) mouseSaiu();
                else retomar('mouse');
            });
        }
        // Clicar numa bolinha também dá foco a ela; só o foco vindo do teclado (Tab) pausa.
        // Safari antigo (antes do 15.4) não conhece :focus-visible e daria erro: lá, qualquer foco pausa.
        const focoTeclado = (el) => { try { return el.matches(':focus-visible'); } catch (e) { return true; } };
        vitrine.addEventListener('focusin', (e) => { if (focoTeclado(e.target)) pausar('foco'); });
        vitrine.addEventListener('focusout', (e) => { if (!vitrine.contains(e.relatedTarget)) retomar('foco'); });
        document.addEventListener('visibilitychange', () => (document.hidden ? pausar('aba') : retomar('aba')));

        // Botão "Pausar animações" do rodapé: o movimento.js liga e desliga a classe na raiz
        // (a raiz tem outras classes que mudam; só reage quando o estado de pausa muda de fato)
        const conferirBotao = () => {
            if (pausadas() === pausas.has('botao')) return;
            if (pausadas()) pausar('botao');
            else { restante = INTERVALO; retomar('botao'); }
        };
        new MutationObserver(conferirBotao).observe(raiz, { attributes: true, attributeFilter: ['class'] });
        conferirBotao();

        // Botão de pausa ao lado das bolinhas: quem não quer o carrossel girando para sem ir ao rodapé
        const caixaPontos = vitrine.querySelector('.ce-pontos');
        if (caixaPontos) {
            const ns = 'http://www.w3.org/2000/svg';
            const icone = (classe, formas) => {
                const svg = document.createElementNS(ns, 'svg');
                svg.setAttribute('viewBox', '0 0 16 16');
                svg.setAttribute('class', classe);
                svg.setAttribute('aria-hidden', 'true');
                formas.forEach(([x, y, w, h]) => {
                    const r = document.createElementNS(ns, 'rect');
                    ['x', 'y', 'width', 'height'].forEach((k, i) => r.setAttribute(k, [x, y, w, h][i]));
                    svg.appendChild(r);
                });
                return svg;
            };
            // O botão já vem no HTML (aparece junto com as bolinhas, sem a página pular); se faltar, é criado aqui
            let botaoPausa = caixaPontos.querySelector('.ce-pausa');
            const veioNoHtml = !!botaoPausa;
            if (!botaoPausa) {
                botaoPausa = document.createElement('button');
                botaoPausa.type = 'button';
                botaoPausa.className = 'ce-pausa';
                botaoPausa.setAttribute('aria-pressed', 'false');
                botaoPausa.setAttribute('aria-label', 'Pausar a troca dos exemplos');
                botaoPausa.appendChild(icone('i-pausar', [[3, 2, 3.5, 12], [9.5, 2, 3.5, 12]]));
                botaoPausa.appendChild(icone('i-tocar', [[4, 2, 3, 12], [7, 4, 3, 8], [10, 6, 2.5, 4]]));
            }
            botaoPausa.addEventListener('click', () => {
                const parar = botaoPausa.getAttribute('aria-pressed') !== 'true';
                botaoPausa.setAttribute('aria-pressed', String(parar));
                botaoPausa.setAttribute('aria-label', parar ? 'Retomar a troca dos exemplos' : 'Pausar a troca dos exemplos');
                if (parar) pausar('pausa-botao');
                else { restante = INTERVALO; retomar('pausa-botao'); }
            });
            if (!veioNoHtml) caixaPontos.appendChild(botaoPausa);
        }

        /* Carregamento só quando o celular chega perto da tela (200 px antes) e pausa enquanto ele está longe:
           assim ninguém baixa mini-páginas que não vai ver e o carrossel não gira escondido. */
        let vistaPronta = !temObservador;
        if (temObservador) {
            new IntersectionObserver((entradas) => {
                const perto = entradas[entradas.length - 1].isIntersecting;
                if (perto) {
                    vistaPronta = true;
                    carregarVez();
                    retomar('fora');
                } else {
                    pausar('fora');
                }
            }, { rootMargin: '200px' }).observe(vitrine);
        } else {
            carregarVez();
        }

        atualizar();
        agendar();
    }

    // Para quem é: transforma a lista de ramos em botões e mostra, para o ramo escolhido,
    // o que o site teria e um botão de WhatsApp com a mensagem já escrita
    const seg = document.querySelector('[data-seg]');
    const itensSeg = seg ? [...seg.querySelectorAll('.seg-lista > li')] : [];
    if (seg && itensSeg.length) {
        const dados = itensSeg.map((li) => ({
            titulo: li.querySelector('strong').textContent,
            texto: li.querySelector('span').textContent,
            ramo: li.dataset.ramo,
            wa: li.dataset.wa
        }));
        const grupo = document.createElement('div');
        grupo.className = 'seg-botoes';
        grupo.setAttribute('role', 'group');
        grupo.setAttribute('aria-label', seg.dataset.grupo || '');
        const painel = document.createElement('div');
        painel.className = 'seg-painel';
        painel.setAttribute('aria-live', 'polite');
        const nome = document.createElement('h3');
        const texto = document.createElement('p');
        const acao = document.createElement('a');
        acao.className = 'btn btn-sol';
        acao.dataset.evento = 'whatsapp_contato';
        acao.target = '_blank';
        acao.rel = 'noopener noreferrer';
        painel.append(nome, texto, acao);

        const botoes = dados.map((d, i) => {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'seg-botao';
            b.textContent = d.titulo;
            b.setAttribute('aria-pressed', 'false');
            b.addEventListener('click', () => escolher(i, true));
            grupo.appendChild(b);
            return b;
        });

        let atual = -1;
        function escolher(i, animar) {
            if (i === atual) return;
            atual = i;
            botoes.forEach((b, k) => b.setAttribute('aria-pressed', k === i ? 'true' : 'false'));
            nome.textContent = dados[i].titulo;
            texto.textContent = dados[i].texto;
            acao.textContent = (seg.dataset.botao || '').replace('{ramo}', dados[i].ramo);
            const aviso = document.createElement('span');
            aviso.className = 'sr-only';
            aviso.textContent = ' (abre em nova aba)';
            acao.appendChild(aviso);
            acao.href = dados[i].wa;
            if (animar && !semMovimento && painel.animate) {
                painel.animate([{ opacity: 0, transform: 'translateY(6px)' }, { opacity: 1, transform: 'none' }],
                    { duration: 220, easing: 'cubic-bezier(.22,1,.36,1)' });
            }
        }

        // Escolhe o primeiro ramo antes de o painel entrar na página, para o leitor de tela não anunciá-lo ao carregar
        escolher(0, false);
        seg.classList.add('seg-ativo');
        seg.append(grupo, painel);
    }

    // Estado inicial da rolagem (por exemplo, ao recarregar no meio da página)
    processarRolagem();
})();
