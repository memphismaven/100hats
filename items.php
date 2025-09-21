<?php
// items.php — mysqli version

require 'db.php'; // must define $conn = new mysqli(...);

// ---------- helper ----------
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function toNull($v){ $v = trim((string)$v); return ($v==='' ? null : $v); }

// ---------- AJAX: parent item lookup ----------
if (isset($_GET['action']) && $_GET['action'] === 'lookup') {
    header('Content-Type: application/json; charset=utf-8');
    $term    = isset($_GET['term']) ? trim($_GET['term']) : '';
    $exclude = isset($_GET['exclude']) ? (int)$_GET['exclude'] : 0;

    if ($term === '') { echo '[]'; exit; }

    $sql = "SELECT IID, ITM
              FROM tbl_ITM
             WHERE ITM LIKE ?
               AND (? = 0 OR IID <> ?)
             ORDER BY ITM
             LIMIT 25";
    $stmt = $conn->prepare($sql);
    $like = "%{$term}%";
    $stmt->bind_param("sii", $like, $exclude, $exclude);
    $stmt->execute();
    $res = $stmt->get_result();

    $out = [];
    while ($row = $res->fetch_assoc()) {
        $out[] = ['id'=>(int)$row['IID'], 'text'=>$row['ITM']];
    }
    echo json_encode($out);
    exit;
}

// ---------- handle save (insert/update) ----------
$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? '';
    $IID  = isset($_POST['IID']) ? (int)$_POST['IID'] : 0;

    $ITM       = $_POST['ITM'] ?? '';
    $PIID      = toNull($_POST['PIID'] ?? '');
    $ITID      = toNull($_POST['ITID'] ?? '');
    $Favorite  = ($_POST['Favorite'] ?? '') === '1' ? 1 : 0;
    $DueDate   = toNull($_POST['DueDate'] ?? '');
    $Completed = toNull($_POST['Completed'] ?? '');
    $SortOrder = toNull($_POST['SortOrder'] ?? '');
    $EstQty    = toNull($_POST['EstQty'] ?? '');
    $UOM       = toNull($_POST['UOM'] ?? '');
    $Score     = toNull($_POST['Score'] ?? '');

    if ($mode === 'update' && $IID > 0) {
        $sql = "UPDATE tbl_ITM
                   SET ITM=?, PIID=?, ITID=?, Favorite=?, DueDate=?, Completed=?, SortOrder=?,
                       EstQty=?, UOM=?, Score=?
                 WHERE IID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siiiiiiiiii", $ITM, $PIID, $ITID, $Favorite, $DueDate, $Completed,
                                           $SortOrder, $EstQty, $UOM, $Score, $IID);
        $stmt->execute();
        $notice = "Item updated.";
        $_GET['id'] = (string)$IID;
    } elseif ($mode === 'insert') {
        $sql = "INSERT INTO tbl_ITM
                   (ITM, PIID, ITID, Favorite, DueDate, Completed, SortOrder, EstQty, UOM, Score)
                VALUES (?,?,?,?,?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siiiiiiiii", $ITM, $PIID, $ITID, $Favorite, $DueDate, $Completed,
                                        $SortOrder, $EstQty, $UOM, $Score);
        $stmt->execute();
        $newId = $conn->insert_id;
        header("Location: ".$_SERVER['PHP_SELF']."?id=".$newId."&n=1");
        exit;
    }
}

// ---------- fetch selected item ----------
$editing = null;
if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM tbl_ITM WHERE IID=?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $res = $stmt->get_result();
    $editing = $res->fetch_assoc();
}
if (!$notice && isset($_GET['n'])) { $notice = 'Item added.'; }

