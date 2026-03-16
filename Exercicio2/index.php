<?php
$arquivoJson = __DIR__ . '/alunos.json';
$arquivoCss = __DIR__ . '/style.css';
$mensagem = '';
$erro = false;

$nome = '';
$idade = '';
$curso = '';

$cssVersion = file_exists($arquivoCss) ? (string) filemtime($arquivoCss) : (string) time();

if (isset($_POST['cadastrar'])) {
    $nome = trim($_POST['nome'] ?? '');
    $idade = trim($_POST['idade'] ?? '');
    $curso = trim($_POST['curso'] ?? '');

    if ($nome === '' || $idade === '' || $curso === '') {
        $mensagem = 'Preencha todos os campos para cadastrar o aluno.';
        $erro = true;
    } elseif (!is_numeric($idade) || (int) $idade <= 0) {
        $mensagem = 'Informe uma idade valida maior que zero.';
        $erro = true;
    } else {
        if (!file_exists($arquivoJson)) {
            file_put_contents($arquivoJson, "[]");
        }

        $conteudo = file_get_contents($arquivoJson);
        $alunos = json_decode($conteudo, true);

        if (!is_array($alunos)) {
            $alunos = [];
        }

        $alunos[] = [
            'nome' => $nome,
            'idade' => (int) $idade,
            'curso' => $curso,
        ];

        $json = json_encode($alunos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            $mensagem = 'Erro ao converter os dados para JSON.';
            $erro = true;
        } elseif (file_put_contents($arquivoJson, $json) === false) {
            $mensagem = 'Erro ao salvar os dados no arquivo alunos.json.';
            $erro = true;
        } else {
            $mensagem = 'Aluno cadastrado com sucesso!';
            $erro = false;
            $nome = '';
            $idade = '';
            $curso = '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exercicio 2 - Cadastro de Alunos</title>
    <link rel="stylesheet" href="/Apis/Exercicio2/style.css?v=<?= $cssVersion ?>">
</head>

<body>
    <h1>Cadastro de Alunos</h1>

    <?php if ($mensagem !== ''): ?>
        <div class="mensagem <?= $erro ? 'erro' : 'sucesso' ?>">
            <?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <div>
            <label for="nome">Nome do aluno</label>
            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div>
            <label for="idade">Idade</label>
            <input type="number" id="idade" name="idade" min="1"
                value="<?= htmlspecialchars($idade, ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div>
            <label for="curso">Curso</label>
            <input type="text" id="curso" name="curso" value="<?= htmlspecialchars($curso, ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <button type="submit" name="cadastrar">Cadastrar</button>
    </form>
</body>

</html>