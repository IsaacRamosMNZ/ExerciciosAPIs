<?php
$arquivoJson = __DIR__ . '/produtos.json';

if (!file_exists($arquivoJson)) {
    die('Arquivo produtos.json não encontrado.');
}

$conteudoJson = file_get_contents($arquivoJson);
$produtos = json_decode($conteudoJson, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die('Erro ao decodificar o JSON: ' . json_last_error_msg());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Produtos</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <main class="container">
        <h1>Lista de Produtos</h1>
        <p class="subtitulo">Dados carregados de um arquivo JSON local com <code>json_decode()</code>.</p>

        <?php if (empty($produtos)): ?>
            <p class="vazio">Nenhum produto encontrado.</p>
        <?php else: ?>
            <ul class="grid">
                <?php foreach ($produtos as $produto): ?>
                    <li class="card">
                        <h2 class="produto-nome"><?= htmlspecialchars($produto['nome']) ?></h2>
                        <p class="linha"><strong>Preço:</strong> R$ <?= number_format((float) $produto['preco'], 2, ',', '.') ?>
                        </p>
                        <span class="tag"><?= htmlspecialchars($produto['categoria']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>
</body>

</html>