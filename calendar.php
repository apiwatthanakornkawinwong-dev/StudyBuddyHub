<?php
// ปฏิทินรายเดือน
require 'db.php';
check_login();
$page = 'calendar.php';
$uid = $_SESSION['user_id'];

$month = $_GET['m'] ?? date('Y-m');            // เดือนที่แสดง เช่น 2026-10
$first_day = strtotime($month . '-01');
$days_in_month = date('t', $first_day);
$start_blank = date('w', $first_day);          // วันแรกของเดือนเป็นวันอะไร (0 = อาทิตย์)
$prev = date('Y-m', strtotime('-1 month', $first_day));
$next = date('Y-m', strtotime('+1 month', $first_day));

// ดึงงานในเดือนนี้ แล้วจัดกลุ่มตามวันที่
$result = query("SELECT tasks.*, subjects.code, subjects.color
                 FROM tasks LEFT JOIN subjects ON tasks.subject_id = subjects.id
                 WHERE tasks.user_id = ? AND DATE_FORMAT(tasks.due_date, '%Y-%m') = ?
                 ORDER BY tasks.due_date", [$uid, $month]);
$by_day = [];
while ($row = mysqli_fetch_assoc($result)) {
    $day = (int) date('j', strtotime($row['due_date']));
    $by_day[$day][] = $row;
}

$month_names = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];

include 'header.php';
?>

<div class="page-top">
    <div>
        <h1><?= $month_names[date('n', $first_day)] ?> <?= date('Y', $first_day) + 543 ?></h1>
        <p class="lead">กดที่ตัวเลขวันที่เพื่อเพิ่มงานในวันนั้น</p>
    </div>
    <div>
        <a href="?m=<?= $prev ?>" class="btn btn-sm">เดือนก่อน</a>
        <a href="calendar.php" class="btn btn-sm">เดือนนี้</a>
        <a href="?m=<?= $next ?>" class="btn btn-sm">เดือนถัดไป</a>
    </div>
</div>

<div class="box calendar-wrap">
    <div class="calendar">
        <?php foreach (['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'] as $dn): ?>
            <div class="cal-head"><?= $dn ?></div>
        <?php endforeach; ?>

        <?php for ($i = 0; $i < $start_blank; $i++): ?>
            <div class="cal-day empty"></div>
        <?php endfor; ?>

        <?php for ($day = 1; $day <= $days_in_month; $day++):
            $date = $month . '-' . str_pad($day, 2, '0', STR_PAD_LEFT); ?>
            <div class="cal-day <?= $date == date('Y-m-d') ? 'today' : '' ?>">
                <a href="task_form.php?date=<?= $date ?>" class="cal-num"><?= $day ?></a>
                <?php if (isset($by_day[$day])): ?>
                    <?php foreach ($by_day[$day] as $t): ?>
                        <a href="task_form.php?id=<?= $t['id'] ?>" class="cal-task <?= $t['status'] == 'done' ? 'done' : '' ?>"
                           style="--c: <?= h($t['color'] ?? '#8C8676') ?>" title="<?= h($t['title']) ?>">
                            <b><?= h($t['code']) ?></b> <?= h($t['title']) ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endfor; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
