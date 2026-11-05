<?php
declare(strict_types=1);

// Erros nunca aparecem para o visitante: vão só para o log do servidor.
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
// A Hostinger roda em UTC; sem isto, horários de mensagens, e-mails e registros saem 3 horas adiantados
date_default_timezone_set('America/Sao_Paulo');

// Data de uma mensagem no horário de Brasília. Usa o instante gravado ('ts') quando existe,
// o que também corrige mensagens antigas que foram gravadas com o horário do servidor.
function data_mensagem(array $m): string
{
    $ts = $m['ts'] ?? null;
    return is_int($ts) && $ts > 0 ? date('d/m/Y H:i', $ts) : (string) ($m['data'] ?? '');
}
error_reporting(E_ALL);

define('RAIZ', dirname(__DIR__));
define('ARQ_DADOS', RAIZ . '/data/site.json');
define('ARQ_TENTATIVAS', RAIZ . '/data/tentativas.json');
define('ARQ_MENSAGENS', RAIZ . '/data/mensagens.json');
define('ARQ_EVENTOS', RAIZ . '/data/eventos.json');
define('PASTA_PROJETOS', 'assets/img/projetos/');
// Registro de eventos de segurança: fica fora da pasta pública (um nível acima de public_html).
// Se não for possível escrever lá, usa data/logs/, que também é bloqueada pelo .htaccess.
define('PASTA_LOG_SEGURANCA', dirname(RAIZ) . '/logs-site');

// Falha inesperada: registra no log e mostra uma mensagem simples, sem detalhes técnicos.
set_exception_handler(static function (Throwable $erro): void {
    error_log('[site] ' . get_class($erro) . ': ' . $erro->getMessage() . ' em ' . $erro->getFile() . ':' . $erro->getLine());
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
    }
    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="robots" content="noindex">'
        . '<title>Erro temporário</title></head><body>'
        . '<h1>Algo deu errado por aqui.</h1><p>Tente de novo em alguns instantes. Se o problema continuar, fale comigo pelo WhatsApp.</p>'
        . '<p><a href="/">Voltar para o início</a></p></body></html>';
});

/** Configuração (config.php), lida uma única vez por requisição. */
function config(): array
{
    static $cfg = null;
    if ($cfg === null) {
        $lido = is_file(RAIZ . '/config.php') ? require RAIZ . '/config.php' : [];
        $cfg = is_array($lido) ? $lido : [];
    }
    return $cfg;
}

function carregar_dados(): array
{
    static $cache = null;
    if ($cache === null) {
        $json = @file_get_contents(ARQ_DADOS);
        $dados = $json ? json_decode($json, true) : null;
        if ($json && !is_array($dados)) {
            error_log('[site] site.json inválido: ' . json_last_error_msg());
        }
        $cache = is_array($dados) ? $dados : [];
    }
    return $cache;
}

/**
 * Grava um JSON por inteiro em arquivo temporário e troca de uma vez (rename é atômico),
 * para nunca deixar o arquivo pela metade. Retorna false se não conseguiu.
 */
function gravar_json_atomico(string $arq, array $dados, bool $bonito = false): bool
{
    $opcoes = JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | ($bonito ? JSON_PRETTY_PRINT : 0);
    $json = json_encode($dados, $opcoes);
    if ($json === false) {
        error_log('[site] JSON inválido ao gravar ' . basename($arq));
        return false;
    }
    $tmp = $arq . '.' . bin2hex(random_bytes(4)) . '.tmp';
    if (@file_put_contents($tmp, $json, LOCK_EX) === false) {
        error_log('[site] Sem permissão de escrita em ' . dirname($arq));
        return false;
    }
    if (!@rename($tmp, $arq)) {
        @unlink($tmp);
        error_log('[site] Não consegui substituir ' . basename($arq));
        return false;
    }
    return true;
}

/** Abre a trava de um arquivo JSON (arquivo .lock ao lado). */
function travar(string $arq)
{
    $trava = @fopen($arq . '.lock', 'c');
    if ($trava) {
        flock($trava, LOCK_EX);
    }
    return $trava;
}

function destravar($trava): void
{
    if ($trava) {
        flock($trava, LOCK_UN);
        fclose($trava);
    }
}

function salvar_dados(array $dados): bool
{
    $trava = travar(ARQ_DADOS);
    $ok = gravar_json_atomico(ARQ_DADOS, $dados, true);
    destravar($trava);
    return $ok;
}

