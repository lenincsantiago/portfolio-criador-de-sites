<?php
// Roteador temporário (só para auditoria): imita as regras do .htaccess no servidor embutido do PHP.
$caminho = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$raiz = $_SERVER["DOCUMENT_ROOT"];
if (preg_match('#^/(includes|data|tests|ferramentas|referencias|logs-site)(/|$)#', $caminho) || preg_match('#(^|/)\.#', $caminho)) {
    http_response_code(403); echo 'Forbidden'; return true;
}
if ($caminho === '/sitemap.xml') { require $raiz . '/sitemap.php'; return true; }
if ($caminho === '/robots.txt')  { require $raiz . '/robots.php'; return true; }
if ($caminho === '/llms.txt')    { require $raiz . '/llms.php'; return true; }
if (preg_match('#^/servicos/([a-z0-9-]+)/?$#', $caminho, $m)) {
    $_GET['s'] = $m[1]; $_SERVER['SCRIPT_NAME'] = '/servico.php'; require $raiz . '/servico.php'; return true;
}
if ($caminho === '/' || $caminho === '') { require $raiz . '/index.php'; return true; }
if (is_file($raiz . $caminho)) { return false; }
$_SERVER['SCRIPT_NAME'] = '/404.php'; require $raiz . '/404.php'; return true;
