<?php
require_once("header.php");
require_once("formatters.php");

$id = $_GET['id'];

$sql = "select created, description, amount, debit, credit from transaction where id = :id";

$stmt = $dbh->prepare($sql);
$stmt->execute([":id" => $id]);

$row = $stmt->fetch();

$created = $row['created'];
$description = $row['description'];
$amount = $row['amount'];

echo "Really delete $created $description $amount?";

?>
<br><br>

<a href="del_tr.php?id=<?= $id ?>">Yes, delete</a>

<br><br><br><br>

<a href="balances.php">No, go back</a>