function e(mixed $valor): string
{
    return htmlspecialchars(is_scalar($valor) ? (string) $valor : '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Só aceita texto: listas, objetos e números estranhos viram vazio. Remove caracteres de controle. */
function limpar(mixed $texto, int $max = 300): string
{
    if (!is_string($texto)) {
        return '';
    }
    // Mantém quebras de linha e tabulação; remove os demais caracteres de controle
    $texto = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $texto) ?? '';
    $texto = str_replace("\r\n", "\n", $texto);
    return mb_substr(trim($texto), 0, $max, 'UTF-8');
}

/** Texto em uma linha só (sem quebras), para nomes, assuntos e cabeçalhos. */
function limpar_linha(mixed $texto, int $max = 200): string
{
    return trim(preg_replace('/\s+/u', ' ', limpar($texto, $max)) ?? '');
}

function post_txt(string $chave, int $max = 300): string
{
    return limpar($_POST[$chave] ?? '', $max);
}

function get_txt(string $chave, int $max = 80): string
{
    return limpar($_GET[$chave] ?? '', $max);
}

/** Identificadores internos (ids de itens, nomes de listas e ações): só letras, números, _ e -. */
function id_seguro(mixed $valor, int $max = 40): string
{
    $v = limpar($valor, $max);
    return preg_match('/^[A-Za-z0-9_-]+$/', $v) ? $v : '';
}

function validar_url(mixed $url): ?string
{
    if (!is_string($url)) {
        return null;
    }
    $url = trim($url);
    if ($url === '' || $url === '#') {
        return '#';
    }
    $esquema = parse_url($url, PHP_URL_SCHEME);
    if (in_array($esquema, ['http', 'https'], true) && filter_var($url, FILTER_VALIDATE_URL)) {
        return $url;
    }
    return null;
}

function novo_id(): string
{
    return bin2hex(random_bytes(6));
}

function ip_cliente(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}

/** IP parcial para o registro de segurança: zera o fim do endereço (IPv4 /24, IPv6 /48). */
function ip_parcial(string $ip): string
{
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return preg_replace('/\.\d+$/', '.0', $ip) ?? '0.0.0.0';
    }
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        $partes = explode(':', inet_ntop((string) inet_pton($ip)) ?: '::');
        return implode(':', array_slice(array_pad($partes, 3, '0'), 0, 3)) . '::';
    }
    return '0.0.0.0';
}

/**
 * Lê, altera e grava um arquivo JSON dentro de uma trava, para evitar perda de dados entre acessos simultâneos.
 * Se o arquivo existir e estiver corrompido, não grava por cima (assim nada é apagado sem querer).
 * $gravou diz se a alteração chegou mesmo ao disco.
 */
function alterar_json(string $arq, callable $fn, ?bool &$gravou = null): mixed
{
    $gravou = false;
    $trava = travar($arq);
    $dados = [];
    $corrompido = false;
    if (is_file($arq)) {
        $bruto = (string) @file_get_contents($arq);
        $lido = $bruto === '' ? [] : json_decode($bruto, true);
        if (is_array($lido)) {
            $dados = $lido;
        } else {
            $corrompido = true;
            error_log('[site] ' . basename($arq) . ' está corrompido; nada foi gravado por cima.');
        }
    }
    $resultado = $fn($dados);
    if (!$corrompido) {
        $gravou = gravar_json_atomico($arq, $dados);
    }
    destravar($trava);
    return $resultado;
}

/**
 * Conta uma tentativa para a chave. Retorna false se o limite já foi atingido na janela.
 * Usado no login (limite de erros), no formulário de contato e na contagem de cliques.
 */
function permitir_tentativa(string $chave, int $limite, int $janela, bool $deNovo = true): bool
{
    $permitido = (bool) alterar_json(ARQ_TENTATIVAS, function (array &$todas) use ($chave, $limite, $janela): bool {
        $agora = time();
        foreach ($todas as $k => $r) {
            // Cada registro guarda a própria janela; registros vencidos são apagados
            if (!is_array($r) || $agora - (int) ($r['inicio'] ?? 0) > (int) ($r['janela'] ?? 3600)) {
                unset($todas[$k]);
            }
        }
        $reg = $todas[$chave] ?? ['n' => 0, 'inicio' => $agora, 'janela' => $janela];
        if ($reg['n'] >= $limite) {
            return false;
        }
        $reg['n']++;
        $todas[$chave] = $reg;
        return true;
    }, $gravou);
    if ($gravou) {
        return $permitido;
    }
    // Não conseguiu gravar a contagem. Se o arquivo estragou, ele só guarda contagens de 1 hora:
    // é posto de lado e começa do zero. Se o problema continuar (pasta sem permissão, disco cheio),
    // recusa, para o limite nunca ficar desligado sem ninguém perceber.
    if ($deNovo && is_file(ARQ_TENTATIVAS)) {
        @rename(ARQ_TENTATIVAS, ARQ_TENTATIVAS . '.corrompido');
        return permitir_tentativa($chave, $limite, $janela, false);
    }
    error_log('[site] Não consegui gravar ' . basename(ARQ_TENTATIVAS) . '; tentativa recusada por segurança.');
    return false;
}

