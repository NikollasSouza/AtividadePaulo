<?php
// Configurações para depuração (remova em produção)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Evita HTML no output (crucial para JSON)
ini_set('log_errors', 1);
ini_set('error_log', '../logs/php-errors.log'); // Crie a pasta logs

// Garante que a resposta será JSON
header('Content-Type: application/json; charset=utf-8');

// Incluir classes e conexão
try {
    require_once '../Model/modelPessoa.php';
    require_once '../Classes/pessoa.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Arquivo não encontrado. Verifique a estrutura de pastas.'
    ]);
    exit;
}

// Instanciar model
try {
    $model = new ModelPessoa();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro ao conectar ao banco. Verifique a configuração.'
    ]);
    exit;
}

// Receber a ação
$acao = $_POST['acao'] ?? null;

// Switch para tratar as ações
switch ($acao) {
    // CADASTRAR PESSOA
    case 'cadastrar':
        $nome = trim($_POST['nome'] ?? '');
        $cpf = trim($_POST['cpf'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');

        if (empty($nome) || empty($cpf) || empty($telefone)) {
            echo json_encode(['status' => 'erro', 'mensagem' => 'Todos os campos são obrigatórios.']);
            exit;
        }

        // Validar formato do CPF: 123.456.789-09
        if (!preg_match('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', $cpf)) {
            echo json_encode(['status' => 'erro', 'mensagem' => 'CPF inválido. Use o formato 123.456.789-09.']);
            exit;
        }

        $pessoa = new Pessoa();
        $pessoa->setNome($nome);
        $pessoa->setCpf($cpf);
        $pessoa->setTelefone($telefone);

        try {
            if ($model->cadastrar($pessoa)) {
                echo json_encode(['status' => 'sucesso', 'mensagem' => 'Pessoa cadastrada com sucesso!']);
            } else {
                echo json_encode(['status' => 'erro', 'mensagem' => 'Falha ao cadastrar.']);
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // CPF duplicado
                echo json_encode(['status' => 'erro', 'mensagem' => 'CPF já cadastrado.']);
            } else {
                // Erro genérico para não expor detalhes
                echo json_encode(['status' => 'erro', 'mensagem' => 'Erro no banco de dados.']);
            }
        }
        break;

    // LISTAR TODAS AS PESSOAS
    case 'listar':
        try {
            $pessoas = $model->listar();
            echo json_encode([
                'status' => 'sucesso',
                'dados' => $pessoas
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'erro',
                'mensagem' => 'Erro ao listar os dados.'
            ]);
        }
        break;

    // EXCLUIR PESSOA POR ID
    case 'excluir':
        try {
            $id = $_POST['id'] ?? null;

            if (!$id || !is_numeric($id)) {
                echo json_encode(['status' => 'erro', 'mensagem' => 'ID inválido.']);
                exit;
            }

            if ($model->excluir($id)) {
                echo json_encode(['status' => 'sucesso', 'mensagem' => 'Pessoa excluída com sucesso!']);
            } else {
                echo json_encode(['status' => 'erro', 'mensagem' => 'Falha ao excluir.']);
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'erro',
                'mensagem' => 'Erro ao excluir.'
            ]);
        }
        break;

    // BUSCAR PESSOA POR ID (para edição)
    case 'buscarPorId':
        try {
            $id = $_POST['id'] ?? null;

            if (!$id || !is_numeric($id)) {
                echo json_encode(['status' => 'erro', 'mensagem' => 'ID inválido.']);
                exit;
            }

            $pessoa = $model->buscarPorId($id);

            if ($pessoa) {
                echo json_encode([
                    'status' => 'sucesso',
                    'dados' => $pessoa
                ]);
            } else {
                echo json_encode([
                    'status' => 'erro',
                    'mensagem' => 'Pessoa não encontrada.'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'erro',
                'mensagem' => 'Erro ao buscar pessoa.'
            ]);
        }
        break;

    // ATUALIZAR DADOS DA PESSOA
    case 'atualizar':
        try {
            $id = $_POST['id'] ?? null;
            $nome = trim($_POST['nome'] ?? '');
            $cpf = trim($_POST['cpf'] ?? '');
            $telefone = trim($_POST['telefone'] ?? '');

            if (!$id || !is_numeric($id) || empty($nome) || empty($cpf) || empty($telefone)) {
                echo json_encode(['status' => 'erro', 'mensagem' => 'Todos os campos são obrigatórios.']);
                exit;
            }

            // Verificar duplicidade de CPF
            try {
                $sql = "SELECT id FROM pessoas WHERE cpf = ? AND id != ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$cpf, $id]);
                if ($stmt->fetch()) {
                    echo json_encode(['status' => 'erro', 'mensagem' => 'CPF já cadastrado para outra pessoa.']);
                    exit;
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao validar CPF.']);
                exit;
            }

            $pessoa = new Pessoa();
            $pessoa->setId($id);
            $pessoa->setNome($nome);
            $pessoa->setCpf($cpf);
            $pessoa->setTelefone($telefone);

            if ($model->atualizar($pessoa)) {
                echo json_encode(['status' => 'sucesso', 'mensagem' => 'Pessoa atualizada com sucesso!']);
            } else {
                echo json_encode(['status' => 'erro', 'mensagem' => 'Falha ao atualizar.']);
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'erro',
                'mensagem' => 'Erro ao atualizar.'
            ]);
        }
        break;

    // AÇÃO INVÁLIDA
    default:
        echo json_encode([
            'status' => 'erro',
            'mensagem' => 'Ação inválida.'
        ]);
        break;
}
?>