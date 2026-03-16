<?php
$urlApi = 'https://jsonplaceholder.typicode.com/users';
$mensagemErro = '';
$usuarios = [];

$contexto = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 10,
    ],
]);

$resposta = @file_get_contents($urlApi, false, $contexto);

if ($resposta === false) {
    $mensagemErro = 'Nao foi possivel consultar a API no momento.';
} else {
    $dados = json_decode($resposta, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($dados)) {
        $mensagemErro = 'Resposta invalida da API.';
    } else {
        $usuarios = $dados;
    }
}

$cssVersion = file_exists(__DIR__ . '/style.css') ? (string) filemtime(__DIR__ . '/style.css') : (string) time();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exercicio 5 - Usuarios da API</title>
    <link rel="stylesheet" href="/Apis/Exercicio5/style.css?v=<?= $cssVersion ?>">
</head>

<body>
    <main class="container">
        <h1>Usuarios da API Publica</h1>
        <p class="subtitulo">Dados vindos de https://jsonplaceholder.typicode.com/users</p>

        <?php if ($mensagemErro !== ''): ?>
            <p class="mensagem erro"><?= htmlspecialchars($mensagemErro, ENT_QUOTES, 'UTF-8') ?></p>
        <?php elseif (empty($usuarios)): ?>
            <p class="mensagem vazio">Nenhum usuario encontrado.</p>
        <?php else: ?>
            <section class="grid-usuarios">
                <?php foreach ($usuarios as $usuario): ?>
                    <article class="card-usuario">
                        <h2><?= htmlspecialchars((string) ($usuario['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                        <p><strong>E-mail:</strong>
                            <?= htmlspecialchars((string) ($usuario['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                        <p><strong>Telefone:</strong>
                            <?= htmlspecialchars((string) ($usuario['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                        <p><strong>Cidade:</strong>
                            <?= htmlspecialchars((string) ($usuario['address']['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>
</body>

</html>