/** Diz se a chave já chegou ao limite na janela atual, sem contar mais uma tentativa. */
function tentativas_esgotadas(string $chave, int $limite): bool
{
    $bruto = @file_get_contents(ARQ_TENTATIVAS);
    $todas = $bruto ? json_decode($bruto, true) : null;
    $reg = is_array($todas) ? ($todas[$chave] ?? null) : null;
    if (!is_array($reg) || time() - (int) ($reg['inicio'] ?? 0) > (int) ($reg['janela'] ?? 3600)) {
        return false;
    }
    return (int) ($reg['n'] ?? 0) >= $limite;
}

function zerar_tentativas(string $chave): void
{
    alterar_json(ARQ_TENTATIVAS, function (array &$todas) use ($chave): void {
        unset($todas[$chave]);
    });
}

/**
 * Chave temporária derivada do IP, para limitar abusos sem guardar o IP em si.
 * O sal muda todo dia e fica só em data/ (bloqueada para o navegador).
 */
function chave_anonima(string $prefixo): string
{
    $arq = RAIZ . '/data/sal.json';
    $hoje = date('Y-m-d');
    $sal = alterar_json($arq, function (array &$s) use ($hoje): string {
        if (($s['dia'] ?? '') !== $hoje || empty($s['sal'])) {
            $s = ['dia' => $hoje, 'sal' => bin2hex(random_bytes(16))];
        }
        return (string) $s['sal'];
    });
    // IPv6: a mesma conexão costuma trocar o fim do endereço, então conta pelo bloco /64
    $ip = ip_cliente();
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        $ip = inet_ntop(substr((string) inet_pton($ip), 0, 8) . str_repeat("\0", 8)) ?: $ip;
    }
    return $prefixo . ':' . substr(hash_hmac('sha256', $ip, (string) $sal), 0, 16);
}

/** Insere ou substitui um item de uma lista pelo id. */
function salvar_item(array &$lista, array $item): void
{
    foreach ($lista as $i => $existente) {
        if (($existente['id'] ?? '') === $item['id']) {
            $lista[$i] = $item;
            return;
        }
    }
    $lista[] = $item;
}

function remover_item(array &$lista, string $id): ?array
{
    $removido = buscar_item($lista, $id);
    $lista = array_values(array_filter($lista, fn($i) => ($i['id'] ?? '') !== $id));
    return $removido;
}

function buscar_item(array $lista, string $id): ?array
{
    if ($id === '') {
        return null;
    }
    foreach ($lista as $item) {
        if (is_array($item) && ($item['id'] ?? '') === $id) {
            return $item;
        }
    }
    return null;
}

