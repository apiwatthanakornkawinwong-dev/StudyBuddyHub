<?php
// ส่วนหัวและเมนูด้านข้าง  (ต้องกำหนด $page ก่อน include)
$menu = [
    'index.php'    => 'ภาพรวม',
    'tasks.php'    => 'งานทั้งหมด',
    'calendar.php' => 'ปฏิทิน',
    'subjects.php' => 'รายวิชา',
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>รวมงาน</title>
    <link href="https://fonts.googleapis.com/css2?family=Anuphan:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar" id="sidebar">
        <a href="index.php" class="logo">รวมงาน</a>
        <a href="task_form.php" class="btn btn-main">+ เพิ่มงาน</a>

        <nav class="menu">
            <?php foreach ($menu as $file => $label): ?>
                <a href="<?= $file ?>" class="<?= $page == $file ? 'active' : '' ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </nav>

        <div class="user">
            <b><?= h($_SESSION['user_name']) ?></b>
            <a href="logout.php">ออกจากระบบ</a>
        </div>
    </aside>

    <div class="main">
        <div class="topbar">
            <button onclick="document.body.classList.toggle('menu-open')">เมนู</button>
            <span class="logo">รวมงาน</span>
            <a href="task_form.php" class="btn btn-main btn-sm">+ เพิ่มงาน</a>
        </div>
        <div class="content">
            <?php if (isset($_SESSION['msg'])): ?>
                <p class="alert"><?= h($_SESSION['msg']) ?></p>
                <?php unset($_SESSION['msg']); ?>
            <?php endif; ?>
