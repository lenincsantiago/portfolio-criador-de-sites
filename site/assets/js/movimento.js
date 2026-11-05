(function () {
    'use strict';
    // Controle de movimento do site. O visual não tem mais fundo animado nem efeitos de cursor:
    // sobrou o botão "Pausar animações" do rodapé, para quem quer ler sem nada se mexendo
    // (carrossel do celular de exemplo, entradas das seções e terminal).

    const raiz = document.documentElement;
    const reduzido = window.matchMedia('(prefers-reduced-motion: reduce)');

    // Avisa o main.js que ele pode começar (a partir daqui nada mais precisa ser baixado)
    const avisarPronto = () => {
        if (raiz.dataset.movimento === 'pronto') return;
        raiz.dataset.movimento = 'pronto';
        document.dispatchEvent(new Event('movimento-pronto'));
    };
    avisarPronto();

    // Com "menos movimento" ligado no sistema, não há o que pausar
    if (reduzido.matches) return;

    // A escolha fica guardada só neste navegador
    let pausado = false;
    try { pausado = localStorage.getItem('animacoes') === 'pausadas'; } catch (e) { /* armazenamento bloqueado */ }
    raiz.classList.toggle('animacoes-pausadas', pausado);

    const base = document.querySelector('.rodape-base');
    if (!base) return;
    const caixa = document.createElement('span');
    const botao = document.createElement('button');
    botao.type = 'button';
    botao.className = 'pausar-animacoes';
    const rotular = () => { botao.textContent = pausado ? 'Retomar animações' : 'Pausar animações'; };
    rotular();
    botao.addEventListener('click', () => {
        pausado = !pausado;
        try { localStorage.setItem('animacoes', pausado ? 'pausadas' : 'ativas'); } catch (e) { /* só vale nesta visita */ }
        raiz.classList.toggle('animacoes-pausadas', pausado);
        // Ao retomar, as entradas que já aconteceram nesta visita pulam direto para o fim
        if (!pausado && document.getAnimations) {
            document.getAnimations().forEach(a => {
                if (!a.animationName || !a.effect || a.effect.getComputedTiming().iterations === Infinity) return;
                if (a.timeline && a.timeline !== document.timeline) return;
                const alvo = a.effect.target;
                if (alvo && alvo.closest && alvo.closest('.ce-ponto')) return; // a barrinha do carrossel segue contando
                a.finish();
            });
        }
        rotular();
    });
    caixa.appendChild(botao);
    base.appendChild(caixa);
})();
