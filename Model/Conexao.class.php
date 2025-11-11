<?php

// Defina uma constante para indicar que a chamada é via API
if (!defined('IS_API_CALL')) {
    define('IS_API_CALL', false);
}

class Conexao
{
    private $host;
    private $dbname;
    private $user;
    private $password;
    private $link = null;

    // Detecta automaticamente o ambiente (local ou online)
    private function ambiente()
    {
        // detecção de conexão de teste ( servidoor e porta)
        if (!isset($_SERVER['SERVER_NAME']) || in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1'])) {
            return 'local';
        } else {
            return 'online';
        }
    }

    // Configuração de Conexao com base no Servidor detectado
    private function configurarBanco()
    {
        $ambiente = $this->ambiente();
        // local {Ambiente de Teste}
        if ($ambiente === 'local') {
            $this->host = getenv('DB_HOST');
            $this->dbname = getenv('DB_NAME');
            $this->user = getenv('DB_USER');
            $this->password = getenv('DB_PASS');
        } else {
            // Online {Ambiente de Podução}
            $this->host = getenv('DB_HOSTProduction');
            $this->dbname = getenv('DB_NAMEProduction');
            $this->user = getenv('DB_USERProduction');
            $this->password = getenv('DB_PASSProduction');
        }
    }

    // Método principal de conexão
    public function conectarBanco()
    {
        try {
            // Configura banco conforme ambiente
            $this->configurarBanco();

            // Inicia sessão se ainda não estiver iniciada
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Cria conexão PDO
            $this->link = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->user,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );

            // Se houver usuário logado na sessão
            if (!empty($_SESSION['id_usuario'])) {
                $stmt = $this->link->prepare("SELECT SETAR_USUARIO(:id_usuario)");
                $stmt->bindValue(':id_usuario', $_SESSION['id_usuario'], PDO::PARAM_INT);
                $stmt->execute();
            }

            return $this->link;
        } catch (PDOException $e) {
            error_log("Erro ao conectar ao banco de dados ({$this->dbname}): " . $e->getMessage());
            print "Erro ao conectar ao banco de dados" . $e->getMessage();
            return false;
        }
    }
}
