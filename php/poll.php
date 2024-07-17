<?php
include 'db-connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answerId = intval($_POST['answerId']);
    $query = "UPDATE live_poll_results SET count = count + 1 WHERE id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $answerId);
    $stmt->execute();
    $stmt->close();
}

// $query = "SELECT * FROM live_poll_results WHERE question = 'Do you think Scotland will qualify from the group stage?'";
// $query = "SELECT * FROM live_poll_results WHERE question = 'What stage do you think England will reach?'";
$query = "SELECT * FROM live_poll_results WHERE question = 'Who should start as centre forward for England in the final?'";
$result = $con->query($query);

$pollData = [];
while ($row = $result->fetch_assoc()) {
    $pollData[] = $row;
}

echo json_encode($pollData);

$con->close();
?>