// ---------- search results ----------
$results = [];
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($q !== '' && !$editing) {
    $sql = "SELECT IID, ITM, DueDate, Completed
              FROM tbl_ITM
             WHERE ITM LIKE ?
             ORDER BY ITM
             LIMIT 50";
    $stmt = $conn->prepare($sql);
    $like = "%{$q}%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) { $results[] = $row; }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Items</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; margin: 18px; }
  form.inline { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
  .notice { background:#eefbe7; border:1px solid #b7e3a1; padding:8px 10px; margin-bottom:12px; border-radius:6px; }
  .list { margin-top:12px; border:1px solid #ddd; border-radius:8px; overflow:hidden; }
  .row { display:flex; justify-content:space-between; padding:10px 12px; border-top:1px solid #eee; }
  .row:first-child { border-top:none; }
  .row a { text-decoration:none; }
  .btn { padding:8px 12px; border:1px solid #aaa; border-radius:6px; background:#f7f7f7; cursor:pointer; }
  .btn-primary { border-color:#2f6fed; background:#2f6fed; color:#fff; }
  fieldset { border:1px solid #ddd; padding:12px; border-radius:8px; }
  label { display:block; margin-top:10px; font-weight:600; }
  input[type="text"], input[type="number"], input[type="date"] { width: min(520px, 95%); padding:7px; }
  .grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:12px; }
  small.mono { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; color:#666;}
</style>
</head>
<body>

<?php if ($notice): ?>
  <div class="notice"><?= h($notice) ?></div>
<?php endif; ?>

<!-- Top search -->
<form class="inline" method="get" action="">
  <input type="text" name="q" value="<?= h($q) ?>" placeholder="Search items by name…" autofocus>
  <button class="btn">Search</button>
  <a class="btn" href="<?= h($_SERVER['PHP_SELF']) ?>?add=1">Add New</a>
</form>

<?php if ($results): ?>
  <div class="list">
    <?php foreach ($results as $r): ?>
      <div class="row">
        <div>
          <a href="?id=<?= (int)$r['IID'] ?>"><strong><?= h($r['ITM']) ?></strong></a>
          <div><small class="mono">ID <?= (int)$r['IID'] ?></small></div>
        </div>
        <div><a class="btn" href="?id=<?= (int)$r['IID'] ?>">Select</a></div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php
if ($editing || isset($_GET['add'])):
  $isEdit = (bool)$editing;
  $item = $editing ?: [
      'IID'=>0,'ITM'=>'','PIID'=>null,'ITID'=>null,'Favorite'=>0,'DueDate'=>null,'Completed'=>null,
      'SortOrder'=>null,'EstQty'=>null,'UOM'=>null,'Score'=>null
  ];

  $piText = '';
  if (!empty($item['PIID'])) {
      $stmt = $conn->prepare("SELECT ITM FROM tbl_ITM WHERE IID=?");
      $stmt->bind_param("i", $item['PIID']);
      $stmt->execute();
      $res = $stmt->get_result();
      $piText = (string)($res->fetch_column() ?: '');
  }
?>
  <h2><?= $isEdit ? 'Edit Item' : 'Add New Item' ?></h2>
  <form method="post" action="">
    <?php if ($isEdit): ?>
      <input type="hidden" name="IID" value="<?= (int)$item['IID'] ?>">
      <div><small class="mono">Editing ID <?= (int)$item['IID'] ?></small></div>
    <?php endif; ?>
    <input type="hidden" name="mode" value="<?= $isEdit ? 'update' : 'insert' ?>">

    <fieldset>
      <div class="grid">
        <div>
          <label for="ITM">Name (ITM)</label>
          <input type="text" id="ITM" name="ITM" required maxlength="50" value="<?= h($item['ITM']) ?>">
        </div>

        <div>
          <label for="PIID_text">Parent Item (PIID)</label>
          <input id="PIID_text" name="PIID_text" value="<?= h($piText) ?>" autocomplete="off" list="itemList">
          <input type="hidden" name="PIID" id="PIID" value="<?= h($item['PIID']) ?>">
          <datalist id="itemList"></datalist>
          <small>Type 2+ chars, choose from suggestions.</small>
        </div>

        <div>
          <label for="ITID">Item Type ID (ITID)</label>
          <input type="number" id="ITID" name="ITID" value="<?= h($item['ITID']) ?>">
        </div>

        <div>
          <label><input type="checkbox" name="Favorite" value="1" <?= ($item['Favorite'] ? 'checked' : '') ?>> Favorite</label>
        </div>

        <div>
          <label for="DueDate">Due Date</label>
          <input type="date" id="DueDate" name="DueDate" value="<?= h($item['DueDate']) ?>">
        </div>

        <div>
          <label for="Completed">Completed</label>
          <input type="date" id="Completed" name="Completed" value="<?= h($item['Completed']) ?>">
        </div>

        <div>
          <label for="SortOrder">Sort Order</label>
          <input type="number" id="SortOrder" name="SortOrder" value="<?= h($item['SortOrder']) ?>">
        </div>

        <div>
          <label for="EstQty">Est Qty</label>
          <input type="number" id="EstQty" name="EstQty" value="<?= h($item['EstQty']) ?>">
        </div>

        <div>
          <label for="UOM">UOM</label>
          <input type="number" id="UOM" name="UOM" value="<?= h($item['UOM']) ?>">
        </div>

        <div>
          <label for="Score">Score</label>
          <input type="number" id="Score" name="Score" value="<?= h($item['Score']) ?>">
        </div>
      </div>
    </fieldset>

    <p style="margin-top:12px;">
      <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Save Changes' : 'Add Item' ?></button>
      <a class="btn" href="<?= h($_SERVER['PHP_SELF']) ?>">Done</a>
    </p>
  </form>

  <script>
  (function(){
    const text   = document.getElementById('PIID_text');
    const hidden = document.getElementById('PIID');
    const list   = document.getElementById('itemList');
    const excludeId = <?= (int)($item['IID'] ?? 0) ?>;

    let timer = null;
    function debounce(fn, ms){ return (...a)=>{ clearTimeout(timer); timer=setTimeout(()=>fn(...a), ms); }; }
    function escapeHtml(s){ return s.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

    async function runLookup(q){
      if (!q || q.length < 2) { list.innerHTML=''; return; }
      const url = `<?= h($_SERVER['PHP_SELF']) ?>?action=lookup&term=${encodeURIComponent(q)}&exclude=${excludeId}`;
      const res = await fetch(url, {headers:{'Accept':'application/json'}});
      if (!res.ok) return;
      const rows = await res.json();
      list.innerHTML = rows.map(r => `<option data-id="${r.id}" value="${escapeHtml(r.text)}"></option>`).join('');
    }
    const search = debounce(runLookup, 250);

    function syncHidden(){
      const opts = list.querySelectorAll('option');
      let found = '';
      for (const o of opts) { if (o.value === text.value) { found = o.dataset.id; break; } }
      hidden.value = found; // '' if no exact match
    }

    text.addEventListener('input', e => { search(e.target.value); syncHidden(); });
    text.addEventListener('change', syncHidden);

    const form = text.closest('form');
    if (form) form.addEventListener('submit', e => {
      if (text.value && !hidden.value) {
        e.preventDefault();
        alert('Please pick a valid Parent from the suggestions (or clear the field).');
        text.focus();
      }
    });
  })();
  </script>
<?php endif; ?>

</body>
</html>