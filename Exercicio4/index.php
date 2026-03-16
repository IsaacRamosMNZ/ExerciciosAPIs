<?php
$cep = '';
$mensagem = '';
$erro = false;
$endereco = null;

if (isset($_POST['buscar'])) {
    $cep = trim($_POST['cep'] ?? '');

    if ($cep === '') {
        $mensagem = 'Informe um CEP para realizar a busca.';
        $erro = true;
    } else {
        $cepLimpo = preg_replace('/\D/', '', $cep);

        if (strlen($cepLimpo) !== 8) {
            $mensagem = 'O CEP deve conter 8 digitos.';
            $erro = true;
        } else {
            $url = 'https://viacep.com.br/ws/' . $cepLimpo . '/json/';
            $resposta = @file_get_contents($url);

            if ($resposta === false) {
                $mensagem = 'Nao foi possivel consultar a API ViaCEP no momento.';
                $erro = true;
            } else {
                $dados = json_decode($resposta, true);

                if (json_last_error() !== JSON_ERROR_NONE || !is_array($dados)) {
                    $mensagem = 'Resposta invalida recebida da API.';
                    $erro = true;
                } elseif (isset($dados['erro']) && $dados['erro'] === true) {
                    $mensagem = 'CEP nao encontrado.';
                    $erro = true;
                } else {
                    $endereco = [
                        'logradouro' => $dados['logradouro'] ?? '',
                        'bairro' => $dados['bairro'] ?? '',
                        'cidade' => $dados['localidade'] ?? '',
                        'estado' => $dados['uf'] ?? '',
                    ];
                    $mensagem = 'Endereco localizado com sucesso.';
                }
            }
        }
    }
}

$cssVersion = file_exists(__DIR__ . '/style.css') ? (string) filemtime(__DIR__ . '/style.css') : (string) time();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exercicio 4 - Buscar CEP</title>
    <link rel="stylesheet" href="/Apis/Exercicio4/style.css?v=<?= $cssVersion ?>">
</head>

<body>
    <main class="container">
        <h1>Buscar Endereco por CEP</h1>
        <p class="subtitulo">Consulta em tempo real usando a API ViaCEP.</p>

        <?php if ($mensagem !== ''): ?>
            <p class="mensagem <?= $erro ? 'erro' : 'sucesso' ?>">
                <?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <form method="post" action="" class="form-busca">
            <label for="cep">CEP</label>
            <div class="linha-campo">
                <input type="text" id="cep" name="cep" maxlength="9" placeholder="00000-000"
                    value="<?= htmlspecialchars($cep, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" name="buscar">Buscar</button>
            </div>
        </form>

        <?php if ($endereco !== null): ?>
            <section class="resultado" aria-live="polite">
                <h2>Resultado</h2>
                <ul>
                    <li><strong>Logradouro:</strong>
                        <?= htmlspecialchars((string) $endereco['logradouro'], ENT_QUOTES, 'UTF-8') ?></li>
                    <li><strong>Bairro:</strong> <?= htmlspecialchars((string) $endereco['bairro'], ENT_QUOTES, 'UTF-8') ?>
                    </li>
                    <li><strong>Cidade:</strong> <?= htmlspecialchars((string) $endereco['cidade'], ENT_QUOTES, 'UTF-8') ?>
                    </li>
                    <li><strong>Estado:</strong> <?= htmlspecialchars((string) $endereco['estado'], ENT_QUOTES, 'UTF-8') ?>
                    </li>
                </ul>
            </section>
        <?php endif; ?>
    </main>
</body>

</html>