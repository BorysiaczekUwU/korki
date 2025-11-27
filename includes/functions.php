<?php

require_once 'db.php';

function deleteExpiredTutoringRequests(mysqli $conn): int
{

    $sql = "DELETE FROM tutoring_requests WHERE proposed_date < NOW() AND status = 'pending'";


    if ($conn->query($sql) === TRUE) {

        return $conn->affected_rows;
    } else {

        return 0;
    }
}

?>