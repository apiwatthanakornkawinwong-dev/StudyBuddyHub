<?php
require 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // เช็กว่าอีเมลซ้ำไหม
    $check = query("SELECT id FROM users WHERE email = ?", [$email]);

    if (strlen($password) < 8) {
        $error = 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร';
    } elseif (mysqli_num_rows($check) > 0) {
        $error = 'อีเมลนี้มีคนใช้แล้ว';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        query("INSERT INTO users (name, email, password) VALUES (?, ?, ?)", [$name, $email, $hash]);
        $_SESSION['msg'] = 'สมัครสมาชิกสำเร็จ เข้าสู่ระบบได้เลย';
        header('Location: login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>สมัครสมาชิก | รวมงาน</title>
    <link href="https://fonts.googleapis.com/css2?family=Anuphan:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="auth-box">
        <span class="logo">รวมงาน</span>
        <h1>สร้างบัญชีใหม่</h1>
        <p class="muted">สมัครแล้วเริ่มรวมงานทุกวิชาได้เลย</p>

        <?php if ($error): ?><p class="alert error"><?= $error ?></p><?php endif; ?>

        <form method="post">
            <label>ชื่อ <input type="text" name="name" required></label>
            <label>อีเมล <input type="email" name="email" required></label>
            <label>รหัสผ่าน (อย่างน้อย 8 ตัว) <input type="password" name="password" minlength="8" required></label>
            <button class="btn btn-main full">สมัครสมาชิก</button>
        </form>
        <p class="muted center">มีบัญชีแล้ว? <a href="login.php">เข้าสู่ระบบ</a></p>
    </div>
    <div class="auth-side">
        <p>งานทุกวิชา<br>อยู่ในที่เดียว</p>
    </div>
</body>
</html>
