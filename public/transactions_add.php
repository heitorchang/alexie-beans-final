<?php
session_start();
require_once("../db.php");
require_once("formatters.php");

// insert a transaction if form was submitted
if (isset($_POST['description'])) {
    $datetime = $_POST['date'] . " " . $_POST['time'];

    // determine whether amount in cents should be parsed
    $stmt = $dbh->prepare("select cents from currency where id = :id");
    $stmt->execute([":id" => $_POST["currency"]]);
    $cents_row = $stmt->fetch();

    $amount = $_POST['amount'];
    
    if ((int) $cents_row['cents'] !== 0) {
        $amount = parse_amount($amount);
    }

    $sql = "insert into transaction (username, description, currency, amount, debit, credit, created)
values (:username, :description, :currency, :amount, :debit, :credit, :created)";

    $stmt = $dbh->prepare($sql);
    $stmt->execute([":username" => $_SESSION['username'],
                    ":description" => $_POST['description'],
                    ":currency" => $_POST['currency'],
                    ":amount" => $amount,
                    ":debit" => $_POST['debit'],
                    ":credit" => $_POST['credit'],
                    ":created" => $datetime]);
}

ob_end_clean();
header('Location: transactions.php');
