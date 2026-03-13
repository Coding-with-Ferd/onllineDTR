<?php
include '../auth/db_connect.php';

date_default_timezone_set('Asia/Manila');

$labels = [];
$present = [];
$absent = [];

for($i = 6; $i >= 0; $i--){

$date = date('Y-m-d', strtotime("-$i days"));
$labels[] = date('M d', strtotime($date));

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM attendance WHERE attendance_date = ? AND status='present'");
$stmt->bind_param("s",$date);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

$present[] = $result['total'] ?? 0;

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM attendance WHERE attendance_date = ? AND status='absent'");
$stmt->bind_param("s",$date);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

$absent[] = $result['total'] ?? 0;

}

$totalEmployees = $conn->query("SELECT COUNT(*) as total FROM employees")->fetch_assoc()['total'];

$today = date('Y-m-d');

$presentToday = $conn->query("SELECT COUNT(*) as total FROM attendance WHERE attendance_date='$today' AND status='present'")->fetch_assoc()['total'];

$absentToday = $totalEmployees - $presentToday;

echo json_encode([
"labels"=>$labels,
"present"=>$present,
"absent"=>$absent,
"totalEmployees"=>$totalEmployees,
"presentToday"=>$presentToday,
"absentToday"=>$absentToday
]);