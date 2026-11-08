<?php
require 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $result = query("SELECT * FROM users WHERE email = ?", [$email]);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>เข้าสู่ระบบ | รวมงาน</title>
    <link href="https://fonts.googleapis.com/css2?family=Anuphan:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="auth-box">
        <span class="logo">รวมงาน</span>
        <p class="muted">ทำโดยนิสิต เพื่อนิสิต</p>
        <h1>ยินดีต้อนรับกลับมา</h1>
        <p class="muted">เข้าสู่ระบบเพื่อดูงานทุกวิชาของคุณ</p>

        <?php if ($error): ?><p class="alert error"><?= $error ?></p><?php endif; ?>
        <?php if (isset($_SESSION['msg'])): ?>
            <p class="alert"><?= h($_SESSION['msg']) ?></p><?php unset($_SESSION['msg']); ?>
        <?php endif; ?>

        <form method="post">
            <label>อีเมล <input type="email" name="email" required></label>
            <label>รหัสผ่าน <input type="password" name="password" required></label>
            <button class="btn btn-main full">เข้าสู่ระบบ</button>
        </form>
        <p class="muted center">ยังไม่มีบัญชี? <a href="register.php">สร้างบัญชีใหม่</a></p>
    </div>
    <div class="auth-side">
        <p>งานทุกวิชา<br>อยู่ในที่เดียว</p>
    </div>
</body>
</html>
