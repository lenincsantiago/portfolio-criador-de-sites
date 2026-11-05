# Modelo de site de portfólio para criação de sites (PHP, sem banco de dados)

Site de uma página para quem vende serviços de criação de sites: apresentação, o problema do
cliente, serviços, como funciona, projetos, perguntas frequentes, formulário de contato, botão de
WhatsApp, um terminal interativo, um assistente de perguntas e um celular que troca de exemplos
de sites como abas do Chrome. Feito com HTML5, CSS3, JavaScript puro e PHP. Não usa banco de
dados: o conteúdo fica em `site/data/site.json` e os textos padrão em `site/includes/textos.php`.

> Este repositório é um **modelo neutro**. Nome, marca, foto, contatos, cidade e projetos são
> exemplos para você trocar pelos seus. Não há painel de administração: você edita os arquivos.

## O que precisa

- PHP 8 ou mais novo (testado com PHP 8.5), com a extensão `mbstring`.
- Hospedagem com Apache/LiteSpeed (o `.htaccess` já vem pronto) ou o servidor embutido do PHP para testar.

## Rodar no seu computador

```bash
cp site/config.exemplo.php site/config.php
php -S 127.0.0.1:8080 -t site roteador-local.php
```

Abra `http://127.0.0.1:8080`. O `roteador-local.php` imita as regras do `.htaccess`
(bloqueio de pastas internas, `sitemap.xml`, `robots.txt`, páginas de serviço).

## Configurar

1. **`site/config.php`**: domínio do site e e-mail que recebe o formulário de contato.
2. **`site/data/site.json`**: abra num editor de texto e troque nome, marca, WhatsApp, e-mail,
   LinkedIn, cidade, o texto "Sobre", serviços, etapas do processo, projetos, perguntas
   frequentes e respostas do assistente. Depois de editar, confira se o arquivo continua sendo
   um JSON válido (por exemplo, abrindo o site e vendo se as seções aparecem).
3. **Títulos, botões e textos das seções**: ficam em `site/includes/textos.php`. Cada linha tem a
   descrição, o tipo e o texto padrão (o último item). Troque só o texto padrão.
4. **Marca nas páginas**: procure por `Sua Marca` nos arquivos `.php` (títulos das páginas) e em
   `site/site.webmanifest`, e troque pelo nome da sua marca. Procure também `Seu Nome` e `Sua Cidade`.
5. **Imagens**: troque `site/assets/img/perfil-*.webp`, `logo-simbolo.svg`, `favicon.svg`,
   os ícones `.png`, `og-image.png` (1200x630) e as imagens em `site/assets/img/projetos/`.
6. **Celular de exemplos**: as mini-páginas ficam em `site/exemplos/` (negócios fictícios) e o
   celular está em `site/index.php` (procure `celular-exemplo`). Troque pelos seus exemplos.
7. **Política de privacidade**: os textos são um **exemplo**. Revise com atenção à LGPD e ao
   seu caso (hospedagem, provedor de e-mail, dados que você coleta).

## Segurança

- Nunca envie ao GitHub o `site/config.php` nem os arquivos de execução de `site/data/`
  (`sal.json`, `tentativas.json`, `eventos.json` e os `.lock`). O `.gitignore` já cuida disso, mas
  confira antes de cada envio.
- Em produção, ao atualizar o site, não apague os arquivos de `data/` que o servidor criou.
- As pastas `site/data/` e `site/includes/` ficam bloqueadas pelo `.htaccess`.

## Estrutura

```
site/                 arquivos publicados no servidor
  index.php           página inicial
  exemplos/           mini-páginas que aparecem no celular da primeira tela
  includes/           funções e textos padrão (bloqueado no navegador)
  data/site.json      conteúdo do site
  assets/             css, js, fontes e imagens
roteador-local.php    roteador para testar com o servidor do PHP
```

## Licença

MIT. Veja o arquivo `LICENSE` e preencha o nome do titular dos direitos.
