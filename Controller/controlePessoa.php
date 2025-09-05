<?php
require_once '../Model/modelPessoa.php';
require_once '../Classes/pessoa.php';

header('Content-Type: application/json');

$acao = $_POST['acao'] ?? $_GET['acao'] ?? null;

$model = new ModelPessoa();

switch ($acao) {
    case 'cadastrar':
        $nome = trim($_POST['nome'] ?? '');
        $cpf = trim($_POST['cpf'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');

        if (empty($nome) || empty($cpf) || empty($telefone)) {
            echo json_encode(['status' => 'erro', 'mensagem' => 'Todos os campos são obrigatórios.']);
            exit;
        }

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
            if ($e->getCode() == 23000) {
                echo json_encode(['status' => 'erro', 'mensagem' => 'CPF já cadastrado.']);
            } else {
                echo json_encode(['status' => 'erro', 'mensagem' => 'Erro no banco: ' . $e->getMessage()]);
            }
        }
        break;

    case 'listar':
        try {
            $pessoas = $model->listar();
            echo json_encode(['status' => 'sucesso', 'dados' => $pessoas]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao listar: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'erro', 'mensagem' => 'Ação inválida.']);
        break;

    case 'excluir':
    try {
        $id = $_POST['id'] ?? null;

        if (!$id || !is_numeric($id)) {
            echo json_encode(['status' => 'erro', 'mensagem' => 'ID inválido.']);
            exit;
        }

        // Chamar o model para excluir
        if ($model->excluir($id)) {
            echo json_encode(['status' => 'sucesso', 'mensagem' => 'Pessoa excluída com sucesso!']);
        } else {
            echo json_encode(['status' => 'erro', 'mensagem' => 'Falha ao excluir.']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro: ' . $e->getMessage()]);
    }
    break;
}
?>