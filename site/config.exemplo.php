<?php
// Copie este arquivo para config.php (na mesma pasta) e preencha os dados.
// O config.php NÃO deve ir para o GitHub (já está no .gitignore).

return [
    // Endereço final do site (com https://). Usado em canônico, mapa do site e remetente dos e-mails.
    'dominio' => 'https://seudominio.com.br',

    // Destino de TODAS as mensagens enviadas pelo formulário de contato
    'email_contato' => 'contato@seudominio.com.br',

    // Código da verificação do Google Search Console (só o valor do "content").
    // Deixe vazio até ter o código.
    'google_verificacao' => '',
];
