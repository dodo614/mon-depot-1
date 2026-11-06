<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$user = currentUser();
$db = getDB();

$month = (int)($_GET['m'] ?? date('n'));
$year = (int)($_GET['y'] ?? date('Y'));
if ($month < 1) { $month = 12; $year--; }
if ($month > 12) { $month = 1; $year++; }

$firstDay = new DateTime("$year-$month-01");
$daysInMonth = (int)$firstDay->format('t');
$startWeekday = (int)$firstDay->format('N'); // 1 (lundi) - 7 (dimanche)

$stmt = $db->prepare("SELECT * FROM tasks WHERE user_id = ? AND due_date IS NOT NULL AND MONTH(due_date) = ? AND YEAR(due_date) = ?");
$stmt->execute([$user['id'], $month, $year]);
$tasksByDay = [];
foreach ($stmt->fetchAll() as $t) {
    $day = (int)date('j', strtotime($t['due_date']));
    $tasksByDay[$day][] = $t;
}

$moisNoms = ['', 'Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

$pageTitle = "Calendrier";
$activeMenu = "calendar";
include __DIR__ . '/includes/header.php';
?>
<div class="card fade-in">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
        <a href="?m=<?= $month-1 ?>&y=<?= $year ?>" class="btn btn-secondary">← Précédent</a>
        <strong style="font-size:18px;"><?= $moisNoms[$month] ?> <?= $year ?></strong>
        <a href="?m=<?= $month+1 ?>&y=<?= $year ?>" class="btn btn-secondary">Suivant →</a>
    </div>
    <div style="display:grid; grid-template-columns:repeat(7,1fr); gap:6px; text-align:center; font-weight:700; font-size:13px; color:#8a93a6; margin-bottom:8px;">
        <div>Lun</div><div>Mar</div><div>Mer</div><div>Jeu</div><div>Ven</div><div>Sam</div><div>Dim</div>
    </div>
    <div style="display:grid; grid-template-columns:repeat(7,1fr); gap:6px;">
        <?php for ($i = 1; $i < $startWeekday; $i++): ?>
            <div></div>
        <?php endfor; ?>
        <?php for ($d = 1; $d <= $daysInMonth; $d++):
            $isToday = $d == date('j') && $month == date('n') && $year == date('Y');
        ?>
            <div style="min-height:80px; border:1px solid #eef1f6; border-radius:10px; padding:6px; <?= $isToday ? 'border-color:var(--primary); background:var(--primary-light);' : '' ?>">
                <div style="font-size:12px; font-weight:700; margin-bottom:4px;"><?= $d ?></div>
                <?php if (!empty($tasksByDay[$d])): foreach ($tasksByDay[$d] as $t): ?>
                    <a href="edit_task.php?id=<?= (int)$t['id'] ?>" style="display:block; font-size:11px; background:var(--primary); color:#fff; border-radius:6px; padding:2px 5px; margin-bottom:2px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        <?= e($t['title']) ?>
                    </a>
                <?php endforeach; endif; ?>
            </div>
        <?php endfor; ?>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
