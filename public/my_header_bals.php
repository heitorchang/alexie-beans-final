<span style="color: #115911">

<?php

require_once("formatters.php");

// $acct_ids = [31, 4, 106, 3, 136];
$acct_ids = [31, 162, 3, 6, 4];
$names = ["31" => "brc",
          "4" => "itc",
	  "3" => "wal",
          "106" => "int",
	  "162" => "drw",
	  // "136" => "nubank",
          "6" => "itpou",
         ];  // hardcode to avoid running another query

$sql_for_header_total = "select sum(acct_amounts) as total from (select sum(amount) as acct_amounts from transaction where debit = :acct_id and currency=1 union all select -sum(amount) from transaction where credit = :acct_id_repeat and currency=1) acct_amounts";

$stmt = $dbh->prepare($sql_for_header_total);

foreach ($acct_ids as $id) {
  $stmt->execute([":acct_id" => $id,
		  ":acct_id_repeat" => $id]);
  foreach ($stmt as $row) {
    echo $names[$id] . " " . separate_amount($row['total']) . " *** ";
  }
}

?>

</span>
<br><br><br>
