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

function currentBalance($dbh, $acct_id, $currency_id) {
    $get_debits_sql = "select sum(amount) as debits from transaction where debit = :acct_id and currency = :currency_id";
    $stmt = $dbh->prepare($get_debits_sql);
    $stmt->execute([":acct_id" => $acct_id,
                    ":currency_id" => $currency_id]);
    return $stmt->fetch()['debits'];
    
}

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

    // TODO: get sign to auto-compute dr/cr
    
    $acct_name_sql = "select a.name, at.sign from account a
inner join account_type at on a.account_type = at.id
where id = :id";
    $stmt = $dbh->prepare($acct_name_sql);
    $stmt->execute([":id" => $acct_id]);

    echo $stmt->fetch()['name'];
    
    ?>
    </b>

    <br><br>

    <table class="balances">
        
        <?php
        // Get current balances
        foreach ($user_currencies as $curr_id => $curr_name) {
            echo "<tr>";
            
            echo "<td>$curr_name</td>";

            echo "<td class='right_align'>";
            
            if ($cents[$curr_id] > 0) {
                echo separate_amount((int) currentBalance($dbh, $acct_id, $curr_id));
            } else {
                echo currentBalance($dbh, $acct_id, $curr_id);
            }
            
            echo "</td>";
            
            echo "</tr>";
        }
        ?>

    </table>

    <hr>
    
    Enter new currency and balance:

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

    <input name="new_balance">

    <br><br>
    Select other account:

    <select name="dr_cr">
        <option value="dr">debit</option>
        <option value="cr">credit</option>
    </select>

    <select name="other">
        <option value="1">Other acct</option>
    </select>
        

    
    
    <input type="submit">
</form>
