// Roda no <head>, antes de a página aparecer: marca que há JavaScript.
// Assim o que depende do JavaScript (bolinhas e botão de pausa do celular, menu) já nasce no lugar certo,
// sem esperar o main.js, que carrega depois. Não pode ser inline: a política de segurança só aceita scripts do próprio site.
document.documentElement.classList.add('js');
