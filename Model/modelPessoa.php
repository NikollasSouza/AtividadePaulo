<?php
require_once '../Classes/pessoa.php';
require_once '../Conexao/conexao.php';

class ModelPessoa {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function cadastrar(Pessoa $pessoa) {
        $sql = "INSERT INTO pessoas (nome, cpf, telefone) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $pessoa->getNome(),
            $pessoa->getCpf(),
            $pessoa->getTelefone()
        ]);
    }

    public function listar() {
        $sql = "SELECT * FROM pessoas";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id) {
        $sql = "SELECT * FROM pessoas WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar(Pessoa $pessoa) {
        $sql = "UPDATE pessoas SET nome = ?, cpf = ?, telefone = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $pessoa->getNome(),
            $pessoa->getCpf(),
            $pessoa->getTelefone(),
            $pessoa->getId()
        ]);
    }

    public function excluir($id) {
        $sql = "DELETE FROM pessoas WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
}
?>