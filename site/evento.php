<?php
declare(strict_types=1);
// Recebe cliques dos botões do site (contagem anônima, sem dados pessoais).
require __DIR__ . '/includes/dados.php';

header('Cache-Control: no-store');
header('X-Robots-Tag: noindex');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Só nomes da lista EVENTOS_VALIDOS são contados; qualquer outro é ignorado
    registrar_evento(id_seguro($_POST['evento'] ?? '', 40));
}
http_response_code(204);
