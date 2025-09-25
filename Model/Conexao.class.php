<?php
// class Conexao
// {
//     private $host = 'localhost';
//     private $dbname = 'sg3s';
//     private $user = 'root';
//     private $password = '';
//     private $link = null;

//     // Método para conectar ao banco
//     public function conectarBanco()
//     {
//         try {
//             // Certifique-se de que a sessão foi iniciada
//             if (session_status() === PHP_SESSION_NONE) {
//                 session_start();
//             }
//             // Cria a conexão PDO com charset e tratamento de erros
//             $pdo = new PDO(
//                 "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
//                 $this->user,
//                 $this->password,
//                 [
//                     PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Lança exceções em erros
//                     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Padrão de fetch
//                 ]
//             );
//             $this->link = $pdo;
//             // Se houver usuário logado na sessão, seta para triggers
//             if (!empty($_SESSION['id_usuario'])) {
//                 $stmt = $this->link->prepare("SELECT SETAR_USUARIO(:id_usuario)");
//                 $stmt->bindValue(':id_usuario', $_SESSION['id_usuario'], PDO::PARAM_INT);
//                 $stmt->execute();
//             }
//             return $this->link;
//         } catch (PDOException $e) {
//             // Log detalhado de erro
//             error_log("Erro ao conectar ao banco de dados: " . $e->getMessage());
//             print "Erro ao conectar ao banco de dados.";
//             return false;
//         }
//     }
// }


class Conexao
{
    private $host = 'localhost';     // Servidor do banco
    private $dbname = 'td187899_sg3s';        // Nome do banco que criamos
    private $user = 'td187899_sg3s';          // Usuário padrão do XAMPP
    private $password = '34FqyUp9NLt7Ybv7ZDeE';          // Senha padrão é vazia
    private $link = null;            // link de conexao
    // Método para conectar ao banco
    public function conectarBanco()
    {
        try {
            // Certifique-se de que a sessão foi iniciada
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            // Cria a conexão PDO com charset e tratamento de erros
            $pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->user,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Lança exceções em erros
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Padrão de fetch
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
            // Log detalhado de erro
            error_log("Erro ao conectar ao banco de dados: " . $e->getMessage());
            print "Erro ao conectar ao banco de dados.";
            return false;
        }
    }
}
