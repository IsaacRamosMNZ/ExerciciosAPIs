<?php
$urlApi = 'https://jsonplaceholder.typicode.com/posts';
$mensagemErro = '';
$postsExibicao = [];

$resposta = @file_get_contents($urlApi);

if ($resposta === false) {
    $mensagemErro = 'Nao foi possivel consultar a API de posts.';
} else {
    $dados = json_decode($resposta, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($dados)) {
        $mensagemErro = 'Resposta invalida recebida da API.';
    } else {
        $filtrados = [];

        foreach ($dados as $post) {
            if ((int) ($post['userId'] ?? 0) === 1) {
                $filtrados[] = $post;
            }
        }

        $postsExibicao = array_slice($filtrados, 0, 10);
    }
}

$cssVersion = file_exists(__DIR__ . '/style.css') ? (string) filemtime(__DIR__ . '/style.css') : (string) time();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exercicio 6 - Posts da API</title>
    <link rel="stylesheet" href="/Apis/Exercicio6/style.css?v=<?= $cssVersion ?>">
</head>

<body>
    <main class="container">
        <h1>10 primeiros posts do userId 1</h1>
        <p class="subtitulo">Dados da API JSONPlaceholder.</p>

        <?php if ($mensagemErro !== ''): ?>
            <p class="mensagem erro"><?= htmlspecialchars($mensagemErro, ENT_QUOTES, 'UTF-8') ?></p>
        <?php elseif (empty($postsExibicao)): ?>
            <p class="mensagem vazio">Nenhum post encontrado para userId 1.</p>
        <?php else: ?>
            <section class="grid-posts">
                <?php foreach ($postsExibicao as $post): ?>
                    <article class="card-post">
                        <p class="post-id">Post #<?= htmlspecialchars((string) ($post['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                        <h2><?= htmlspecialchars((string) ($post['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                        <p><?= htmlspecialchars((string) ($post['body'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>
</body>

</html>