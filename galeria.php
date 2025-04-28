<?php
// Adicione isto no TOPO do arquivo galeria.php
header('Content-Type: text/html; charset=utf-8');

include 'assets/conexao.php';

// Configurações de paginação
$pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$limite = 10;
$offset = ($pagina - 1) * $limite;

// Filtros
$tipo_arte = isset($_GET['tipo_arte']) ? $_GET['tipo_arte'] : '';
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';

// Query base com prepared statements
$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM artistas WHERE 1=1";
$params = [];
$types = '';

// Adiciona filtro por tipo de arte
if ($tipo_arte) {
    $sql .= " AND tipo_arte = ?";
    $params[] = $tipo_arte;
    $types .= 's';
}

// Adiciona filtro de busca
if ($busca) {
    $sql .= " AND (nome LIKE ? OR email LIKE ? OR celular LIKE ?)";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
    $types .= 'sss';
}

// Adiciona paginação
$sql .= " LIMIT ? OFFSET ?";
$params[] = $limite;
$params[] = $offset;
$types .= 'ii';

// Prepara e executa a query
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Total de registros para paginação
$totalRegistros = $conn->query("SELECT FOUND_ROWS()")->fetch_row()[0];
$totalPaginas = ceil($totalRegistros / $limite);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Galeria de Artistas</title>
    <link rel="stylesheet" href="assets/estilo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><i class="fas fa-images"></i> Galeria de Artistas</h1>
            <div class="filtros-container">
                <form method="GET" class="filtro">
                    <div class="filtro-group">
                        <label for="tipo_arte">Filtrar por tipo:</label>
                        <select name="tipo_arte" id="tipo_arte">
                            <option value="">Todos os tipos</option>
                            <option value="Pintura" <?= $tipo_arte == 'Pintura' ? 'selected' : '' ?>>Pintura</option>
                            <option value="Música" <?= $tipo_arte == 'Música' ? 'selected' : '' ?>>Música</option>
                            <option value="Fotografia" <?= $tipo_arte == 'Fotografia' ? 'selected' : '' ?>>Fotografia</option>
                        </select>
                    </div>
                    <div class="filtro-group">
                        <label for="busca">Buscar:</label>
                        <input type="text" name="busca" id="busca" value="<?= htmlspecialchars($busca) ?>" placeholder="Nome, e-mail ou telefone">
                    </div>
                    <button type="submit" class="btn-filtrar"><i class="fas fa-search"></i> Aplicar Filtros</button>
                    <a href="galeria.php" class="btn-limpar"><i class="fas fa-times"></i> Limpar Filtros</a>
                </form>
            </div>
        </div>
    </header>
    <main class="container">
        <?php if ($result->num_rows > 0): ?>
            <div class="grid-artistas">
                <?php while ($artista = $result->fetch_assoc()): ?>
                    <div class="artista">
                        <div class="artista-imagem">
                            <?php
                            $portfolio = explode(',', $artista['portfolio']);
                            $primeiraImagem = trim($portfolio[0]);
                            if (pathinfo($primeiraImagem, PATHINFO_EXTENSION) === 'mp3'): ?>
                                <i class="fas fa-music"></i>
                            <?php elseif (!empty($primeiraImagem)): ?>
                                <img src="<?= htmlspecialchars($primeiraImagem) ?>" alt="<?= htmlspecialchars($artista['nome']) ?>">
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        <div class="artista-info">
                            <h3><?= htmlspecialchars($artista['nome']) ?></h3>
                            <p><?= htmlspecialchars($artista['tipo_arte']) ?></p>
                            <a href="detalhe.php?id=<?= $artista['id'] ?>" class="btn-ver-perfil">Ver Perfil</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <div class="paginacao">
                <?php if ($pagina > 1): ?>
                    <a href="galeria.php?pagina=<?= $pagina - 1 ?>&tipo_arte=<?= htmlspecialchars($tipo_arte) ?>&busca=<?= htmlspecialchars($busca) ?>">Anterior</a>
                <?php endif; ?>
                
                <span>Página <?= $pagina ?> de <?= $totalPaginas ?></span>
                
                <?php if ($pagina < $totalPaginas): ?>
                    <a href="galeria.php?pagina=<?= $pagina + 1 ?>&tipo_arte=<?= htmlspecialchars($tipo_arte) ?>&busca=<?= htmlspecialchars($busca) ?>">Próxima</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="sem-resultados">
                <p>Nenhum artista encontrado com os critérios de busca.</p>
                <a href="galeria.php" class="btn-limpar">Limpar filtros</a>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>