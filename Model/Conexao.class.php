<?php
class Conexao {
    private $host = 'localhost';     // Servidor do banco
    private $dbname = 'sg3s';        // Nome do banco que criamos
    private $user = 'root';          // Usuário padrão do XAMPP
    private $password = '';          // Senha padrão é vazia
    private $link = null;            // link de conexao

    // Método para realizar a conexão com o banco de dados.
    //Retorna um link de conexão ou false caso de falha.
    public function conectarBanco() {
        try {
            // try = valida ou trata como verdadeiro
            // PDO é uma classe do PHP que gerencia a conexão com bancos de dados.
            // Criar a conexão com PDO (PHP Data Objects)
            $pdo = new PDO("mysql:host={$this->host};dbname={$this->dbname}",
            "{$this->user}", "{$this->password}");
            $this->link = $pdo;
            // tratamento e erros
            return $this->link;
        } catch (PDOException $e) {
            // Caso de erro
            // Exibe a mensagem de erro
            print "Erro ao conectar ao banco de dados: " . $e->getMessage();
            return false;
        }
    }
}
?>

