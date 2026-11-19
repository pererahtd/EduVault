<?php
/** EduVault live dashboard data. Read-only, admin only. */
require __DIR__ . '/_auth.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
date_default_timezone_set('Asia/Colombo');

$dayList = [];
for ($i = 6; $i >= 0; $i--) $dayList[] = date('Y-m-d', strtotime("-$i days"));
$out = [
  'ok' => true,
  'generated_at' => date(DATE_ATOM),
  'days' => $dayList,
  'daily' => array_fill_keys($dayList, 0),
  'daily_sources' => [],
  'stats' => ['users'=>null, 'materials'=>null, 'pending'=>null, 'forums'=>null],
  'records' => ['users'=>[], 'materials'=>[], 'forums'=>[]],
  'unavailable' => [],
];
function ev_live_fail($part, $e = null) {
  global $out;
  $out['unavailable'][] = $part;
  if ($e) error_log('EduVault live dashboard '.$part.': '.$e->getMessage());
}
function ev_live_date($value) {
  if ($value === null || $value === '') return null;
  try {
    if (is_numeric($value)) {
      $n = (float)$value;
      if ($n > 100000000000) $n /= 1000;
      if ($n < 946684800) return null;
      return date('Y-m-d', (int)$n);
    }
    $t = strtotime((string)$value);
    return $t === false ? null : date('Y-m-d', $t);
  } catch (Throwable $e) { return null; }
}
function ev_live_add($day) {
  global $out;
  if ($day !== null && array_key_exists($day, $out['daily'])) $out['daily'][$day]++;
}
function ev_live_table($db,$name) {
  if (!preg_match('/^[a-z_][a-z0-9_]*$/i',$name)) return false;
  $escaped = $db->real_escape_string($name);
  $result = @$db->query("SHOW TABLES LIKE '$escaped'");
  return $result && $result->num_rows > 0;
}
function ev_live_columns($db,$name) {
  $cols=[];
  $result=@$db->query("SHOW COLUMNS FROM `".$name."`");
  if ($result) while ($row=$result->fetch_assoc()) $cols[]=$row['Field'];
  return $cols;
}
function ev_live_rows($db,$table,$fields,$limit=500) {
  $cols=ev_live_columns($db,$table);
  $available=array_values(array_intersect($fields,$cols));
  if (!$available) return [];
  $quoted=array_map(function($c){return '`'.$c.'`';},$available);
  $order=in_array('created_at',$cols,true) ? ' ORDER BY `created_at` DESC' : (in_array('id',$cols,true) ? ' ORDER BY `id` DESC' : '');
  $r=@$db->query('SELECT '.implode(',',$quoted).' FROM `'.$table.'`'.$order.' LIMIT '.(int)$limit);
  if (!$r) throw new RuntimeException($db->error);
  return $r->fetch_all(MYSQLI_ASSOC);
}
function ev_live_count($db,$table) {
  $r=@$db->query('SELECT COUNT(*) AS n FROM `'.$table.'`');
  if (!$r) throw new RuntimeException($db->error);
  return (int)($r->fetch_assoc()['n']??0);
}
// Connect using the existing root config.php without including pages that may exit/redirect.
$configFile=__DIR__.'/../config.php';
if (is_file($configFile)) {
  try {
    require_once $configFile;
    if (!class_exists('mysqli')) throw new RuntimeException('mysqli extension missing');
    $db = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, defined('DB_PORT') ? DB_PORT : 3306);
    if ($db->connect_errno) throw new RuntimeException('mysqli connection failed');
    $db->set_charset(defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4');
    foreach (['users'=>'users','forums'=>'forum_posts'] as $key=>$table) {
      if (!ev_live_table($db,$table)) { ev_live_fail($key); continue; }
      try {
        $out['stats'][$key]=ev_live_count($db,$table);
        $fields=$key==='users' ? ['id','name','role','created_at'] : ['id','title','type','status','author','category','created_at'];
        $rows=ev_live_rows($db,$table,$fields);
        $out['records'][$key]=$rows;
        foreach ($rows as $r) ev_live_add(ev_live_date($r['created_at']??null));
        $out['daily_sources'][]=$key;
      } catch (Throwable $e) {ev_live_fail($key,$e);}
    }
    $db->close();
  } catch (Throwable $e) { ev_live_fail('database',$e); }
} else ev_live_fail('database');
// Material storage is managed by the same module used by Study_Materials_Manage_Batch.php.
$materialFile=__DIR__.'/../materials_store.php';
if (is_file($materialFile)) {
  try {
    require_once $materialFile;
    if (!function_exists('mat_load')) throw new RuntimeException('mat_load() missing');
    $items=mat_load();
    if (!is_array($items)) throw new RuntimeException('Material storage invalid');
    $out['stats']['materials']=count($items);
    $out['stats']['pending']=0;
    foreach ($items as $m) {
      if (!is_array($m)) continue;
      if (strtolower(trim((string)($m['status']??'')))==='pending') $out['stats']['pending']++;
      $out['records']['materials'][]=[
        'id'=> (string)($m['id']??''), 'title'=>(string)($m['title']??$m['name']??''),
        'status'=>(string)($m['status']??''), 'subject'=>(string)($m['subject']??''),
        'created_at'=>(string)($m['created']??$m['createdAt']??$m['uploadedAt']??''),
      ];
      ev_live_add(ev_live_date($m['created']??$m['createdAt']??$m['uploadedAt']??null));
    }
    $out['daily_sources'][]='materials';
  } catch (Throwable $e) {ev_live_fail('materials',$e);}
} else ev_live_fail('materials');
$out['daily']=array_values($out['daily']);
$out['daily_sources']=array_values(array_unique($out['daily_sources']));
$out['unavailable']=array_values(array_unique($out['unavailable']));
echo json_encode($out, JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
