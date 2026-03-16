<?php
$arquivoUsuarios = __DIR__ . '/usuarios_api.json';
$urlApi = 'https://jsonplaceholder.typicode.com/users';
$mensagem = '';
$erro = false;

if (!file_exists($arquivoUsuarios)) {
    file_put_contents($arquivoUsuarios, "[]");
}

if (isset($_POST['atualizar']) || filesize($arquivoUsuarios) === 0) {
    $resposta = @file_get_contents($urlApi);

    if ($resposta === false) {
        $mensagem = 'Nao foi possivel consultar a API de usuarios.';
        $erro = true;
    } else {
        $dados = json_decode($resposta, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($dados)) {
            $mensagem = 'Resposta invalida da API.';
            $erro = true;
        } else {
            $jsonSalvar = json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            if ($jsonSalvar === false || file_put_contents($arquivoUsuarios, $jsonSalvar) === false) {
                $mensagem = 'Erro ao salvar usuarios_api.json.';
                $erro = true;
            } else {
                $mensagem = 'Dados da API atualizados e salvos com sucesso.';
            }
        }
    }
}

$usuarios = [];
$conteudoLocal = @file_get_contents($arquivoUsuarios);

if ($conteudoLocal !== false) {
    $lidos = json_decode($conteudoLocal, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($lidos)) {
        $usuarios = $lidos;
    }
}

$cssVersion = file_exists(__DIR__ . '/style.css') ? (string) filemtime(__DIR__ . '/style.css') : (string) time();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exercicio 9 - API + JSON local</title>
    <link rel="stylesheet" href="/Apis/Exercicio9/style.css?v=<?= $cssVersion ?>">
</head>

<body>
    <main class="container">
        <h1>Usuarios da API salvos localmente</h1>

        <?php if ($mensagem !== ''): ?>
            <p class="mensagem <?= $erro ? 'erro' : 'sucesso' ?>">
                <?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <form method="post" action="" class="acoes">
            <button type="submit" name="atualizar">Atualizar da API e salvar</button>
        </form>

        <section class="bloco">
            <h2>Usuarios lidos do arquivo local</h2>
            <?php if (empty($usuarios)): ?>
                <p class="mensagem vazio">Nenhum usuario salvo em usuarios_api.json.</p>
            <?php else: ?>
                <div class="grid">
                    <?php foreach ($usuarios as $usuario): ?>
                        <article class="card">
                            <h3><?= htmlspecialchars((string) ($usuario['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                            <p><strong>E-mail:</strong>
                                <?= htmlspecialchars((string) ($usuario['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                            <p><strong>Telefone:</strong>
                                <?= htmlspecialchars((string) ($usuario['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                            <p><strong>Cidade:</strong>
                                <?= htmlspecialchars((string) ($usuario['address']['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>

</html>