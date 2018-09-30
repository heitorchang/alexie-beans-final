<?php

require_once("../db.php");

if (isset($_GET['id'])) {
    // don't care about checking for username, I am the only user
    $sql = "delete from transaction where id=:id";

    $stmt = $dbh->prepare($sql);
    $stmt->execute([':id' => $_GET['id']]);
}

header('Location: balances.php');
?>
