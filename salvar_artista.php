<?php

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// ===== CONFIGURAÇÕES ===== //
$uploadDir = __DIR__ . '/uploads/';
$allowedTypes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'audio/mpeg' => 'mp3',
    'video/mp4' => 'mp4'
];

// Criar diretório se não existir
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        die(json_encode([
            'success' => false,
            'message' => 'Falha ao criar diretório de uploads. Verifique as permissões do servidor.'
        ]));
    }
}

// Verificar permissões de escrita
if (!is_writable($uploadDir)) {
    die(json_encode([
        'success' => false,
        'message' => 'Diretório de uploads não tem permissão de escrita. Configure as permissões para 777.'
    ]));
}

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Configurações
$uploadDir = __DIR__ . '/uploads/';
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        die(json_encode(['success' => false, 'message' => 'Falha ao criar diretório de uploads']));
    }
}

// Tipos permitidos com extensões correspondentes
$allowedTypes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'audio/mpeg' => 'mp3',
    'video/mp4' => 'mp4'
];
$maxFileSize = 5 * 1024 * 1024; // 5MB

// Conexão com o banco de dados
try {
    include __DIR__ . '/assets/conexao.php';
    
    // Validação dos campos obrigatórios
    $required = ['nome', 'email', 'arte'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("O campo '{$field}' é obrigatório");
        }
    }

    // Validação de e-mail
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Por favor, insira um e-mail válido");
    }

    // Processamento de uploads
    $portfolioPaths = [];
    if (!empty($_FILES['portfolio']['name'][0])) {
        foreach ($_FILES['portfolio']['tmp_name'] as $key => $tmpName) {
            $fileType = $_FILES['portfolio']['type'][$key];
            $fileSize = $_FILES['portfolio']['size'][$key];
            $fileName = $_FILES['portfolio']['name'][$key];
            
            // Verifica tipo e tamanho
            if (!array_key_exists($fileType, $allowedTypes)) {
                continue;
            }
            
            if ($fileSize > $maxFileSize) {
                continue;
            }

            // Gera nome seguro para o arquivo
            $fileExt = $allowedTypes[$fileType];
            $safeFileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.\-]/', '_', $fileName);
            $filePath = $uploadDir . $safeFileName;

            // Move o arquivo
            if (move_uploaded_file($tmpName, $filePath)) {
                $portfolioPaths[] = $filePath;
            }
        }
    }

    // Prepara e executa a query
    $stmt = $conn->prepare("INSERT INTO artistas (nome, email, celular, tipo_arte, portfolio, bio) VALUES (?, ?, ?, ?, ?, ?)");
    $portfolioStr = implode(',', $portfolioPaths);
    $bio = $_POST['bio'] ?? '';
    
    $stmt->bind_param(
        "ssssss", 
        $_POST['nome'], 
        $_POST['email'], 
        $_POST['celular'] ?? '', 
        $_POST['arte'], 
        $portfolioStr,
        $bio
    );

    if (!$stmt->execute()) {
        throw new Exception("Erro ao salvar no banco de dados");
    }

    echo json_encode([
        'success' => true,
        'message' => 'Artista cadastrado com sucesso!'
    ]);

} catch (Exception $e) {
    // Remove arquivos em caso de erro
    foreach ($portfolioPaths as $file) {
        if (file_exists($file)) {
            @unlink($file);
        }
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>