/** Recebe um upload de imagem, recorta em 4:3, reduz e salva como WebP (ou JPG). */
function processar_imagem_upload(array $arquivo, string $id): array
{
    $codigo = $arquivo['error'] ?? UPLOAD_ERR_NO_FILE;
    if (!is_int($codigo) || !is_string($arquivo['tmp_name'] ?? null)) {
        return ['ok' => false, 'erro' => 'Envie uma imagem por vez.'];
    }
    if ($codigo === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'caminho' => null];
    }
    if ($codigo === UPLOAD_ERR_INI_SIZE || $codigo === UPLOAD_ERR_FORM_SIZE) {
        return ['ok' => false, 'erro' => 'A imagem passou do tamanho permitido pelo servidor. Use uma imagem de até 4 MB.'];
    }
    if ($codigo !== UPLOAD_ERR_OK || !is_uploaded_file($arquivo['tmp_name'])) {
        return ['ok' => false, 'erro' => 'Falha no envio da imagem. Tente de novo.'];
    }
    if ((int) ($arquivo['size'] ?? 0) > 4 * 1024 * 1024) {
        return ['ok' => false, 'erro' => 'A imagem tem mais de 4 MB.'];
    }
    if (!extension_loaded('gd')) {
        return ['ok' => false, 'erro' => 'O servidor não tem suporte a imagens (extensão GD). Peça para ativar no hPanel.'];
    }

    $info = @getimagesize($arquivo['tmp_name']);
    if (!$info) {
        return ['ok' => false, 'erro' => 'O arquivo enviado não é uma imagem válida.'];
    }
    // Confere as dimensões antes de abrir a imagem, para não estourar a memória (limite de 12 megapixels)
    if ($info[0] < 1 || $info[1] < 1 || $info[0] * $info[1] > 12000000) {
        return ['ok' => false, 'erro' => 'A imagem tem dimensões grandes demais (limite de 12 megapixels). Reduza e tente de novo.'];
    }

    $src = match ($info[2]) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($arquivo['tmp_name']),
        IMAGETYPE_PNG  => @imagecreatefrompng($arquivo['tmp_name']),
        IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($arquivo['tmp_name']) : false,
        default        => false,
    };
    if (!$src) {
        return ['ok' => false, 'erro' => 'Use uma imagem JPG, PNG ou WebP.'];
    }

    // Recorta no centro no formato 4:3 e reduz para no máximo 900 px de largura (900 x 675)
    $w = imagesx($src);
    $h = imagesy($src);
    $proporcao = 4 / 3;
    if ($w / $h > $proporcao) {
        $cw = (int) round($h * $proporcao);
        $ch = $h;
        $cx = (int) floor(($w - $cw) / 2);
        $cy = 0;
    } else {
        $cw = $w;
        $ch = (int) round($w / $proporcao);
        $cx = 0;
        $cy = (int) floor(($h - $ch) / 2);
    }
    $nw = max(1, min(900, $cw));
    $nh = max(1, (int) round($nw * 3 / 4));

    // A imagem é sempre redesenhada do zero: qualquer conteúdo escondido no arquivo original é descartado
    $dst = imagecreatetruecolor($nw, $nh);
    imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
    imagecopyresampled($dst, $src, 0, 0, $cx, $cy, $nw, $nh, $cw, $ch);

    $pasta = RAIZ . '/' . PASTA_PROJETOS;
    if (!is_dir($pasta) && !@mkdir($pasta, 0755, true)) {
        return ['ok' => false, 'erro' => 'Não consegui criar a pasta de imagens. Verifique a permissão de assets/img/projetos/.'];
    }

    $extensao = function_exists('imagewebp') ? '.webp' : '.jpg';
    // Nome aleatório: um envio nunca substitui um arquivo que já existe
    do {
        $nomeArq = 'projeto-' . preg_replace('/[^a-z0-9]/i', '', $id) . '-' . bin2hex(random_bytes(4)) . $extensao;
    } while (file_exists($pasta . $nomeArq));

    $salvou = $extensao === '.webp'
        ? imagewebp($dst, $pasta . $nomeArq, 82)
        : imagejpeg($dst, $pasta . $nomeArq, 85);

    if (!$salvou) {
        return ['ok' => false, 'erro' => 'Não consegui salvar a imagem. Verifique a permissão de escrita de assets/img/projetos/.'];
    }
    return ['ok' => true, 'caminho' => PASTA_PROJETOS . $nomeArq];
}

function apagar_imagem(?string $caminho): void
{
    if (!$caminho || !str_starts_with($caminho, PASTA_PROJETOS)) {
        return;
    }
    // Só apaga arquivos que estão dentro da pasta de projetos (impede apagar outros arquivos)
    $pasta = realpath(RAIZ . '/' . PASTA_PROJETOS);
    $arq = realpath(RAIZ . '/' . $caminho);
    if ($pasta && $arq && str_starts_with($arq, $pasta . DIRECTORY_SEPARATOR) && is_file($arq)) {
        @unlink($arq);
    }
}

function conexao_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
}

/**
 * Token do formulário de contato sem sessão: momento de criação + assinatura com um segredo fixo do site.
 * Assim a página inicial não cria cookie para quem só lê.
 */
function token_formulario(): string
{
    $agora = (string) time();
    return $agora . '.' . hash_hmac('sha256', 'contato|' . $agora, segredo_site());
}

function token_formulario_ok(mixed $token): bool
{
    if (!is_string($token) || !preg_match('/^(\d{10})\.([0-9a-f]{64})$/', $token, $m)) {
        return false;
    }
    $idade = time() - (int) $m[1];
    if ($idade < 0 || $idade > TOKEN_FORM_VALIDADE) {
        return false;
    }
    return hash_equals(hash_hmac('sha256', 'contato|' . $m[1], segredo_site()), $m[2]);
}

