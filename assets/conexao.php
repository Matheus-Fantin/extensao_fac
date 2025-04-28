<?php
$host = "localhost";
$user = "app_artistas";  
$password = "SenhaSegura123@";
$database = "plataforma_artistas";

// Tentativa de conexão com tratamento de erros
try {
    $conn = new mysqli($host, $user, $password, $database);
    
    if ($conn->connect_error) {
        throw new Exception("Falha na conexão com o MySQL: " . $conn->connect_error);
    }

    // Verificar se a tabela 'artistas' existe
    $result = $conn->query("SHOW TABLES LIKE 'artistas'");
    if ($result->num_rows == 0) {
        throw new Exception("Tabela 'artistas' não encontrada no banco de dados.");
    }

    $conn->set_charset("utf8mb4");

} catch (Exception $e) {
    // Log do erro (opcional: criar um arquivo de log)
    file_put_contents('db_errors.log', date('Y-m-d H:i:s') . " - " . $e->getMessage() . "\n", FILE_APPEND);
    
    // Mensagem amigável
    die("Erro crítico: O sistema está temporariamente indisponível. Contate o administrador.");
}
?>