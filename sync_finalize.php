<?php
// Simple version that assumes local is correct unless 'live' is checked

$localConf = [
  'host' => 'localhost',
  'user' => 'root',
  'pass' => '',
  'db'   => 'danad2do'
];

$liveConf = [
  'host' => '100hatscom.ipowermysql.com',
  'user' => 'rpt',
  'pass' => '100H@tsStr0ngPwd!',
  'db'   => 'danad2do'
];

function connect_db($conf) {
  $conn = new mysqli($conf['host'], $conf['user'], $conf['pass'], $conf['db']);
  if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
  return $conn;
}

$localConn = connect_db($localConf);
$liveConn = connect_db($liveConf);

function fetch_by_id($conn, $id) {
  $res = $conn->query("SELECT * FROM tbl_ITM WHERE IID = $id");
  return $res->fetch_assoc();
}

foreach ($_POST as $field => $source) {
  list($id, $column) = explode('_', $field, 2);
  $localRow = fetch_by_id($localConn, $id);
  $liveRow  = fetch_by_id($liveConn, $id);

  $selectedValue = ($source == 'local') ? $localRow[$column] : $liveRow[$column];

  // Update both databases with the selected value
  $localConn->query("UPDATE tbl_ITM SET `$column` = '" . addslashes($selectedValue) . "' WHERE IID = $id");
  $liveConn->query("UPDATE tbl_ITM SET `$column` = '" . addslashes($selectedValue) . "' WHERE IID = $id");
}

$localConn->close();
$liveConn->close();

echo "<h2>Sync Finalized</h2><p>All selected changes have been applied to both databases.</p>";
