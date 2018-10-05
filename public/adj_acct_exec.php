<?php
session_start();
require_once("../db.php");
require_once("../util.php");
require_once("formatters.php");

// echo "adj bal";

// set default timezone
date_default_timezone_set('America/Sao_Paulo');

$target_acct_id = $_POST['acct_id'];
$sign = $_POST['sign'];
$currency = $_POST['currency'];
$new_bal = $_POST['new_balance'];
$desc = $_POST['desc'];
$other = $_POST['other'];

$date_created = date('Y-m-d');
$time_created = date('H:i:s');

// determine whether amount in cents should be parsed
$stmt = $dbh->prepare("select cents from currency where id = :id");
$stmt->execute([":id" => $_POST["currency"]]);
$cents_row = $stmt->fetch();

if ((int) $cents_row['cents'] !== 0) {
    $new_bal = parse_amount($new_bal);
}

// get current balance
function currentBalance($dbh, $acct_id, $currency_id, $sign) {    
    $get_debits_sql = "select sum(amount) as debits from transaction where debit = :acct_id and currency = :currency_id";
    $get_credits_sql = "select sum(amount) as credits from transaction where credit = :acct_id and currency = :currency_id";
    
    $stmt = $dbh->prepare($get_debits_sql);
    $stmt->execute([":acct_id" => $acct_id,
                    ":currency_id" => $currency_id]);
    $dr_amt = $stmt->fetch()['debits'];

    $stmt = $dbh->prepare($get_credits_sql);
    $stmt->execute([":acct_id" => $acct_id,
                    ":currency_id" => $currency_id]);
    $cr_amt = $stmt->fetch()['credits'];

    return $sign * ($dr_amt - $cr_amt);
}


$cur_bal = currentBalance($dbh, $target_acct_id, $currency, $sign);

$diff = $new_bal - $cur_bal;

if ($diff < 0) {
    $diff_sign = -1;
} else {
    $diff_sign = 1;
}

if ($diff_sign * $sign == 1) {
    $debit = $target_acct_id;
    $credit = $other;    
} else {
    $debit = $other;
    $credit = $target_acct_id;
}

$datetime = $date_created . " " . $time_created;

$diff = abs($diff);

$sql = "insert into transaction (username, description, currency, amount, debit, credit, created)
values (:username, :description, :currency, :amount, :debit, :credit, :created)";

$stmt = $dbh->prepare($sql);
$stmt->execute([":username" => $_SESSION['username'],
                ":description" => $desc,
                ":currency" => $currency,
                ":amount" => $diff,
                ":debit" => $debit,
                ":credit" => $credit,
                ":created" => $datetime]);

ob_end_clean();
header('Location: balances.php');