/** Segredo do site, criado uma única vez em data/ (bloqueada para o navegador). Diferente do sal diário. */
function segredo_site(): string
{
    static $segredo = null;
    if ($segredo !== null) {
        return $segredo;
    }
    $arq = RAIZ . '/data/segredo.json';
    $valido = static fn($s): bool => is_string($s) && strlen($s) === 64;
    // Caminho comum: o segredo já existe e só é lido, sem trava nem gravação a cada visita
    $lido = json_decode((string) @file_get_contents($arq), true);
    if ($valido($lido['segredo'] ?? null)) {
        return $segredo = $lido['segredo'];
    }
    $novo = (string) alterar_json($arq, function (array &$s) use ($valido): string {
        if (!$valido($s['segredo'] ?? null)) {
            $s = ['segredo' => bin2hex(random_bytes(32))];
        }
        return $s['segredo'];
    }, $gravou);
    if ($gravou) {
        return $segredo = $novo;
    }
    // data/ sem permissão de escrita (ou arquivo estragado): um segredo novo a cada visita recusaria
    // todo envio do formulário. Usa um derivado fixo do caminho do site, que só existe no servidor.
    error_log('[site] data/segredo.json não pôde ser gravado; usando segredo derivado do config.php.');
    return $segredo = hash('sha256', 'segredo-formulario|' . (string) RAIZ);
}

/**
 * Cabeçalhos de segurança de todas as respostas HTML (todas as páginas do site).
 * O .htaccess repete parte deles; aqui eles valem também em servidores sem mod_headers.
 */
