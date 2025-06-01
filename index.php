<?php
session_start();
if (isset($_SESSION['ftp_logged_in']) && $_SESSION['ftp_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ftp_host = $_POST['ftp_host'];
    $ftp_user = $_POST['ftp_user'];
    $ftp_pass = $_POST['ftp_pass'];

    $conn_id = ftp_connect($ftp_host, 21, 10);
    if ($conn_id) {
        if (@ftp_login($conn_id, $ftp_user, $ftp_pass)) {
            $_SESSION['ftp_logged_in'] = true;
            $_SESSION['ftp_host'] = $ftp_host;
            $_SESSION['ftp_user'] = $ftp_user;
            $_SESSION['ftp_pass'] = $ftp_pass;
            $_SESSION['ftp_dir'] = '/';
            ftp_close($conn_id);
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Usuário ou senha incorretos.";
        }
        ftp_close($conn_id);
    } else {
        $error = "Não foi possível conectar ao servidor FTP.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>FTP Manager - Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0d6efd 0%, #6c757d 100%);
            min-height: 100vh;
        }
        .login-box {
            max-width: 400px;
            margin: 80px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 32px rgba(0,0,0,0.13);
            padding: 32px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2 class="mb-4 text-center text-primary">Gerenciador FTP</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <div class="mb-3">
                <label class="form-label">Servidor FTP</label>
                <input type="text" name="ftp_host" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Usuário</label>
                <input type="text" name="ftp_user" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Senha</label>
                <input type="password" name="ftp_pass" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
    </div>
</body>
</html>
