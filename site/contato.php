<?php
declare(strict_types=1);
require __DIR__ . '/includes/dados.php';

/** Volta para a seção de contato com um dos avisos fixos (nunca com texto vindo do visitante). */
function voltar(string $status): never
{
    $validos = ['ok', 'erro', 'campos', 'limite', 'expirou'];
    $status = in_array($status, $validos, true) ? $status : 'erro';
    header('Location: ./?contato=' . $status . '#contato', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    voltar('erro');
}

// Envio que não veio do próprio site (sem o token da página) é recusado.
if (!token_formulario_ok($_POST['csrf'] ?? null)) {
    voltar('expirou');
}

// Campo escondido preenchido = robô. Responde "ok" sem fazer nada (fica anotado no registro de segurança).
// O nome do campo não lembra nada que o preenchimento automático do navegador conheça (site, url, empresa).
if (post_txt('confirma_humano', 200) !== '') {
    log_seguranca('contato_robo');
    voltar('ok');
}

// Sem o campo "t" (robôs costumam omitir) ou formulário enviado em menos de 3 segundos = robô.
$abriu = (int) post_txt('t', 12);
if ($abriu <= 0 || time() - $abriu < 3) {
    voltar('erro');
}

$nome = limpar_linha($_POST['nome'] ?? '', 100);
$contato = limpar_linha($_POST['contato'] ?? '', 120);
$mensagem = limpar($_POST['mensagem'] ?? '', 1000);
// Opcionais: ramo do negócio e se já tem site (só "sim" ou "não"; qualquer outra coisa vira vazio)
$ramo = limpar_linha($_POST['ramo'] ?? '', 80);
$temSite = ['sim' => 'sim', 'nao' => 'não'][post_txt('tem_site', 3)] ?? '';

if ($nome === '' || $contato === '' || $mensagem === '') {
    voltar('campos');
}

// No máximo 3 mensagens por pessoa (IP anonimizado) e 20 no total por hora: um ataque vindo
// de muitos endereços não enche a caixa de e-mail nem empurra as mensagens reais para fora do painel.
if (!permitir_tentativa(chave_anonima('contato'), 3, 3600) || !permitir_tentativa('contato:todos', 20, 3600)) {
    voltar('limite');
}

$config = config();
$destino = (string) ($config['email_contato'] ?? '');
$destinoValido = (bool) filter_var($destino, FILTER_VALIDATE_EMAIL);

$enviado = false;
if ($destinoValido) {
    $host = preg_replace('/^https?:\/\//', '', rtrim((string) ($config['dominio'] ?? ''), '/')) ?? '';
    $host = preg_replace('/[^a-z0-9.-]/i', '', $host) ?: 'site.local';
    // O nome entra codificado e em pedaços curtos (regra do e-mail), então não consegue quebrar a linha do assunto
    $assunto = mb_encode_mimeheader('Novo contato pelo site: ' . mb_substr($nome, 0, 60), 'UTF-8', 'B', "\r\n");
    $corpo = "Nome: $nome\nContato: $contato\n"
        . ($ramo !== '' ? "Ramo do negócio: $ramo\n" : '')
        . ($temSite !== '' ? "Já tem site: $temSite\n" : '')
        . "\nMensagem:\n$mensagem\n\n--\nEnviado pelo site em " . date('d/m/Y H:i');
    $cabecalhos = [
        'From: Site <noreply@' . $host . '>',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: base64',
        'MIME-Version: 1.0',
    ];
    // Responder ao cliente direto pelo e-mail dele, só quando ele informou um e-mail válido e sem quebras de linha
    if (filter_var($contato, FILTER_VALIDATE_EMAIL) && !preg_match('/[\r\n]/', $contato)) {
        $cabecalhos[] = 'Reply-To: ' . $contato;
    }
    // mail() confirma apenas que o servidor aceitou a mensagem para envio
    // Corpo em base64: mensagem longa sem quebra de linha não é recusada pelo servidor de e-mail.
    // "-f" põe o remetente também no envelope, o que ajuda o e-mail a não cair no spam (SPF).
    $enviado = @mail($destino, $assunto, chunk_split(base64_encode($corpo)), implode("\r\n", $cabecalhos), '-fnoreply@' . $host);
}

// A mensagem é salva sempre, antes de qualquer resultado. Assim nada se perde,
// e o painel mostra se o e-mail foi aceito pelo servidor.
$salvou = registrar_mensagem([
    'id'       => novo_id(),
    'data'     => date('d/m/Y H:i'),
    'ts'       => time(),
    'nome'     => $nome,
    'contato'  => $contato,
    'mensagem' => $mensagem,
    'ramo'     => $ramo,
    'tem_site' => $temSite,
    'email'    => $enviado,
]);

voltar(($enviado || $salvou) ? 'ok' : 'erro');
