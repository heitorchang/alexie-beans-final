<?php

require_once("header.php");
require_once("formatters.php");
?>

<form action="adj_acct.php" method="GET">
    Select account:
    <select name="acct">
        
<?php 

// get all accounts
$all_accounts_sql = "select id, name from account where username = :username order by name";
$stmt = $dbh->prepare($all_accounts_sql);
$stmt->execute([":username" => $_SESSION["username"]]);


foreach ($stmt as $row) {
    $acct_id = $row['id'];
    $acct_name = $row['name'];
    echo "<option value='$acct_id'>$acct_name</option>\n";
}

?>

    </select>

    <input type="submit">
</form>
