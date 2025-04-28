<?php
include 'assets/conexao.php';

$id = intval($_GET['id']);

// Usando prepared statement para evitar SQL injection
$stmt = $conn->prepare("SELECT * FROM artistas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: galeria.php");
    exit;
}

$artista = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($artista['nome']) ?> - Perfil</title>
    <link rel="stylesheet" href="assets/estilo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><i class="fas fa-user"></i> <?= htmlspecialchars($artista['nome']) ?></h1>
            <a href="galeria.php" class="btn-voltar"><i class="fas fa-arrow-left"></i> Voltar</a>
        </div>
    </header>
    <main class="container">
        <div class="perfil-artista">
            <div class="perfil-info">
                <p><strong><i class="fas fa-envelope"></i> E-mail:</strong> <?= htmlspecialchars($artista['email']) ?></p>
                <p><strong><i class="fas fa-phone"></i> Celular:</strong> <?= htmlspecialchars($artista['celular']) ?></p>
                <p><strong><i class="fas fa-palette"></i> Tipo de Arte:</strong> <?= htmlspecialchars($artista['tipo_arte']) ?></p>
            </div>
            
            <div class="perfil-portfolio">
                <h2><i class="fas fa-folder-open"></i> Portfólio</h2>
                <div class="portfolio-grid">
                    <?php
                    $arquivos = explode(',', $artista['portfolio']);
                    foreach ($arquivos as $arquivo):
                        $arquivo = trim($arquivo);
                        if (empty($arquivo)) continue;
                        
                        $extensao = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));
                        if ($extensao === 'mp3'):
                    ?>
                        <div class="portfolio-item">
                            <audio controls src="<?= htmlspecialchars($arquivo) ?>"></audio>
                            <p><?= htmlspecialchars(basename($arquivo)) ?></p>
                        </div>
                    <?php elseif(in_array($extensao, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                        <div class="portfolio-item">
                            <img src="<?= htmlspecialchars($arquivo) ?>" alt="Portfólio">
                        </div>
                    <?php elseif($extensao === 'mp4'): ?>
                        <div class="portfolio-item">
                            <video controls>
                                <source src="<?= htmlspecialchars($arquivo) ?>" type="video/mp4">
                            </video>
                        </div>
                    <?php endif;
                    endforeach; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>