function cabecalhos_seguranca(): void
{
    if (headers_sent()) {
        return;
    }
    header_remove('X-Powered-By');
    $csp = "default-src 'self'; img-src 'self' data:; style-src 'self'; font-src 'self'; "
        . "script-src 'self'; connect-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'; "
        . "object-src 'none'; manifest-src 'self'";
    if (conexao_https()) {
        $csp .= '; upgrade-insecure-requests';
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
    header('Content-Security-Policy: ' . $csp);
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=()');
    header('Cross-Origin-Opener-Policy: same-origin');
}

/**
 * Registra um evento de segurança (login falho, bloqueio, 2FA) em arquivo fora da pasta pública.
 * Guarda só data, tipo e IP parcial. Um arquivo por mês; arquivos com mais de 3 meses são apagados.
 */
function log_seguranca(string $evento): void
{
    $pasta = PASTA_LOG_SEGURANCA;
    if (!is_dir($pasta) && !@mkdir($pasta, 0750, true)) {
        $pasta = RAIZ . '/data/logs';
        if (!is_dir($pasta) && !@mkdir($pasta, 0750, true)) {
            return;
        }
    }
    $arq = $pasta . '/seguranca-' . date('Y-m') . '.log';
    // Teto de 1 MB por mês: protege o disco se alguém insistir em tentativas
    if (is_file($arq) && filesize($arq) > 1024 * 1024) {
        return;
    }
    $linha = date('c') . ' ' . preg_replace('/[^a-z0-9_]/', '', $evento) . ' ip=' . ip_parcial(ip_cliente()) . "\n";
    @file_put_contents($arq, $linha, FILE_APPEND | LOCK_EX);

    // Rotação simples: apaga meses antigos (guarda o mês atual e os dois anteriores)
    $limite = date('Y-m', strtotime('first day of -2 months'));
    foreach (glob($pasta . '/seguranca-*.log') ?: [] as $antigo) {
        if (preg_match('/seguranca-(\d{4}-\d{2})\.log$/', $antigo, $m) && $m[1] < $limite) {
            @unlink($antigo);
        }
    }
}

/** Guarda uma cópia de cada mensagem do formulário, para não perder nenhuma. */
function registrar_mensagem(array $mensagem): bool
{
    alterar_json(ARQ_MENSAGENS, function (array &$lista) use ($mensagem): void {
        array_unshift($lista, $mensagem);
        $lista = array_slice(filtrar_mensagens_recentes($lista), 0, 200);
    }, $gravou);
    return $gravou;
}

/** Mensagens com mais de 12 meses saem da lista (como diz a política de privacidade). */
function filtrar_mensagens_recentes(array $lista): array
{
    $limite = time() - 365 * 86400;
    return array_values(array_filter($lista, fn($m) => is_array($m) && (int) ($m['ts'] ?? 0) >= $limite));
}

require_once __DIR__ . '/textos.php';

/** Endereço base do site, ex.: https://seudominio.com.br/ */
function url_base(): string
{
    static $base = null;
    if ($base === null) {
        $base = rtrim((string) (config()['dominio'] ?? ''), '/') . '/';
    }
    return $base;
}

/** Endereço da página de um serviço, relativo à raiz do site (ex.: servicos/design-responsivo). */
function url_servico(string $slug): string
{
    return 'servicos/' . rawurlencode($slug);
}

/** Arquivo do site com a data da última alteração no endereço, para o navegador nunca usar uma cópia velha. */
function versao(string $caminho): string
{
    return $caminho . '?v=' . (int) @filemtime(RAIZ . '/' . $caminho);
}

/**
 * Monta uma descrição para o Google de até 160 caracteres: usa o texto principal e,
 * se ficar curto, completa com o complemento. Se passar de 160, termina na última frase
 * completa (ou fica só com o texto principal); só em último caso corta numa palavra, com reticências.
 */
function descricao_meta(string $texto, string $complemento = ''): string
{
    $texto = trim(preg_replace('/\s+/u', ' ', $texto) ?? '');
    $principal = $texto;
    if (mb_strlen($texto) < 120 && $complemento !== '') {
        $texto = rtrim($texto, ' .') . '. ' . trim($complemento);
    }
    if (mb_strlen($texto) > 160) {
        $corte = mb_substr($texto, 0, 160);
        $fimFrase = max((int) mb_strrpos($corte, '. '), (int) mb_strrpos($corte, '! '), (int) mb_strrpos($corte, '? '));
        if ($fimFrase >= 100) {
            $texto = mb_substr($corte, 0, $fimFrase + 1);
        } elseif (mb_strlen($principal) >= 70 && mb_strlen($principal) <= 160) {
            $texto = rtrim($principal, ' .') . '.';
        } else {
            $corte = mb_substr($corte, 0, 159);
            $texto = rtrim(mb_substr($corte, 0, (int) mb_strrpos($corte, ' ')), ' ,.;:') . '…';
        }
    }
    return $texto;
}

function slugify(string $texto): string
{
    $texto = mb_strtolower(trim($texto), 'UTF-8');
    $mapa = [
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'ç' => 'c', 'ñ' => 'n',
    ];
    $texto = strtr($texto, $mapa);
    $texto = preg_replace('/[^a-z0-9]+/', '-', $texto) ?? '';
    return trim($texto, '-') ?: 'servico';
}

/** Gera um slug que não existe ainda na lista. */
function slug_unico(string $base, array $lista): string
{
    $usados = array_column($lista, 'slug');
    $slug = $base;
    $n = 2;
    while (in_array($slug, $usados, true)) {
        $slug = $base . '-' . $n++;
    }
    return $slug;
}

function wa_link(array $site, string $mensagem = ''): string
{
    $numero = preg_replace('/\D/', '', (string) ($site['whatsapp'] ?? '')) ?? '';
    return 'https://wa.me/' . $numero . ($mensagem !== '' ? '?text=' . rawurlencode($mensagem) : '');
}

/** Atributos padrão de links que abrem em outra aba. */
const LINK_EXTERNO = 'target="_blank" rel="noopener noreferrer"';

const EVENTOS_VALIDOS = [
    'whatsapp_topo', 'whatsapp_flutuante', 'whatsapp_contato', 'whatsapp_servico',
    'email_contato', 'formulario_envio', 'ver_projeto',
];

/** Conta um clique. Guarda só totais por dia, sem identificar o visitante. */
function registrar_evento(string $nome): bool
{
    if (!in_array($nome, EVENTOS_VALIDOS, true)) {
        return false;
    }
    if (!permitir_tentativa(chave_anonima('evento'), 120, 3600)) {
        return false;
    }
    return (bool) alterar_json(ARQ_EVENTOS, function (array &$todos) use ($nome): bool {
        $hoje = date('Y-m-d');
        $todos[$hoje][$nome] = (int) ($todos[$hoje][$nome] ?? 0) + 1;
        // Guarda apenas os últimos 90 dias
        $limite = date('Y-m-d', strtotime('-90 days'));
        foreach (array_keys($todos) as $dia) {
            if ($dia < $limite) {
                unset($todos[$dia]);
            }
        }
        return true;
    });
}
