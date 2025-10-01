<?php

class Conexao
{
    private $host;
    private $dbname;
    private $user;
    private $password;
    private $link = null;

    // Construtor detecta automaticamente o ambiente
    public function __construct()
    {
        $server = $_SERVER['SERVER_NAME'] ?? 'localhost';

        // Se estiver rodando localmente
        if (in_array($server, ['localhost', '127.0.0.1'])) {
            $this->host = 'localhost';
            $this->dbname = 'sg3s';
            $this->user = 'root';
            $this->password = '';
        } else {
            // Configuração do servidor online
            $this->host = 'localhost';
            $this->dbname = 'td187899_sg3s';
            $this->user = 'td187899_sg3s';
            $this->password = '34FqyUp9NLt7Ybv7ZDeE';
        }
    }

    // Método para conectar ao banco
    public function conectarBanco()
    {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->user,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );

            $this->link = $pdo;

            // Se houver usuário logado na sessão, seta para triggers
            if (!empty($_SESSION['id_usuario'])) {
                $stmt = $this->link->prepare("SELECT SETAR_USUARIO(:id_usuario)");
                $stmt->bindValue(':id_usuario', $_SESSION['id_usuario'], PDO::PARAM_INT);
                $stmt->execute();
            }

            return $this->link;
        } catch (PDOException $e) {
            error_log("Erro ao conectar ao banco de dados: " . $e->getMessage());
            echo "Erro ao conectar ao banco de dados.";
            return false;
        }
    }
}

// ================== USO ==================

// Basta instanciar sem parâmetros
$conexao = new Conexao();
$pdo = $conexao->conectarBanco();
