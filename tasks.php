<?php
// หน้างานทั้งหมด + รับคำสั่งติ๊กเสร็จ / ลบงาน
require 'db.php';
check_login();
$page = 'tasks.php';
$uid = $_SESSION['user_id'];

// ติ๊กงานเสร็จ / ยกเลิกเสร็จ
if (isset($_POST['toggle_id'])) {
    query("UPDATE tasks SET status = IF(status = 'done', 'todo', 'done') WHERE id = ? AND user_id = ?",
          [$_POST['toggle_id'], $uid]);
    $back = ($_POST['back'] == 'index.php') ? 'index.php' : 'tasks.php';
    header('Location: ' . $back);
    exit;
}

// ลบงาน
if (isset($_POST['delete_id'])) {
    query("DELETE FROM tasks WHERE id = ? AND user_id = ?", [$_POST['delete_id'], $uid]);
    $_SESSION['msg'] = 'ลบงานแล้ว';
    header('Location: tasks.php');
    exit;
}

// ตัวกรอง
$show = $_GET['show'] ?? 'open';      // open = งานค้าง, done = เสร็จแล้ว, all = ทั้งหมด
$subject = $_GET['subject'] ?? '';

$sql = "SELECT tasks.*, subjects.code, subjects.color
        FROM tasks LEFT JOIN subjects ON tasks.subject_id = subjects.id
        WHERE tasks.user_id = ?";
$params = [$uid];
if ($show == 'open') $sql .= " AND tasks.status != 'done'";
if ($show == 'done') $sql .= " AND tasks.status = 'done'";
if ($subject != '') {
    $sql .= " AND tasks.subject_id = ?";
    $params[] = $subject;
}
$sql .= " ORDER BY tasks.due_date";
$result = query($sql, $params);

$tasks = [];
while ($row = mysqli_fetch_assoc($result)) {
    $tasks[] = task_info($row);
}

$subjects = query("SELECT * FROM subjects WHERE user_id = ? ORDER BY code", [$uid]);

include 'header.php';
?>

<h1>งานทั้งหมด</h1>
<p class="lead">รวมงานจากทุกวิชาไว้ในหน้าเดียว เรียงตามวันส่ง</p>

<form method="get" class="filters">
    <div class="tabs">
        <a href="?show=open" class="<?= $show == 'open' ? 'on' : '' ?>">งานค้าง</a>
        <a href="?show=all" class="<?= $show == 'all' ? 'on' : '' ?>">ทั้งหมด</a>
        <a href="?show=done" class="<?= $show == 'done' ? 'on' : '' ?>">เสร็จแล้ว</a>
    </div>
    <input type="hidden" name="show" value="<?= h($show) ?>">
    <select name="subject" onchange="this.form.submit()">
        <option value="">ทุกวิชา</option>
        <?php while ($s = mysqli_fetch_assoc($subjects)): ?>
            <option value="<?= $s['id'] ?>" <?= $subject == $s['id'] ? 'selected' : '' ?>>
                <?= h($s['code'] . ' ' . $s['name']) ?>
            </option>
        <?php endwhile; ?>
    </select>
</form>

<div class="box">
    <?php if (count($tasks) == 0): ?>
        <p class="muted">ไม่มีงานในหมวดนี้ <a href="task_form.php">เพิ่มงานใหม่</a></p>
    <?php else: ?>
        <ul class="task-list">
            <?php foreach ($tasks as $t) show_task($t); ?>
        </ul>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
