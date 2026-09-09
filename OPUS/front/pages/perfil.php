<?php
session_start();
require_once '../../back/conexao.php';

// Verifica se está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Busca apenas os dados necessários para a tela de perfil
$stmt_user = $conn->prepare("SELECT nome, email, dificuldade, foto_perfil FROM usuarios WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$dados_user = $stmt_user->get_result()->fetch_assoc();

// Define a foto padrão caso o usuário ainda não tenha enviado uma
$foto_perfil = !empty($dados_user['foto_perfil']) ? $dados_user['foto_perfil'] : 'assets/img/opi pulando feliz.png';

// NOVA FUNÇÃO AUXILIAR PREPARADA PARA BASE64
function obterCaminhoAvatar($path) {
    // Se a string começar com "data:image", significa que é Base64 do banco. O HTML lê isso direto!
    if (strpos($path, 'data:image') === 0) {
        return $path;
    }
    // Se for o avatar padrão
    if (strpos($path, 'assets/') === 0) {
        return '../' . $path;
    }
    // Caso de falha, retorna a imagem padrão
    return '../assets/img/opi pulando feliz.png';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Opus</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="shortcut icon" href="../assets/img/logo.png">
    
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    
    <style>
        /* Trava a tela para não rolar */
        body, html {
            overflow: hidden; 
        }
        
        .main-content {
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .dashboard-grid {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center; /* Centraliza tudo verticalmente na tela */
            padding-bottom: 20px;
        }

        .path-header {
            margin-bottom: 10px;
        }

        .path-header h1 {
            font-size: 1.4rem;
            margin: 0;
        }

        /* Grid principal ajustado */
        .perfil-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr; /* Coluna direita um pouco mais larga */
            gap: 20px;
            align-items: stretch; /* Força as colunas a terem a mesma altura */
            height: 100%;
            max-height: 500px; /* Limita a altura máxima para não esticar demais */
        }

        /* Cartões mais compactos */
        .perfil-card {
            background: rgba(20, 20, 28, 0.7);
            border: 1px solid rgba(26, 54, 202, 0.2);
            border-radius: 12px;
            padding: 20px;
            color: #fff;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
        }

        /* Coluna direita: Distribui as duas caixas uniformemente */
        .coluna-direita {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .coluna-direita .perfil-card {
            flex: 1; /* Faz as duas caixas dividirem a altura igualmente */
            justify-content: center;
        }

        .perfil-card-header {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.1rem;
            color: #4d66f5;
            margin-bottom: 15px;
            border-bottom: 1px solid rgba(77, 102, 245, 0.2);
            padding-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Lado Esquerdo: Foto reduzida e centralizada */
        .avatar-section {
            align-items: center;
            justify-content: space-between;
        }

        .foto-preview-grande {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #1a36ca;
            box-shadow: 0 0 15px rgba(26, 54, 202, 0.4);
            margin-bottom: 10px;
        }

        .info-badge {
            background: rgba(0, 0, 0, 0.4);
            padding: 6px 12px;
            border-radius: 20px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.8rem;
            color: #a0a0b0;
            margin-bottom: 10px;
        }

        /* Inputs e Botões compactos */
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-family: 'Poppins', sans-serif;
            font-size: 0.85rem;
            color: #a0a0b0;
            margin-bottom: 5px;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(26, 54, 202, 0.3);
            border-radius: 8px;
            color: #fff;
            font-family: 'Poppins', sans-serif;
        }
        .form-control:focus {
            outline: none;
            border-color: #4d66f5;
            box-shadow: 0 0 10px rgba(77, 102, 245, 0.3);
        }
        .btn-custom {
            display: inline-block;
            width: 100%;
            padding: 10px;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-primary { background: linear-gradient(135deg, #3a1aca, #3a1aca); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(26, 54, 202, 0.6); }
        .btn-danger { background: #3a1aca; color: white; margin-top: 10px; }
        .btn-danger:hover { background: #281677; }
        .btn-success { background: #3a1aca; color: white; }
        .btn-success:hover { background: #281677; }
        
        .file-upload-wrapper { margin-bottom: 10px; width: 100%; text-align: center; }
        .file-upload-wrapper input[type="file"] { font-size: 0.75rem; color: #a0a0b0; }

        p.security-text {
            font-family: 'Poppins', sans-serif; 
            font-size: 0.8rem; 
            color: #a0a0b0; 
            margin-bottom: 10px; 
            line-height: 1.4;
        }

        /* Responsividade para telas menores */
        @media (max-width: 900px) {
            body, html { overflow: auto; }
            .main-content { height: auto; }
            .perfil-grid { grid-template-columns: 1fr; max-height: none; }
        }
    </style>
</head>
<body>
    <canvas id="bg-canvas"></canvas>

    <div class="app-container">
        
        <aside class="sidebar">
            <div class="logo">OPUS</div>
            <nav class="menu">
                <a href="dashboard.php" class="nav-link"><i class="fa-solid fa-chart-line"></i> Progresso</a>
                <a href="conquistas.php" class="nav-link"><i class="fa-solid fa-award"></i> Conquistas</a>
                <a href="ranking.php" class="nav-link"><i class="fa-solid fa-ranking-star"></i> Ranking</a>
                <a href="perfil.php" class="nav-link active"><i class="fa-solid fa-user"></i> Perfil</a>
            </nav>
        </aside>

        <main class="main-content">
            
            <?php include '../../back/topbar.php'; ?>

            <section class="dashboard-grid">
                <div class="curriculum-column">
                    <div class="path-header">
                        <h1>Configurações da Conta</h1>
                    </div>

                    <div class="perfil-grid">
                        
                        <div class="perfil-card avatar-section">
                            <img src="<?php echo htmlspecialchars(obterCaminhoAvatar($foto_perfil)); ?>" alt="Sua Foto" class="foto-preview-grande">
                            
                            <div class="info-badge">
                                <i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($dados_user['email']); ?>
                            </div>

                            <form action="../../back/atualizar_perfil.php" method="POST" enctype="multipart/form-data" style="width: 100%;">
                                <input type="hidden" name="action" value="atualizar_foto">
                                <div class="file-upload-wrapper">
                                    <input type="file" name="foto" accept="image/*" required>
                                </div>
                                <button type="submit" class="btn-custom btn-primary"><i class="fa-solid fa-upload"></i> Alterar Avatar</button>
                            </form>

                             <a href="../../back/logout.php" style="text-decoration: none; width: 100%;">
                                <button class="btn-custom btn-danger"><i class="fa-solid fa-right-from-bracket"></i> Sair do Opus</button>
                            </a>
                        </div>

                        <div class="coluna-direita">
                            
                            <div class="perfil-card">
                                <div class="perfil-card-header">
                                    <i class="fa-solid fa-user-pen"></i> Editar Dados Pessoais
                                </div>
                                <form action="../../back/atualizar_perfil.php" method="POST">
                                    <input type="hidden" name="action" value="atualizar_nome">
                                    <div class="form-group">
                                        <label for="novo_nome">Nome de Exibição</label>
                                        <input type="text" id="novo_nome" name="novo_nome" class="form-control" value="<?php echo htmlspecialchars($dados_user['nome']); ?>" required>
                                    </div>
                                    <button type="submit" class="btn-custom btn-primary"><i class="fa-solid fa-floppy-disk"></i> Salvar Alterações</button>
                                </form>
                            </div>

                            <div class="perfil-card">
                                <div class="perfil-card-header" style="color: #655bf0; border-bottom-color: rgba(79, 43, 238, 0.2);">
                                    <i class="fa-solid fa-shield-halved"></i> Segurança e Senha
                                </div>
                                <p class="security-text">
                                    Para proteger sua conta, a alteração de senha é feita através de um link seguro. Clique no botão abaixo para receber as instruções no seu e-mail cadastrado.
                                </p>
                                <form action="../../back/solicitar_senha.php" method="POST">
                                    <button type="submit" class="btn-custom btn-success"><i class="fa-solid fa-key"></i> Solicitar Troca de Senha</button>
                                </form>
                            </div>
                            
                        </div>

                    </div>
                </div>
            </section>
        </main>
    </div>
    <script src="../assets/js/script.js"></script> 
</body>
</html>