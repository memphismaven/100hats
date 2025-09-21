<?php
header('Content-Type: text/html');
$data = json_decode(file_get_contents('php://input'), true);

$local = $data['local'];
$live = $data['live'];
$syncType = $data['sync_type'];

function connect_db($conf) {
    $conn = new mysqli($conf['host'], $conf['user'], $conf['pass'], $conf['db']);
    if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
    return $conn;
}

$localConn = connect_db($local);
$liveConn = connect_db($live);

$localData = [];
$liveData = [];

function fetch_tbl_ITM($conn) {
    $items = [];
    $res = $conn->query("SELECT * FROM tbl_ITM");
    while ($row = $res->fetch_assoc()) {
        $items[$row['IID']] = $row;
    }
    return $items;
}

$localData = fetch_tbl_ITM($localConn);
$liveData = fetch_tbl_ITM($liveConn);

function print_item_diff($id, $localRow, $liveRow) {
    echo "<h3>Item IID: $id</h3><table border='1' cellpadding='5'><tr><th>Field</th><th>Local</th><th>Live</th><th>Keep</th></tr>";
    foreach ($localRow as $key => $localVal) {
        $liveVal = $liveRow[$key] ?? '';
        if ($localVal !== $liveVal) {
            echo "<tr><td>$key</td><td>$localVal</td><td>$liveVal</td>
                <td><input type='radio' name='{$id}_$key' value='local'> Local
                <input type='radio' name='{$id}_$key' value='live'> Live</td></tr>";
        }
    }
    echo "</table><br>";
}

switch ($syncType) {
    case 'local_to_live':
        foreach ($localData as $id => $row) {
            $cols = implode(',', array_keys($row));
            $vals = implode("','", array_map('addslashes', array_values($row)));
            $sql = "REPLACE INTO tbl_ITM ($cols) VALUES ('$vals')";
            $liveConn->query($sql);
        }
        echo "<p><strong>Local to Live sync complete.</strong></p>";
        break;

    case 'live_to_local':
        foreach ($liveData as $id => $row) {
            $cols = implode(',', array_keys($row));
            $vals = implode("','", array_map('addslashes', array_values($row)));
            $sql = "REPLACE INTO tbl_ITM ($cols) VALUES ('$vals')";
            $localConn->query($sql);
        }
        echo "<p><strong>Live to Local sync complete.</strong></p>";
        break;

    case 'most_recent':
        foreach ($localData as $id => $localRow) {
            $liveRow = $liveData[$id] ?? null;
            if ($liveRow) {
                if (strtotime($localRow['LastModified'] ?? '') > strtotime($liveRow['LastModified'] ?? '')) {
                    $cols = implode(',', array_keys($localRow));
                    $vals = implode("','", array_map('addslashes', array_values($localRow)));
                    $liveConn->query("REPLACE INTO tbl_ITM ($cols) VALUES ('$vals')");
                } else {
                    $cols = implode(',', array_keys($liveRow));
                    $vals = implode("','", array_map('addslashes', array_values($liveRow)));
                    $localConn->query("REPLACE INTO tbl_ITM ($cols) VALUES ('$vals')");
                }
            }
        }
        echo "<p><strong>Most Recent sync complete.</strong></p>";
        break;

    case 'review_all':
        echo "<form method='post' action='sync_finalize.php'>";
        $allKeys = array_unique(array_merge(array_keys($localData), array_keys($liveData)));
        foreach ($allKeys as $id) {
            $localRow = $localData[$id] ?? [];
            $liveRow = $liveData[$id] ?? [];
            if ($localRow != $liveRow) {
                print_item_diff($id, $localRow, $liveRow);
            }
        }
        echo "<button type='submit'>Finish Sync</button></form>";
        break;

    default:
        echo "Invalid sync type.";
        break;
}

$localConn->close();
$liveConn->close();
