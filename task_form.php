<?php
// ฟอร์มเพิ่มงาน / แก้ไขงาน
require 'db.php';
check_login();
$page = 'tasks.php';
$uid = $_SESSION['user_id'];
$id = $_GET['id'] ?? '';

// บันทึกข้อมูล
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_id = $_POST['subject_id'] ?: null;
    $due = $_POST['due_date'] . ' ' . ($_POST['due_time'] ?: '23:59');
    $important = isset($_POST['important']) ? 1 : 0;

    if ($id == '') {
        query("INSERT INTO tasks (user_id, subject_id, title, type, due_date, level, important, status, note)
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
              [$uid, $subject_id, $_POST['title'], $_POST['type'], $due, $_POST['level'], $important, $_POST['status'], $_POST['note']]);
        $_SESSION['msg'] = 'เพิ่มงานแล้ว';
    } else {
        query("UPDATE tasks SET subject_id = ?, title = ?, type = ?, due_date = ?, level = ?, important = ?, status = ?, note = ?
               WHERE id = ? AND user_id = ?",
              [$subject_id, $_POST['title'], $_POST['type'], $due, $_POST['level'], $important, $_POST['status'], $_POST['note'], $id, $uid]);
        $_SESSION['msg'] = 'บันทึกการแก้ไขแล้ว';
    }
    header('Location: tasks.php');
    exit;
}

// ค่าเริ่มต้นของฟอร์ม (กรณีเพิ่มใหม่)
$task = [
    'title' => '', 'subject_id' => $_GET['subject'] ?? '', 'type' => 'solo',
    'due_date' => ($_GET['date'] ?? date('Y-m-d', strtotime('+7 day'))) . ' 23:59',
    'level' => 2, 'important' => 1, 'status' => 'todo', 'note' => '',
];

// กรณีแก้ไข ดึงข้อมูลเดิมมา
if ($id != '') {
    $task = mysqli_fetch_assoc(query("SELECT * FROM tasks WHERE id = ? AND user_id = ?", [$id, $uid]));
    if (!$task) {
        header('Location: tasks.php');
        exit;
    }
}

$subjects = query("SELECT * FROM subjects WHERE user_id = ? ORDER BY code", [$uid]);

include 'header.php';
?>

<a href="tasks.php" class="back">‹ กลับไปหน้างานทั้งหมด</a>
<h1><?= $id ? 'แก้ไขงาน' : 'เพิ่มงาน' ?></h1>

<form method="post" class="box form">
    <label class="wide">ชื่องาน
        <input type="text" name="title" value="<?= h($task['title']) ?>" required placeholder="เช่น รายงานบทที่ 3">
    </label>

    <label>รายวิชา
        <select name="subject_id">
            <option value="">ไม่ระบุวิชา</option>
            <?php while ($s = mysqli_fetch_assoc($subjects)): ?>
                <option value="<?= $s['id'] ?>" <?= $task['subject_id'] == $s['id'] ? 'selected' : '' ?>>
                    <?= h($s['code'] . ' ' . $s['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </label>

    <label>ประเภทงาน
        <select name="type">
            <option value="solo" <?= $task['type'] == 'solo' ? 'selected' : '' ?>>งานเดี่ยว</option>
            <option value="group" <?= $task['type'] == 'group' ? 'selected' : '' ?>>งานกลุ่ม</option>
        </select>
    </label>

    <label>วันส่ง
        <input type="date" name="due_date" value="<?= date('Y-m-d', strtotime($task['due_date'])) ?>" required>
    </label>
    <label>เวลาส่ง
        <input type="time" name="due_time" value="<?= date('H:i', strtotime($task['due_date'])) ?>">
    </label>

    <div class="wide">
        <p class="label">ความยากของงาน <small class="muted">(ใช้คำนวณว่าควรเริ่มทำก่อนกี่วัน)</small></p>
        <div class="levels">
            <label><input type="radio" name="level" value="1" <?= $task['level'] == 1 ? 'checked' : '' ?>><span>ง่าย<small>เริ่มก่อน 1 วัน</small></span></label>
            <label><input type="radio" name="level" value="2" <?= $task['level'] == 2 ? 'checked' : '' ?>><span>ปานกลาง<small>เริ่มก่อน 3 วัน</small></span></label>
            <label><input type="radio" name="level" value="3" <?= $task['level'] == 3 ? 'checked' : '' ?>><span>ยาก<small>เริ่มก่อน 5 วัน</small></span></label>
        </div>
    </div>

    <label class="wide checkbox">
        <input type="checkbox" name="important" <?= $task['important'] ? 'checked' : '' ?>>
        งานนี้สำคัญ (คะแนนเยอะ หรือมีผลต่อเกรดมาก)
    </label>

    <label>สถานะ
        <select name="status">
            <option value="todo" <?= $task['status'] == 'todo' ? 'selected' : '' ?>>ยังไม่เริ่ม</option>
            <option value="doing" <?= $task['status'] == 'doing' ? 'selected' : '' ?>>กำลังทำ</option>
            <option value="done" <?= $task['status'] == 'done' ? 'selected' : '' ?>>เสร็จแล้ว</option>
        </select>
    </label>

    <label class="wide">บันทึกเพิ่มเติม
        <textarea name="note" rows="3" placeholder="ช่องทางที่สั่งงาน ลิงก์ หรือสิ่งที่ต้องเตรียม"><?= h($task['note']) ?></textarea>
    </label>

    <div class="wide">
        <button class="btn btn-main"><?= $id ? 'บันทึกการแก้ไข' : 'เพิ่มงาน' ?></button>
        <a href="tasks.php" class="btn">ยกเลิก</a>
    </div>
</form>

<?php if ($id): ?>
    <form method="post" action="tasks.php" onsubmit="return confirm('ต้องการลบงานนี้ใช่ไหม?')">
        <input type="hidden" name="delete_id" value="<?= $id ?>">
        <button class="btn btn-delete">ลบงานนี้</button>
    </form>
<?php endif; ?>

<?php include 'footer.php'; ?>
