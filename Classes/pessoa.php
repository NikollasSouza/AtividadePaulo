<?php
class Pessoa {
    private $id;
    private $nome;
    private $cpf;
    private $telefone;

    public function getId() { return $this->id; }
    public function getNome() { return $this->nome; }
    public function getCpf() { return $this->cpf; }
    public function getTelefone() { return $this->telefone; }

    public function setId($id) { $this->id = $id; }
    public function setNome($nome) { $this->nome = $nome; }
    public function setCpf($cpf) { $this->cpf = $cpf; }
    public function setTelefone($telefone) { $this->telefone = $telefone; }
}
?>