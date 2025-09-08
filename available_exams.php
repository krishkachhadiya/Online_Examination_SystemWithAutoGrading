<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

date_default_timezone_set('Asia/Kolkata');

if ($conn->connect_error) { die("DB error"); }

$serverNow = time();
$result = $conn->query("SELECT * FROM exams ORDER BY start_time ASC");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>Available Exams</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
body{display:flex;min-height:100vh;background:linear-gradient(135deg,#eef2ff,#f0f9ff);color:#1e293b;}

/* Sidebar */
.sidebar{width:260px;background:linear-gradient(180deg,#1e3a8a,#2563eb);color:white;padding:30px 20px;display:flex;flex-direction:column;border-top-right-radius:30px;border-bottom-right-radius:30px;box-shadow:6px 0 18px rgba(0,0,0,0.2);}
.sidebar h2{font-size:26px;margin-bottom:45px;text-align:center;color:#bfdbfe;font-weight:bold;}
.sidebar a{text-decoration:none;color:white;padding:14px 18px;margin:8px 0;border-radius:12px;font-size:16px;font-weight:500;display:flex;align-items:center;transition:0.3s;}
.sidebar a:hover{background: rgba(255,255,255,0.25);transform: translateX(8px) scale(1.03);}
.sidebar a.active{background: rgba(255,255,255,0.3);font-weight:bold;}

/* Main */
.main{flex:1;padding:40px;overflow-y:auto;}

/* Topbar */
.topbar{background: rgba(255,255,255,0.9);backdrop-filter: blur(12px);padding:18px 30px;border-radius:16px;box-shadow:0 8px 24px rgba(0,0,0,0.08);display:flex;justify-content:space-between;align-items:center;animation:fadeUp 0.6s ease;}
.topbar h1{font-size:26px;color:#1d4ed8;font-weight:bold;}
.logout-btn{background: linear-gradient(90deg,#ef4444,#dc2626); color:white;text-decoration:none;padding:10px 18px;border-radius:10px;font-weight:bold;transition:0.3s;}
.logout-btn:hover{background: linear-gradient(90deg,#dc2626,#b91c1c);transform: scale(1.07);}

/* Exam Cards */
.exam-cards{margin-top:40px;display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:24px;}
.exam-card{background: linear-gradient(135deg,#ffffff,#e0f2fe);padding:25px;border-radius:16px;box-shadow:0 10px 25px rgba(0,0,0,0.15);transition:transform 0.3s;animation:fadeUp 0.6s ease;}
.exam-card:hover{transform:scale(1.03);}
.exam-title{font-size:20px;color:#1e3a8a;margin-bottom:12px;}
.exam-info{color:#334155;margin-bottom:8px;}
.countdown{font-weight:700;color:#f97316;}
.start-btn{padding:10px 18px;background:#10b981;color:#fff;text-decoration:none;border-radius:10px;font-weight:700;display:inline-block;transition:.3s;}
.start-btn:hover{background:#059669;}
.completed{font-weight:700;color:#ef4444;}

/* Fade Up Animation */
@keyframes fadeUp{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}

/* Responsive */
@media (max-width:768px){.sidebar{display:none;}body{flex-direction:column;}.main{padding:20px;}}
</style>
</head>
<body>

<div class="sidebar">
    <h2>Student Panel</h2>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="available_exams.php" class="active">📝 Available Exams</a>
    <a href="my_results.php">📊 My Results</a>
    <a href="../logout.php">🚪 Logout</a>
</div>

<div class="main">
    <div class="topbar">
        <h1>Available Exams</h1>
        <a class="logout-btn" href="../logout.php">Logout</a>
    </div>

    <div class="exam-cards">
    <?php if($result && $result->num_rows > 0): ?>
        <?php while ($exam = $result->fetch_assoc()):
            $examId = isset($exam['exam_id']) ? (int)$exam['exam_id'] : (int)$exam['id'];
            $startTs = strtotime($exam['start_time']);
            $durationMin = (int)$exam['duration'];
            $endTs = $startTs + ($durationMin * 60);
        ?>
        
        <div class="exam-card" data-exam-id="<?= $examId ?>" data-start="<?= $startTs ?>" data-end="<?= $endTs ?>">
            <div class="exam-title"><?= htmlspecialchars($exam['title']) ?> (<?= htmlspecialchars($exam['subject']) ?>)</div>
            <div class="exam-info">🕒 Starts at: <?= date("d M Y, h:i A", $startTs) ?></div>
            <div class="exam-info">⏳ Duration: <?= $durationMin ?> minutes</div>
            <?php if ($serverNow < $startTs): ?>
                <div class="countdown">Starting in…</div>
            <?php elseif ($serverNow >= $startTs && $serverNow <= $endTs): ?>
                <a href="start_exam.php?exam_id=<?= $examId ?>" class="start-btn">Start Exam</a>
            <?php else: ?>
                <div class="completed">✅ Exam Completed</div>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="completed">No exams available.</div>
    <?php endif; ?>
    </div>
</div>

<script>
const serverNowMs = <?= $serverNow * 1000 ?>;
const clientLoadMs = Date.now();
const serverOffsetMs = serverNowMs - clientLoadMs;

function formatDhms(ms){
    const totalSec=Math.max(0,Math.floor(ms/1000));
    const days=Math.floor(totalSec/86400);
    const hours=Math.floor((totalSec%86400)/3600);
    const minutes=Math.floor((totalSec%3600)/60);
    const seconds=totalSec%60;
    const d = days>0 ? days+"d " : "";
    return d + String(hours).padStart(2,'0')+"h : "+String(minutes).padStart(2,'0')+"m : "+String(seconds).padStart(2,'0')+"s";
}

function tick(){
    const nowServerMs=Date.now()+serverOffsetMs;
    document.querySelectorAll(".exam-card").forEach(card=>{
        const startMs=parseInt(card.dataset.start,10)*1000;
        const endMs=parseInt(card.dataset.end,10)*1000;
        let dynamicEl = card.querySelector(".countdown, .start-btn, .completed");
        if(nowServerMs<startMs){
            if(!dynamicEl || !dynamicEl.classList.contains("countdown")){
                if(dynamicEl) dynamicEl.remove();
                dynamicEl=document.createElement("div");
                dynamicEl.className="countdown";
                card.appendChild(dynamicEl);
            }
            dynamicEl.textContent="Starts in "+formatDhms(startMs-nowServerMs);
        } else if(nowServerMs>=startMs && nowServerMs<=endMs){
            if(!dynamicEl || !dynamicEl.classList.contains("start-btn")){
                if(dynamicEl) dynamicEl.remove();
                const examId=card.dataset.examId;
                const a=document.createElement("a");
                a.className="start-btn";
                a.href="start_exam.php?exam_id="+encodeURIComponent(examId);
                a.textContent="Start Exam";
                card.appendChild(a);
            }
        } else {
            if(!dynamicEl || !dynamicEl.classList.contains("completed")){
                if(dynamicEl) dynamicEl.remove();
                dynamicEl=document.createElement("div");
                dynamicEl.className="completed";
                dynamicEl.textContent="✅ Exam Completed";
                card.appendChild(dynamicEl);
            }
        }
    });
}
tick();
setInterval(tick,1000);
</script>

</body>
</html>
