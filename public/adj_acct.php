<?php

require_once("header.php");
require_once("formatters.php");

// Idea: display current balances in all currencies.
// Dropdown select desired currency,
// Text: desired new balance
// Dr/Cr is automatically computed based on sign and whether new
//   balance is less or more than current balance
// Dropdown select balancing acct (other acct)

?>

<?php

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

function displayAccountSelect($name, $accounts) {
    $select = "<select name='$name'>";
    $select .= "<option value='0'>choose acct</option>";
    foreach ($accounts as $account) {
        $select .= "<option value='{$account['id']}'>{$account["name"]}</option>";
    }
    $select .= "</select>";
    print($select);
}

// retrieve account ids
$sql = "select a.id, a.name
from account a
inner join user u on a.username = u.username
where a.username = :username
order by name";

$stmt = $dbh->prepare($sql);
$stmt->execute([":username" => $_SESSION['username']]);

$account_ids = $stmt->fetchAll();


?>

<?php
$acct_id = $_GET['acct'];

// load base currency from settings
$sql = "select value from settings where username = :username and name = 'base_currency'";
$stmt = $dbh->prepare($sql);
$stmt->execute([":username" => $_SESSION["username"]]);

$base_currency = $stmt->fetch()['value'];

// get user's currencies
$sql = "select id, code, cents from currency where username = :username";
$stmt = $dbh->prepare($sql);
$stmt->execute([":username" => $_SESSION["username"]]);

$user_currencies = [];
$cents = [];

foreach ($stmt as $currency_row) {
    $user_currencies[$currency_row['id']] = $currency_row['code'];
    $cents[$currency_row['id']] = $currency_row['cents'];
}

?>

<form action="adj_acct_exec.php" method="POST">

    Account balances for

    <b>
    <?php
    // Get account name and sign

    $acct_name_sql = "select a.name, at.sign from account a
inner join account_type at on a.account_type = at.id
where a.id = :id";
    $stmt = $dbh->prepare($acct_name_sql);
    $stmt->execute([":id" => $acct_id]);

    $row = $stmt->fetch();
    $sign = $row['sign'];
    
    echo $row['name'];
    
    ?>
    </b>

    <br>
    Acct sign: <?= $row['sign'] ?>

    <input type="hidden" name="acct_id" value="<?= $acct_id ?>">
    <input type="hidden" name="sign" value="<?= $row['sign'] ?>">

    <br><br>

    <table class="balances">
        
        <?php
        // Get current balances
        foreach ($user_currencies as $curr_id => $curr_name) {
            echo "<tr>";
            
            echo "<td>$curr_name</td>";

            echo "<td class='right_align'>";
            
            if ($cents[$curr_id] > 0) {
                echo separate_amount((int) currentBalance($dbh, $acct_id, $curr_id, $sign));
            } else {
                echo currentBalance($dbh, $acct_id, $curr_id, $sign);
            }
            
            echo "</td>";
            
            echo "</tr>";
        }
        ?>

    </table>

    <hr>

    <table>        
        <tr>
            <td>
                New balance:
            </td>
            <td>
                <select name="currency">
                    <?php
                    $sql = "select id, code from currency where username = :username";
                    $stmt = $dbh->prepare($sql);
                    $stmt->execute([":username" => $_SESSION["username"]]);
                    
                    foreach ($stmt as $currency_row) {
                        $selected = ($currency_row['id'] == $base_currency) ? " selected" : "";
                        print("<option value='{$currency_row['id']}'$selected>{$currency_row['code']}</option>");
                    }
                    
                    ?>
                </select>
                <input name="new_balance" autofocus>
                    
            </td>
        </tr>

        <tr>
            <td>
                Description</td><td><input name="desc"></td>
        </tr>

        <tr>
            <td>Other account:</td>
            <td>
                <?= displayAccountSelect("other", $account_ids) ?>
            </td>
        </tr>
    </table>
    
    <input type="submit">
</form>
