<?php
$pais = '';
$mensagem = '';
$erro = false;
$dadosPais = null;

function removerAcentos(string $texto): string
{
    if (function_exists('iconv')) {
        $convertido = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        if ($convertido !== false) {
            return $convertido;
        }
    }

    $mapa = [
        'á' => 'a',
        'à' => 'a',
        'â' => 'a',
        'ã' => 'a',
        'ä' => 'a',
        'Á' => 'A',
        'À' => 'A',
        'Â' => 'A',
        'Ã' => 'A',
        'Ä' => 'A',
        'é' => 'e',
        'è' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'É' => 'E',
        'È' => 'E',
        'Ê' => 'E',
        'Ë' => 'E',
        'í' => 'i',
        'ì' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'Í' => 'I',
        'Ì' => 'I',
        'Î' => 'I',
        'Ï' => 'I',
        'ó' => 'o',
        'ò' => 'o',
        'ô' => 'o',
        'õ' => 'o',
        'ö' => 'o',
        'Ó' => 'O',
        'Ò' => 'O',
        'Ô' => 'O',
        'Õ' => 'O',
        'Ö' => 'O',
        'ú' => 'u',
        'ù' => 'u',
        'û' => 'u',
        'ü' => 'u',
        'Ú' => 'U',
        'Ù' => 'U',
        'Û' => 'U',
        'Ü' => 'U',
        'ç' => 'c',
        'Ç' => 'C',
    ];

    return strtr($texto, $mapa);
}

if (isset($_POST['buscar'])) {
    $pais = trim($_POST['pais'] ?? '');

    if ($pais === '') {
        $mensagem = 'Digite o nome de um pais.';
        $erro = true;
    } else {
        $contexto = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 12,
                'ignore_errors' => true,
            ],
        ]);

        $nomeSemAcento = strtolower(trim(removerAcentos($pais)));
        $equivalencias = [
            'butao' => 'bhutan',
        ];

        $termosBusca = [$pais, removerAcentos($pais)];
        if (isset($equivalencias[$nomeSemAcento])) {
            $termosBusca[] = $equivalencias[$nomeSemAcento];
        }

        $encontrado = false;
        foreach (array_unique($termosBusca) as $termo) {
            $urls = [
                'https://restcountries.com/v3.1/name/' . rawurlencode($termo) . '?fullText=true',
                'https://restcountries.com/v3.1/name/' . rawurlencode($termo),
            ];

            foreach ($urls as $url) {
                $resposta = @file_get_contents($url, false, $contexto);

                if ($resposta === false) {
                    continue;
                }

                $dados = json_decode($resposta, true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($dados)) {
                    continue;
                }

                if (isset($dados['status']) && (int) $dados['status'] === 404) {
                    continue;
                }

                if (isset($dados[0]) && is_array($dados[0])) {
                    $paisApi = $dados[0];
                    $dadosPais = [
                        'nome_oficial' => (string) ($paisApi['name']['official'] ?? ''),
                        'capital' => (string) (($paisApi['capital'][0] ?? '')),
                        'regiao' => (string) ($paisApi['region'] ?? ''),
                        'populacao' => (int) ($paisApi['population'] ?? 0),
                        'bandeira' => (string) ($paisApi['flags']['png'] ?? ($paisApi['flags']['svg'] ?? '')),
                    ];

                    $mensagem = 'Pais localizado com sucesso.';
                    $encontrado = true;
                    break 2;
                }
            }
        }

        if (!$encontrado) {
            $mensagem = 'Pais nao encontrado.';
            $erro = true;
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
    <title>Exercicio 7 - Buscar Pais</title>
    <link rel="stylesheet" href="/Apis/Exercicio7/style.css?v=<?= $cssVersion ?>">
</head>

<body>
    <main class="container">
        <h1>Buscar informacoes de um pais</h1>

        <?php if ($mensagem !== ''): ?>
            <p class="mensagem <?= $erro ? 'erro' : 'sucesso' ?>">
                <?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <form method="post" action="" class="form-busca">
            <label for="pais">Nome do pais</label>
            <div class="linha">
                <input type="text" id="pais" name="pais" value="<?= htmlspecialchars($pais, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="Ex.: brazil">
                <button type="submit" name="buscar">Buscar</button>
            </div>
        </form>

        <?php if ($dadosPais !== null): ?>
            <section class="resultado">
                <h2>Resultado</h2>
                <ul>
                    <li><strong>Nome oficial:</strong>
                        <?= htmlspecialchars($dadosPais['nome_oficial'], ENT_QUOTES, 'UTF-8') ?></li>
                    <li><strong>Capital:</strong> <?= htmlspecialchars($dadosPais['capital'], ENT_QUOTES, 'UTF-8') ?></li>
                    <li><strong>Regiao:</strong> <?= htmlspecialchars($dadosPais['regiao'], ENT_QUOTES, 'UTF-8') ?></li>
                    <li><strong>Populacao:</strong>
                        <?= htmlspecialchars(number_format((int) $dadosPais['populacao'], 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?>
                    </li>
                </ul>

                <?php if ($dadosPais['bandeira'] !== ''): ?>
                    <img src="<?= htmlspecialchars($dadosPais['bandeira'], ENT_QUOTES, 'UTF-8') ?>"
                        alt="Bandeira do pais buscado">
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>
</body>

</html>