<?php
// จัดการรายวิชา
require 'db.php';
check_login();
$page = 'subjects.php';
$uid = $_SESSION['user_id'];

$colors = ['#2F5D50', '#3E6A8A', '#7A5C99', '#8A4B5C', '#9A7B1F', '#4F7A3A', '#5C5A8C', '#6B5444'];

// เพิ่มวิชา
if (isset($_POST['add'])) {
    query("INSERT INTO subjects (user_id, code, name, color) VALUES (?, ?, ?, ?)",
          [$uid, strtoupper(trim($_POST['code'])), trim($_POST['name']), $_POST['color']]);
    $_SESSION['msg'] = 'เพิ่มรายวิชาแล้ว';
    header('Location: subjects.php');
    exit;
}

// ลบวิชา
if (isset($_POST['delete_id'])) {
    query("DELETE FROM subjects WHERE id = ? AND user_id = ?", [$_POST['delete_id'], $uid]);
    $_SESSION['msg'] = 'ลบรายวิชาแล้ว (งานของวิชานี้ยังอยู่)';
    header('Location: subjects.php');
    exit;
}

// ดึงวิชาพร้อมนับจำนวนงาน
$result = query("SELECT subjects.*,
                        SUM(tasks.status != 'done') AS open_count,
                        SUM(tasks.status = 'done') AS done_count
                 FROM subjects LEFT JOIN tasks ON tasks.subject_id = subjects.id
                 WHERE subjects.user_id = ?
                 GROUP BY subjects.id ORDER BY subjects.code", [$uid]);

include 'header.php';
?>

<h1>รายวิชาเทอมนี้</h1>
<p class="lead">แต่ละวิชามีสีของตัวเอง เพื่อให้แยกงานได้ง่ายในทุกหน้า</p>

<div class="two-col">
    <div class="box">
        <?php if (mysqli_num_rows($result) == 0): ?>
            <p class="muted">ยังไม่มีรายวิชา เพิ่มได้จากฟอร์มด้านข้าง</p>
        <?php endif; ?>
        <ul class="subject-list">
            <?php while ($s = mysqli_fetch_assoc($result)):
                $total = $s['open_count'] + $s['done_count'];
                $percent = $total > 0 ? round($s['done_count'] / $total * 100) : 0; ?>
                <li style="--c: <?= h($s['color']) ?>">
                    <a href="tasks.php?show=all&subject=<?= $s['id'] ?>">
                        <b><?= h($s['code']) ?></b> <?= h($s['name']) ?>
                        <small>ค้าง <?= (int) $s['open_count'] ?> งาน, เสร็จ <?= $percent ?>%</small>
                    </a>
                    <form method="post" onsubmit="return confirm('ลบวิชานี้ใช่ไหม?')">
                        <input type="hidden" name="delete_id" value="<?= $s['id'] ?>">
                        <button class="btn btn-sm btn-delete">ลบ</button>
                    </form>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>

    <form method="post" class="box">
        <h2>เพิ่มรายวิชา</h2>
        <label>รหัสวิชา <input type="text" name="code" placeholder="เช่น ET361"></label>
        <label>ชื่อวิชา <input type="text" name="name" required></label>
        <p class="label">สีประจำวิชา</p>
        <div class="colors">
            <?php foreach ($colors as $i => $c): ?>
                <label style="--c: <?= $c ?>">
                    <input type="radio" name="color" value="<?= $c ?>" <?= $i == 0 ? 'checked' : '' ?>><span></span>
                </label>
            <?php endforeach; ?>
        </div>
        <button name="add" class="btn btn-main">เพิ่มรายวิชา</button>
    </form>
</div>

<?php include 'footer.php'; ?>
