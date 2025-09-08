<?php
session_start();
require("../includes/db.php");
require("../fpdf/fpdf.php");
include("../includes/db.php");

// Check student login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    die("Unauthorized access.");
}

$student_id = $_SESSION['student_id'];

// Fetch student info
$studentQuery = $conn->prepare("SELECT * FROM students WHERE id = ?");
$studentQuery->bind_param("i", $student_id);
$studentQuery->execute();
$student = $studentQuery->get_result()->fetch_assoc();

// Fetch student results
$query = "
    SELECT r.id AS result_id, r.score, r.total_questions, 
           e.title, e.subject, e.start_time
    FROM results r
    JOIN exams e ON r.exam_id = e.exam_id
    WHERE r.student_id = ?
    ORDER BY e.start_time DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$results = $stmt->get_result();

// Function to calculate grade
function calculateGrade($percentage) {
    if ($percentage >= 90) return "A+";
    elseif ($percentage >= 80) return "A";
    elseif ($percentage >= 70) return "B";
    elseif ($percentage >= 60) return "C";
    elseif ($percentage >= 50) return "D";
    else return "F";
}

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();

// Title
$pdf->SetFont('Arial','B',18);
$pdf->Cell(0,10,"Student Marksheet",0,1,'C');
$pdf->Ln(5);

// Student Info
$pdf->SetFont('Arial','',12);
$pdf->Cell(50,8,"Name: ",0,0);
$pdf->Cell(0,8,$student['name'],0,1);
$pdf->Cell(50,8,"Student ID (Roll No): ",0,0);
$pdf->Cell(0,8,$student['id'],0,1);
$pdf->Cell(50,8,"Email: ",0,0);
$pdf->Cell(0,8,$student['email'],0,1);
$pdf->Cell(50,8,"Generated On: ",0,0);
$pdf->Cell(0,8,date("d-m-Y H:i"),0,1);
$pdf->Ln(8);

// Table Header
$pdf->SetFont('Arial','B',12);
$pdf->Cell(50,10,"Exam Title",1);
$pdf->Cell(30,10,"Subject",1);
$pdf->Cell(30,10,"Date",1);
$pdf->Cell(30,10,"Marks",1);
$pdf->Cell(25,10,"Percent",1);
$pdf->Cell(25,10,"Grade",1);
$pdf->Ln();

// Table Rows
$pdf->SetFont('Arial','',11);

while ($row = $results->fetch_assoc()) {
    $score = $row['score'];
    $total = $row['total_questions'];
    $percentage = ($total > 0) ? round(($score / $total) * 100, 2) : 0;
    $grade = calculateGrade($percentage);

    $pdf->Cell(50,10,$row['title'],1);
    $pdf->Cell(30,10,$row['subject'],1);
    $pdf->Cell(30,10,date("d-m-Y", strtotime($row['start_time'])),1);
    $pdf->Cell(30,10,"$score/$total",1);
    $pdf->Cell(25,10,"$percentage%",1);
    $pdf->Cell(25,10,$grade,1);
    $pdf->Ln();
}

// Output PDF
$pdf->Output("D","student_results.pdf"); // Force download
?>
