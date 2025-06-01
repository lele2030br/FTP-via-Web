<?php
session_start();
require_once 'ftp.php';
if (!isset($_SESSION['ftp_logged_in']) || $_SESSION['ftp_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}
$conn_id = ftp_connect_logged();
if (!$conn_id) {
    session_destroy();
    header("Location: index.php");
    exit;
}

$dir = isset($_GET['dir']) ? $_GET['dir'] : $_SESSION['ftp_dir'];
if (substr($dir, -1) !== '/') $dir .= '/';
$_SESSION['ftp_dir'] = $dir;
$message = '';
$edit_content = '';
$edit_filename = '';
$view_content = '';
$view_filename = '';
$rename_filename = '';
$move_filename = '';

// Criar arquivo
if (isset($_POST['create_file'])) {
    $new_file = trim($_POST['file_name']);
    $new_content = $_POST['file_content'];
    if ($new_file) {
        $local_tmp = tempnam(sys_get_temp_dir(), 'ftpcreate');
        file_put_contents($local_tmp, $new_content);
        if (ftp_put($conn_id, $dir . $new_file, $local_tmp, FTP_ASCII)) {
            $message = "Arquivo criado!";
        } else {
            $message = "Falha ao criar arquivo.";
        }
        unlink($local_tmp);
    }
}

// Salvar edição
if (isset($_POST['save_edit'])) {
    $edit_filename = $_POST['edit_filename'];
    $edit_content = $_POST['edit_content'];
    $local_tmp = tempnam(sys_get_temp_dir(), 'ftpedit');
    file_put_contents($local_tmp, $edit_content);
    if (ftp_put($conn_id, $dir . $edit_filename, $local_tmp, FTP_ASCII)) {
        $message = "Arquivo salvo!";
    } else {
        $message = "Falha ao salvar arquivo.";
    }
    unlink($local_tmp);
}

// Abrir para edição
if (isset($_GET['edit'])) {
    $edit_filename = $_GET['edit'];
    $local_tmp = tempnam(sys_get_temp_dir(), 'ftpedit');
    if (ftp_get($conn_id, $local_tmp, $dir . $edit_filename, FTP_ASCII)) {
        $edit_content = file_get_contents($local_tmp);
    } else {
        $message = "Falha ao abrir o arquivo para edição.";
    }
    unlink($local_tmp);
}

// Visualizar arquivo de texto
if (isset($_GET['view'])) {
    $view_filename = $_GET['view'];
    $local_tmp = tempnam(sys_get_temp_dir(), 'ftpview');
    if (ftp_get($conn_id, $local_tmp, $dir . $view_filename, FTP_ASCII)) {
        $view_content = htmlspecialchars(file_get_contents($local_tmp));
    } else {
        $message = "Falha ao abrir o arquivo para visualização.";
    }
    unlink($local_tmp);
}

// Renomear arquivo ou pasta
if (isset($_GET['rename'])) {
    $rename_filename = $_GET['rename'];
}
if (isset($_POST['do_rename'])) {
    $old_name = $_POST['old_name'];
    $new_name = $_POST['new_name'];
    if (ftp_rename($conn_id, $dir . $old_name, $dir . $new_name)) {
        $message = "Renomeado com sucesso!";
    } else {
        $message = "Falha ao renomear.";
    }
}

// Mover arquivo ou pasta
if (isset($_GET['move'])) {
    $move_filename = $_GET['move'];
}
if (isset($_POST['do_move'])) {
    $move_name = $_POST['move_name'];
    $target_dir = rtrim($_POST['target_dir'], '/') . '/';
    if (ftp_rename($conn_id, $dir . $move_name, $target_dir . $move_name)) {
        $message = "Movido com sucesso!";
    } else {
        $message = "Falha ao mover.";
    }
}

// Criar pasta
if (isset($_POST['create_folder'])) {
    $new_folder = trim($_POST['folder_name']);
    if ($new_folder) {
        if (ftp_mkdir($conn_id, $dir . $new_folder)) {
            $message = "Pasta criada!";
        } else {
            $message = "Falha ao criar pasta.";
        }
    }
}

// Upload
if (isset($_POST['upload'])) {
    $file = $_FILES['file'];
    if ($file['error'] === 0) {
        $upload = ftp_put($conn_id, $dir . $file['name'], $file['tmp_name'], FTP_BINARY);
        $message = $upload ? "Arquivo enviado!" : "Falha no upload.";
    }
}

// Excluir arquivo ou pasta
if (isset($_GET['delete'])) {
    $target = $dir . $_GET['delete'];
    if (@ftp_delete($conn_id, $target)) {
        $message = "Arquivo removido!";
    } else if (@ftp_rmdir($conn_id, $target)) {
        $message = "Pasta removida!";
    } else {
        $message = "Falha na remoção.";
    }
}

// Download
if (isset($_GET['download'])) {
    $target = $dir . $_GET['download'];
    $local = tempnam(sys_get_temp_dir(), 'ftp');
    if (ftp_get($conn_id, $local, $target, FTP_BINARY)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($target) . '"');
        readfile($local);
        unlink($local);
        ftp_close($conn_id);
        exit;
    } else {
        $message = "Falha ao baixar o arquivo.";
    }
}

$items = ftp_list_dir($conn_id, $dir);
ftp_close($conn_id);
function upper_dir($dir) {
    $dir = trim($dir, '/');
    if ($dir === '') return '/';
    $parts = explode('/', $dir);
    array_pop($parts);
    return '/' . (count($parts) ? implode('/', $parts) . '/' : '');
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel FTP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; }
        .table-responsive { background: #fff; border-radius: 10px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 16px; }
        .navbar { margin-bottom: 32px; }
        .ftp-folder { color: #0d6efd; font-weight: 500; }
        .ftp-actions a { margin-right: 5px; margin-bottom: 4px; }
        @media (max-width: 600px) { .table-responsive { padding: 4px; } }
        .modal-backdrop { z-index: 1050; }
        .modal { z-index: 1060; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Gerenciador FTP</a>
            <div class="ms-auto">
                <a href="logout.php" class="btn btn-outline-light">Sair</a>
            </div>
        </div>
    </nav>
    <div class="container">
        <h4 class="mb-3">Pasta atual: <span class="ftp-folder"><?=htmlspecialchars($dir)?></span></h4>
        <?php if ($message): ?>
            <div class="alert alert-info"><?= $message ?></div>
        <?php endif; ?>

        <!-- Botão para modal de novo arquivo -->
        <div class="mb-3">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFileModal">
                <i class="bi bi-file-earmark-plus"></i> Novo Arquivo
            </button>
        </div>

        <div class="row mb-3">
            <div class="col-md-6 mb-2">
                <form method="post" enctype="multipart/form-data" class="d-flex gap-2">
                    <input type="file" name="file" class="form-control" required>
                    <button type="submit" name="upload" class="btn btn-success">Upload</button>
                </form>
            </div>
            <div class="col-md-6 mb-2">
                <form method="post" class="d-flex gap-2">
                    <input type="text" name="folder_name" class="form-control" placeholder="Nova pasta" required>
                    <button type="submit" name="create_folder" class="btn btn-secondary">Criar pasta</button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($dir !== '/'): ?>
                        <tr>
                            <td colspan="3">
                                <a href="?dir=<?=urlencode(upper_dir($dir))?>" class="text-decoration-none">
                                    <i class="bi bi-arrow-left"></i> .. Voltar
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <?php if ($item['is_dir']): ?>
                                    <i class="bi bi-folder-fill text-warning"></i>
                                    <a href="?dir=<?=urlencode($dir . $item['name'])?>/" class="ftp-folder"><?=htmlspecialchars($item['name'])?></a>
                                <?php else: ?>
                                    <i class="bi bi-file-earmark"></i>
                                    <?=htmlspecialchars($item['name'])?>
                                <?php endif; ?>
                            </td>
                            <td><?= $item['is_dir'] ? 'Pasta' : 'Arquivo' ?></td>
                            <td class="ftp-actions text-end">
                                <?php if ($item['is_dir']): ?>
                                    <a href="?dir=<?=urlencode($dir . $item['name'])?>/" class="btn btn-sm btn-outline-primary">Abrir</a>
                                    <a href="?rename=<?=urlencode($item['name'])?>" class="btn btn-sm btn-outline-secondary">Renomear</a>
                                    <a href="?move=<?=urlencode($item['name'])?>" class="btn btn-sm btn-outline-dark">Mover</a>
                                    <a href="?delete=<?=urlencode($item['name'])?>" class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Excluir pasta?')">Excluir</a>
                                <?php else: ?>
                                    <a href="?download=<?=urlencode($item['name'])?>" class="btn btn-sm btn-outline-success">Baixar</a>
                                    <a href="?view=<?=urlencode($item['name'])?>" class="btn btn-sm btn-outline-info">Visualizar</a>
                                    <a href="?edit=<?=urlencode($item['name'])?>" class="btn btn-sm btn-outline-warning">Editar</a>
                                    <a href="?rename=<?=urlencode($item['name'])?>" class="btn btn-sm btn-outline-secondary">Renomear</a>
                                    <a href="?move=<?=urlencode($item['name'])?>" class="btn btn-sm btn-outline-dark">Mover</a>
                                    <a href="?delete=<?=urlencode($item['name'])?>" class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Excluir arquivo?')">Excluir</a>
                                <?php endif;?>
                            </td>
                        </tr>
                    <?php endforeach;?>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted">Nenhum arquivo ou pasta.</td>
                        </tr>
                    <?php endif;?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para criar arquivo -->
    <div class="modal fade" id="createFileModal" tabindex="-1" aria-labelledby="createFileModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form method="post">
            <div class="modal-header">
              <h5 class="modal-title" id="createFileModalLabel">Novo Arquivo</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Nome do arquivo</label>
                <input type="text" name="file_name" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Conteúdo</label>
                <textarea name="file_content" class="form-control" rows="10" required></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" name="create_file" class="btn btn-primary">Criar Arquivo</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal para editar arquivo -->
    <?php if ($edit_filename): ?>
    <div class="modal fade show" id="editFileModal" tabindex="-1" aria-labelledby="editFileModalLabel" aria-modal="true" style="display:block;">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form method="post">
            <div class="modal-header">
              <h5 class="modal-title" id="editFileModalLabel">Editar Arquivo: <?=htmlspecialchars($edit_filename)?></h5>
              <a href="<?=htmlspecialchars($_SERVER['PHP_SELF'])?>" class="btn-close"></a>
            </div>
            <div class="modal-body">
              <input type="hidden" name="edit_filename" value="<?=htmlspecialchars($edit_filename)?>">
              <textarea name="edit_content" class="form-control" rows="16" required><?=htmlspecialchars($edit_content)?></textarea>
            </div>
            <div class="modal-footer">
              <a href="<?=htmlspecialchars($_SERVER['PHP_SELF'])?>" class="btn btn-secondary">Cancelar</a>
              <button type="submit" name="save_edit" class="btn btn-primary">Salvar</button>
            </div>
          </form>
        </div>
      </div>
      <div class="modal-backdrop fade show"></div>
      <script>document.body.classList.add('modal-open');</script>
    </div>
    <?php endif; ?>

    <!-- Modal para visualizar arquivo -->
    <?php if ($view_filename): ?>
    <div class="modal fade show" id="viewFileModal" tabindex="-1" aria-labelledby="viewFileModalLabel" aria-modal="true" style="display:block;">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="viewFileModalLabel">Visualizar Arquivo: <?=htmlspecialchars($view_filename)?></h5>
              <a href="<?=htmlspecialchars($_SERVER['PHP_SELF'])?>" class="btn-close"></a>
            </div>
            <div class="modal-body">
              <pre style="white-space: pre-wrap;"><?= $view_content ?></pre>
            </div>
            <div class="modal-footer">
              <a href="<?=htmlspecialchars($_SERVER['PHP_SELF'])?>" class="btn btn-secondary">Fechar</a>
            </div>
        </div>
      </div>
      <div class="modal-backdrop fade show"></div>
      <script>document.body.classList.add('modal-open');</script>
    </div>
    <?php endif; ?>

    <!-- Modal para renomear arquivo/pasta -->
    <?php if ($rename_filename): ?>
    <div class="modal fade show" id="renameFileModal" tabindex="-1" aria-labelledby="renameFileModalLabel" aria-modal="true" style="display:block;">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="post">
            <div class="modal-header">
              <h5 class="modal-title" id="renameFileModalLabel">Renomear: <?=htmlspecialchars($rename_filename)?></h5>
              <a href="<?=htmlspecialchars($_SERVER['PHP_SELF'])?>" class="btn-close"></a>
            </div>
            <div class="modal-body">
              <input type="hidden" name="old_name" value="<?=htmlspecialchars($rename_filename)?>">
              <input type="text" name="new_name" class="form-control" value="<?=htmlspecialchars($rename_filename)?>" required>
            </div>
            <div class="modal-footer">
              <a href="<?=htmlspecialchars($_SERVER['PHP_SELF'])?>" class="btn btn-secondary">Cancelar</a>
              <button type="submit" name="do_rename" class="btn btn-primary">Renomear</button>
            </div>
          </form>
        </div>
      </div>
      <div class="modal-backdrop fade show"></div>
      <script>document.body.classList.add('modal-open');</script>
    </div>
    <?php endif; ?>

    <!-- Modal para mover arquivo/pasta -->
    <?php if ($move_filename): ?>
    <div class="modal fade show" id="moveFileModal" tabindex="-1" aria-labelledby="moveFileModalLabel" aria-modal="true" style="display:block;">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="post">
            <div class="modal-header">
              <h5 class="modal-title" id="moveFileModalLabel">Mover: <?=htmlspecialchars($move_filename)?></h5>
              <a href="<?=htmlspecialchars($_SERVER['PHP_SELF'])?>" class="btn-close"></a>
            </div>
            <div class="modal-body">
              <input type="hidden" name="move_name" value="<?=htmlspecialchars($move_filename)?>">
              <label>Destino (diretório FTP, ex: /outra/pasta/):</label>
              <input type="text" name="target_dir" class="form-control" required>
            </div>
            <div class="modal-footer">
              <a href="<?=htmlspecialchars($_SERVER['PHP_SELF'])?>" class="btn btn-secondary">Cancelar</a>
              <button type="submit" name="do_move" class="btn btn-primary">Mover</button>
            </div>
          </form>
        </div>
      </div>
      <div class="modal-backdrop fade show"></div>
      <script>document.body.classList.add('modal-open');</script>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (isset($_POST['create_file'])): ?>
      <script>
        // Fechar modal ao criar arquivo
        var createModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('createFileModal'));
        createModal.hide();
      </script>
    <?php endif; ?>
</body>
</html>
