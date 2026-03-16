<?php
$cep = '';
$mensagem = '';
$erro = false;
$endereco = null;

if (isset($_POST['buscar'])) {
    $cep = trim($_POST['cep'] ?? '');

    if ($cep === '') {
        $mensagem = 'Informe um CEP.';
        $erro = true;
    } else {
        $cepLimpo = preg_replace('/\D/', '', $cep);

        if (strlen($cepLimpo) !== 8) {
            $mensagem = 'O CEP deve ter 8 digitos.';
            $erro = true;
        } elseif (!function_exists('curl_init')) {
            $mensagem = 'cURL nao esta disponivel no PHP.';
            $erro = true;
        } else {
            $url = 'https://viacep.com.br/ws/' . $cepLimpo . '/json/';

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $resposta = curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErro = curl_error($ch);
            curl_close($ch);

            if ($resposta === false || $httpCode >= 400) {
                $mensagem = 'Falha ao consultar a API com cURL. ' . $curlErro;
                $erro = true;
            } else {
                $dados = json_decode($resposta, true);

                if (json_last_error() !== JSON_ERROR_NONE || !is_array($dados)) {
                    $mensagem = 'Resposta invalida da API.';
                    $erro = true;
                } elseif (isset($dados['erro']) && $dados['erro'] === true) {
                    $mensagem = 'CEP nao encontrado.';
                    $erro = true;
                } else {
                    $endereco = [
                        'logradouro' => (string) ($dados['logradouro'] ?? ''),
                        'bairro' => (string) ($dados['bairro'] ?? ''),
                        'cidade' => (string) ($dados['localidade'] ?? ''),
                        'estado' => (string) ($dados['uf'] ?? ''),
                    ];
                    $mensagem = 'Endereco localizado com sucesso via cURL.';
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
    <title>Exercicio 8 - Consulta CEP com cURL</title>
    <link rel="stylesheet" href="/Apis/Exercicio8/style.css?v=<?= $cssVersion ?>">
</head>

<body>
    <main class="container">
        <h1>Consulta de CEP usando cURL</h1>

        <?php if ($mensagem !== ''): ?>
            <p class="mensagem <?= $erro ? 'erro' : 'sucesso' ?>">
                <?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <form method="post" action="" class="form-busca">
            <label for="cep">CEP</label>
            <div class="linha">
                <input type="text" id="cep" name="cep" maxlength="9" placeholder="00000-000"
                    value="<?= htmlspecialchars($cep, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" name="buscar">Buscar</button>
            </div>
        </form>

        <?php if ($endereco !== null): ?>
            <section class="resultado">
                <h2>Resultado</h2>
                <ul>
                    <li><strong>Logradouro:</strong> <?= htmlspecialchars($endereco['logradouro'], ENT_QUOTES, 'UTF-8') ?>
                    </li>
                    <li><strong>Bairro:</strong> <?= htmlspecialchars($endereco['bairro'], ENT_QUOTES, 'UTF-8') ?></li>
                    <li><strong>Cidade:</strong> <?= htmlspecialchars($endereco['cidade'], ENT_QUOTES, 'UTF-8') ?></li>
                    <li><strong>Estado:</strong> <?= htmlspecialchars($endereco['estado'], ENT_QUOTES, 'UTF-8') ?></li>
                </ul>
            </section>
        <?php endif; ?>
    </main>
</body>

</html>