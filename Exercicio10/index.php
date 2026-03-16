<?php
date_default_timezone_set('America/Sao_Paulo');

$arquivoConsultas = __DIR__ . '/consultas.json';
$mensagem = '';
$erro = false;
$cep = '';
$resultado = null;
$consultas = [];
$momentoConsulta = '';

function formatarDataHora(?string $valor): string
{
    $texto = trim((string) $valor);
    if ($texto === '') {
        return 'Nao informado';
    }

    $timestamp = strtotime($texto);
    if ($timestamp === false) {
        return $texto;
    }

    return date('d/m/Y H:i:s', $timestamp);
}

if (!file_exists($arquivoConsultas)) {
    file_put_contents($arquivoConsultas, "[]");
}

$conteudoAtual = @file_get_contents($arquivoConsultas);
if ($conteudoAtual !== false) {
    $lidas = json_decode($conteudoAtual, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($lidas)) {
        $consultas = $lidas;
    }
}

if (isset($_POST['consultar'])) {
    $cep = trim($_POST['cep'] ?? '');

    if ($cep === '') {
        $mensagem = 'Informe um CEP para consultar.';
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
                $mensagem = 'Nao foi possivel consultar o ViaCEP.';
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
                    $resultado = [
                        'cep' => (string) ($dados['cep'] ?? $cepLimpo),
                        'logradouro' => (string) ($dados['logradouro'] ?? ''),
                        'bairro' => (string) ($dados['bairro'] ?? ''),
                        'cidade' => (string) ($dados['localidade'] ?? ''),
                        'estado' => (string) ($dados['uf'] ?? ''),
                    ];

                    $novaConsulta = $resultado;
                    $novaConsulta['data_hora'] = date('Y-m-d H:i:s');
                    $momentoConsulta = $novaConsulta['data_hora'];

                    $consultas[] = $novaConsulta;

                    $jsonSalvar = json_encode($consultas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                    if ($jsonSalvar === false || file_put_contents($arquivoConsultas, $jsonSalvar) === false) {
                        $mensagem = 'Consulta realizada, mas houve erro ao salvar no JSON.';
                        $erro = true;
                    } else {
                        $mensagem = 'Consulta realizada e salva com sucesso.';
                    }
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
    <title>Exercicio 10 - Consulta + Armazenamento</title>
    <link rel="stylesheet" href="/Apis/Exercicio10/style.css?v=<?= $cssVersion ?>">
</head>

<body>
    <main class="container">
        <h1>Mini sistema de consulta CEP</h1>

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
                <button type="submit" name="consultar">Consultar</button>
            </div>
        </form>

        <?php if ($resultado !== null): ?>
            <section class="resultado">
                <h2>Endereco encontrado</h2>
                <ul>
                    <li><strong>CEP:</strong> <?= htmlspecialchars($resultado['cep'], ENT_QUOTES, 'UTF-8') ?></li>
                    <li><strong>Data e hora da consulta:</strong>
                        <?= htmlspecialchars(formatarDataHora($momentoConsulta), ENT_QUOTES, 'UTF-8') ?></li>
                    <li><strong>Logradouro:</strong> <?= htmlspecialchars($resultado['logradouro'], ENT_QUOTES, 'UTF-8') ?>
                    </li>
                    <li><strong>Bairro:</strong> <?= htmlspecialchars($resultado['bairro'], ENT_QUOTES, 'UTF-8') ?></li>
                    <li><strong>Cidade:</strong> <?= htmlspecialchars($resultado['cidade'], ENT_QUOTES, 'UTF-8') ?></li>
                    <li><strong>Estado:</strong> <?= htmlspecialchars($resultado['estado'], ENT_QUOTES, 'UTF-8') ?></li>
                </ul>
            </section>
        <?php endif; ?>

        <section class="historico">
            <h2>Historico de consultas</h2>
            <?php if (empty($consultas)): ?>
                <p class="mensagem vazio">Nenhuma consulta salva ainda.</p>
            <?php else: ?>
                <div class="tabela-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Data e hora</th>
                                <th>CEP</th>
                                <th>Logradouro</th>
                                <th>Bairro</th>
                                <th>Cidade</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_reverse($consultas) as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars(formatarDataHora((string) ($item['data_hora'] ?? ($item['dataHora'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td><?= htmlspecialchars((string) ($item['cep'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($item['logradouro'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($item['bairro'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($item['cidade'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($item['estado'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>

</html>