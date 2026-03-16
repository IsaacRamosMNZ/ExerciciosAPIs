<?php
$arquivoJson = __DIR__ . '/alunos.json';
$mensagemErro = '';
$alunos = [];

if (!file_exists($arquivoJson)) {
    $mensagemErro = 'Arquivo alunos.json nao encontrado.';
} else {
    $conteudoJson = file_get_contents($arquivoJson);
    $dados = json_decode($conteudoJson, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $mensagemErro = 'Erro ao ler o JSON: ' . json_last_error_msg();
    } elseif (!is_array($dados)) {
        $mensagemErro = 'Formato invalido no arquivo alunos.json.';
    } else {
        $alunos = $dados;
    }
}

$cssVersion = file_exists(__DIR__ . '/style.css') ? (string) filemtime(__DIR__ . '/style.css') : (string) time();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exercicio 3 - Lista de Alunos</title>
    <link rel="stylesheet" href="/Apis/Exercicio3/style.css?v=<?= $cssVersion ?>">
</head>

<body>
    <main class="container">
        <h1>Alunos Cadastrados</h1>
        <p class="subtitulo">Listagem lida do arquivo JSON local.</p>

        <?php if ($mensagemErro !== ''): ?>
            <p class="erro"><?= htmlspecialchars($mensagemErro, ENT_QUOTES, 'UTF-8') ?></p>
        <?php elseif (empty($alunos)): ?>
            <p class="vazio">Nenhum aluno cadastrado no arquivo.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Idade</th>
                            <th>Curso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alunos as $aluno): ?>
                            <tr>
                                <td><?= htmlspecialchars((string) ($aluno['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($aluno['idade'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($aluno['curso'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</body>

</html>