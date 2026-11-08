<?php
// หน้าภาพรวม (Dashboard)
require 'db.php';
check_login();
$page = 'index.php';
$uid = $_SESSION['user_id'];

// ดึงงานที่ยังไม่เสร็จทั้งหมด
$result = query("SELECT tasks.*, subjects.code, subjects.color
                 FROM tasks LEFT JOIN subjects ON tasks.subject_id = subjects.id
                 WHERE tasks.user_id = ? AND tasks.status != 'done'
                 ORDER BY tasks.due_date", [$uid]);
$tasks = [];
while ($row = mysqli_fetch_assoc($result)) {
    $tasks[] = task_info($row);
}

// นับงานที่เสร็จแล้ว
$done = mysqli_fetch_assoc(query("SELECT COUNT(*) AS n FROM tasks WHERE user_id = ? AND status = 'done'", [$uid]))['n'];

// แยกงานที่ควรเริ่มแล้ว และแบ่งงานลง 4 ช่อง
$start_now = [];
$quadrants = [1 => [], 2 => [], 3 => [], 4 => []];
foreach ($tasks as $t) {
    if ($t['should_start']) $start_now[] = $t;
    $quadrants[$t['quadrant']][] = $t;
}

// เตรียมข้อมูลแถบภาระงาน 14 วันข้างหน้า
$days = [];
for ($i = 0; $i < 14; $i++) {
    $d = date('Y-m-d', strtotime("+$i day"));
    $days[$d] = ['tasks' => [], 'total' => 0];
}
foreach ($tasks as $t) {
    $d = date('Y-m-d', strtotime($t['due_date']));
    if (isset($days[$d])) {
        $days[$d]['tasks'][] = $t;
        $days[$d]['total'] += $t['level']; // รวมความยาก
    }
}
$max = 6;
$clash = 0;
foreach ($days as $d) {
    if ($d['total'] > $max) $max = $d['total'];
    if (count($d['tasks']) >= 2) $clash++;
}

$day_names = ['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'];
$quad_names = [
    1 => ['สำคัญ และเร่งด่วน', 'ทำทันที'],
    2 => ['สำคัญ แต่ยังไม่เร่งด่วน', 'วางแผนเวลาทำ'],
    3 => ['เร่งด่วน แต่ไม่สำคัญมาก', 'ทำให้เสร็จเร็ว ๆ'],
    4 => ['ไม่เร่งด่วน ไม่สำคัญมาก', 'ทำเมื่อว่าง'],
];

include 'header.php';
?>

<p class="date-line">วันนี้ <?= thai_date(date('Y-m-d')) ?></p>
<h1>สวัสดี <?= h($_SESSION['user_name']) ?></h1>
<p class="lead">มีงานค้าง <b><?= count($tasks) ?> ชิ้น</b> ควรเริ่มทำตอนนี้ <b><?= count($start_now) ?> ชิ้น</b>
   <span class="muted">(ทำเสร็จแล้ว <?= $done ?> ชิ้น)</span></p>

<!-- แถบภาระงาน 14 วัน -->
<div class="box">
    <h2>ภาระงาน 14 วันข้างหน้า</h2>
    <p class="muted small">ความสูงของแท่ง = ความยากรวมของงานที่ส่งวันนั้น
        <?php if ($clash > 0): ?><br><b class="warn">มี <?= $clash ?> วันที่งานส่งชนกัน ควรเริ่มทำล่วงหน้า</b><?php endif; ?>
    </p>
    <div class="load">
        <?php foreach ($days as $date => $d): ?>
            <div class="day <?= count($d['tasks']) >= 2 ? 'clash' : '' ?> <?= $date == date('Y-m-d') ? 'today' : '' ?>">
                <div class="bar">
                    <div class="fill" style="height: <?= round($d['total'] / $max * 100) ?>%">
                        <?php foreach ($d['tasks'] as $t): ?>
                            <span style="background: <?= h($t['color'] ?? '#8C8676') ?>; flex: <?= $t['level'] ?>"
                                  title="<?= h($t['code'] . ' ' . $t['title']) ?>"></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <b><?= date('j', strtotime($date)) ?></b>
                <small><?= $day_names[date('w', strtotime($date))] ?></small>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="two-col">
    <!-- งานที่ควรเริ่มแล้ว -->
    <div class="box">
        <h2>ควรลงมือวันนี้</h2>
        <p class="muted small">งานยากควรเริ่มก่อนส่ง 5 วัน ปานกลาง 3 วัน ง่าย 1 วัน</p>
        <?php if (count($start_now) == 0): ?>
            <p class="muted">ยังไม่มีงานที่ต้องเร่ง</p>
        <?php else: ?>
            <ul class="task-list">
                <?php foreach ($start_now as $t) show_task($t); ?>
            </ul>
        <?php endif; ?>
    </div>

    <!-- Time Management Matrix -->
    <div class="box">
        <h2>จัดลำดับความสำคัญ</h2>
        <p class="muted small">ตาม Time Management Matrix (เร่งด่วน = ส่งภายใน 3 วัน)</p>
        <div class="matrix">
            <?php foreach ($quad_names as $q => $name): ?>
                <div class="quad q<?= $q ?>">
                    <h3><?= $name[0] ?> <small><?= $name[1] ?></small></h3>
                    <?php if (count($quadrants[$q]) == 0): ?>
                        <p class="muted small">ไม่มีงาน</p>
                    <?php endif; ?>
                    <?php foreach ($quadrants[$q] as $t): ?>
                        <a href="task_form.php?id=<?= $t['id'] ?>" style="--c: <?= h($t['color'] ?? '#8C8676') ?>">
                            <?= h($t['title']) ?> <small><?= $t['left_text'] ?></small>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
