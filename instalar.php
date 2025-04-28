<?php
// ==============================================
// SCRIPT DE INSTALAÇÃO - CRIAÇÃO DA TABELA ARTISTAS
// Execute apenas uma vez e depois DELETE este arquivo!
// ==============================================

header('Content-Type: text/html; charset=utf-8');

// Configurações de segurança
define('INSTALL_MODE', true);

// Verificar se o script está sendo acessado localmente
$allowed_ips = ['127.0.0.1', '::1'];
if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips) && strpos($_SERVER['HTTP_HOST'], 'localhost') === false) {
    die("<h1>ACESSO NEGADO</h1><p>Este script só pode ser executado localmente.</p>");
}

// Conexão com o banco de dados
$config = [
    'host' => 'localhost',
    'user' => 'app_artistas',
    'password' => 'SenhaSegura123@',
    'database' => 'plataforma_artistas'
];

echo "<!DOCTYPE html>
<html>
<head>
    <title>Instalação - Plataforma de Artistas</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }
        .success { color: #2ecc71; }
        .error { color: #e74c3c; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Instalação do Banco de Dados</h1>";

try {
    // Estabelecer conexão
    $conn = new mysqli($config['host'], $config['user'], $config['password'], $config['database']);
    
    if ($conn->connect_error) {
        throw new Exception("Erro de conexão: " . $conn->connect_error);
    }

    echo "<p>Conexão com o banco de dados estabelecida com sucesso.</p>";

    // Verificar se a tabela já existe
    $check_table = $conn->query("SHOW TABLES LIKE 'artistas'");
    
    if ($check_table->num_rows > 0) {
        echo "<p class='error'>Atenção: A tabela 'artistas' já existe no banco de dados.</p>";
    } else {
        // SQL para criação da tabela
        $sql = "CREATE TABLE `artistas` (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `nome` VARCHAR(100) NOT NULL,
            `email` VARCHAR(100) NOT NULL UNIQUE,
            `celular` VARCHAR(20) NULL,
            `tipo_arte` VARCHAR(50) NOT NULL,
            `portfolio` TEXT NULL,
            `bio` TEXT NULL,
            `data_cadastro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_tipo_arte` (`tipo_arte`),
            INDEX `idx_data_cadastro` (`data_cadastro`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        // Executar a criação
        if ($conn->query($sql)) {
            echo "<p class='success'>Tabela 'artistas' criada com sucesso!</p>";
            
            // Inserir dados de exemplo (opcional)
            $examples = [
                ['nome' => 'Ana Silva', 'email' => 'ana@exemplo.com', 'celular' => '(11) 99999-9999', 'tipo_arte' => 'Pintura', 'bio' => 'Artista plástica especializada em aquarela'],
                ['nome' => 'Carlos Oliveira', 'email' => 'carlos@exemplo.com', 'celular' => '(21) 98888-8888', 'tipo_arte' => 'Música', 'bio' => 'Violonista e compositor']
            ];
            
            $stmt = $conn->prepare("INSERT INTO artistas (nome, email, celular, tipo_arte, bio) VALUES (?, ?, ?, ?, ?)");
            
            foreach ($examples as $artist) {
                $stmt->bind_param("sssss", $artist['nome'], $artist['email'], $artist['celular'], $artist['tipo_arte'], $artist['bio']);
                $stmt->execute();
            }
            
            echo "<p>Dados de exemplo inseridos.</p>";
        } else {
            throw new Exception("Erro ao criar tabela: " . $conn->error);
        }
    }

    // Verificar e criar diretório de uploads
    $uploadDir = __DIR__ . '/uploads/';
    if (!file_exists($uploadDir)) {
        if (mkdir($uploadDir, 0777, true)) {
            echo "<p>Diretório 'uploads' criado com sucesso.</p>";
        } else {
            echo "<p class='error'>Atenção: Não foi possível criar o diretório 'uploads'. Crie manualmente e defina permissões 777.</p>";
        }
    } else {
        echo "<p>Diretório 'uploads' já existe.</p>";
    }

    // Verificar permissões
    if (file_exists($uploadDir) && !is_writable($uploadDir)) {
        echo "<p class='error'>Atenção: O diretório 'uploads' não tem permissão de escrita. Defina permissões 777.</p>";
    }

    echo "<h2>Status Final</h2>
    <ul>
        <li>Banco de dados: <span class='success'>OK</span></li>
        <li>Tabela 'artistas': " . ($check_table->num_rows > 0 ? '<span class="error">JÁ EXISTIA</span>' : '<span class="success">CRIADA</span>') . "</li>
        <li>Diretório 'uploads': " . (file_exists($uploadDir) ? '<span class="success">OK</span>' : '<span class="error">FALHOU</span>' . "</li>
    </ul>");

    echo "<div class='warning'>
        <h3>IMPORTANTE</h3>
        <p>1. Este script deve ser <strong>excluído</strong> após a instalação por questões de segurança.</p>
        <p>2. Configure as permissões do diretório 'uploads' para 777 se ainda não estiverem corretas.</p>
    </div>";

} catch (Exception $e) {
    echo "<div class='error'><h2>Erro durante a instalação</h2>
    <p>" . htmlspecialchars($e->getMessage()) . "</p>
    <pre>" . htmlspecialchars($sql ?? '') . "</pre></div>";
} finally {
    if (isset($conn)) $conn->close();
}

echo "</body></html>";
?>