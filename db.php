<?php
// ไฟล์เชื่อมต่อฐานข้อมูล + ฟังก์ชันที่ใช้ร่วมกันทุกหน้า
session_start();
date_default_timezone_set('Asia/Bangkok');

// ตั้งค่า MySQL ของ XAMPP (ค่าเริ่มต้นคือ root ไม่มีรหัสผ่าน)
$conn = mysqli_connect('localhost', 'root', '', 'raomngan');
if (!$conn) {
    die('เชื่อมต่อฐานข้อมูลไม่ได้: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');

// รันคำสั่ง SQL แบบ prepared statement (กัน SQL Injection)
function query($sql, $params = []) {
    global $conn;
    $stmt = mysqli_prepare($conn, $sql);
    if ($params) {
        $types = str_repeat('s', count($params));
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

// กัน XSS เวลาแสดงข้อความ
function h($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

// ถ้ายังไม่ล็อกอินให้เด้งไปหน้า login
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// แปลงวันที่เป็นแบบไทย เช่น 18 ต.ค. 2569
function thai_date($date, $show_time = false) {
    $months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    $t = strtotime($date);
    $text = date('j', $t) . ' ' . $months[date('n', $t)] . ' ' . (date('Y', $t) + 543);
    if ($show_time) {
        $text .= ' ' . date('H:i', $t) . ' น.';
    }
    return $text;
}

// เหลืออีกกี่วันถึงวันส่ง (ติดลบ = เลยกำหนด)
function days_left($due_date) {
    $today = strtotime(date('Y-m-d'));
    $due = strtotime(date('Y-m-d', strtotime($due_date)));
    return (int) round(($due - $today) / 86400);
}

// ควรเริ่มทำกี่วันก่อนส่ง ตามความยาก: ง่าย 1 วัน, ปานกลาง 3 วัน, ยาก 5 วัน
function lead_days($level) {
    if ($level == 3) return 5;
    if ($level == 2) return 3;
    return 1;
}

// เติมข้อมูลที่คำนวณได้ลงไปในงานแต่ละชิ้น
function task_info($task) {
    $days = days_left($task['due_date']);
    $task['days'] = $days;

    if ($days < 0)       $task['left_text'] = 'เลยกำหนด ' . abs($days) . ' วัน';
    elseif ($days == 0)  $task['left_text'] = 'ส่งวันนี้';
    elseif ($days == 1)  $task['left_text'] = 'ส่งพรุ่งนี้';
    else                 $task['left_text'] = 'อีก ' . $days . ' วัน';

    // วันที่ควรเริ่มทำ
    $task['start_date'] = date('Y-m-d', strtotime($task['due_date'] . ' -' . lead_days($task['level']) . ' day'));
    $task['should_start'] = ($task['status'] != 'done' && $days <= lead_days($task['level']));

    // แบ่ง 4 ช่องตาม Time Management Matrix (เร่งด่วน = ส่งภายใน 3 วัน)
    $urgent = $days <= 3;
    if ($task['important'] && $urgent)       $task['quadrant'] = 1;
    elseif ($task['important'] && !$urgent)  $task['quadrant'] = 2;
    elseif (!$task['important'] && $urgent)  $task['quadrant'] = 3;
    else                                     $task['quadrant'] = 4;

    return $task;
}

// แสดงงาน 1 แถว (ใช้ทั้งหน้าแรกและหน้างานทั้งหมด)
function show_task($t) {
    $levels = [1 => 'ง่าย', 2 => 'ปานกลาง', 3 => 'ยาก'];
    $color = $t['color'] ?? '#8C8676';
    $class = 'task';
    if ($t['status'] == 'done') $class .= ' done';
    elseif ($t['days'] < 0)     $class .= ' overdue';
    ?>
    <li class="<?= $class ?>" style="--c: <?= h($color) ?>">
        <form method="post" action="tasks.php">
            <input type="hidden" name="toggle_id" value="<?= $t['id'] ?>">
            <input type="hidden" name="back" value="<?= h(basename($_SERVER['PHP_SELF'])) ?>">
            <button class="check" title="ทำเครื่องหมายว่าเสร็จ"><?= $t['status'] == 'done' ? '✓' : '' ?></button>
        </form>
        <a class="task-body" href="task_form.php?id=<?= $t['id'] ?>">
            <span class="task-title"><?= h($t['title']) ?></span>
            <span class="task-meta">
                <b class="subj"><?= h($t['code'] ?? 'ไม่ระบุวิชา') ?></b>
                <?= thai_date($t['due_date'], true) ?>
                <?php if ($t['type'] == 'group'): ?><span class="tag">งานกลุ่ม</span><?php endif; ?>
                <span class="tag lv<?= $t['level'] ?>"><?= $levels[$t['level']] ?></span>
                <?php if ($t['status'] == 'doing'): ?><span class="tag doing">กำลังทำ</span><?php endif; ?>
            </span>
        </a>
        <span class="task-left">
            <?php if ($t['status'] == 'done'): ?>
                เสร็จแล้ว
            <?php else: ?>
                <b><?= $t['left_text'] ?></b>
                <small><?= $t['should_start'] ? 'ควรเริ่มแล้ว' : 'เริ่ม ' . thai_date($t['start_date']) ?></small>
            <?php endif; ?>
        </span>
    </li>
    <?php
}
