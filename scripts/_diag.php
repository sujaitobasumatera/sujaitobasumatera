<?php
// Diagnosis READ-ONLY sekali pakai. Dihapus sendiri setelah dijalankan.
if (($_GET['k'] ?? '') !== 'stsdiag2026') { http_response_code(404); exit; }
header('Content-Type: text/plain; charset=utf-8');
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$pattern = '/81323888207|81323888208|1323888207|082381118520|2381118520|laketoba|sujailake|sujai lake|7d6n|7 hari 6 malam|7 days 6 nights|7 hari|7 hari 6|Sisingamangaraja|1234567890/i';
echo "APP_ENV=".config('app.env')." DEBUG=".(config('app.debug') ? 'true' : 'false')." URL=".config('app.url')." LOCALE=".config('app.locale')." SESSION_COOKIE=".config('session.cookie')."\n";
echo "PHP ".PHP_VERSION."\n\n";
foreach (Schema::getTableListing() as $table) {
    $table = preg_replace('/^.*\./', '', $table);
    if (in_array($table, ['sessions','cache','cache_locks','jobs','failed_jobs','migrations','activity_logs','error_logs'])) continue;
    $cols = [];
    foreach (Schema::getColumns($table) as $c) {
        if (preg_match('/char|text|json/i', $c['type_name'] ?? $c['type'])) $cols[] = $c['name'];
    }
    if (!$cols) continue;
    $pk = Schema::hasColumn($table, 'id') ? 'id' : $cols[0];
    foreach (DB::table($table)->get() as $row) {
        foreach ($cols as $col) {
            $val = $row->$col ?? null;
            if (!is_string($val) || $val === '') continue;
            if (preg_match_all($pattern, $val, $m, PREG_OFFSET_CAPTURE)) {
                foreach (array_slice($m[0], 0, 6) as $hit) {
                    $s = max(0, $hit[1] - 50);
                    $snip = preg_replace('/\s+/', ' ', substr($val, $s, 120));
                    echo "$table.$col #".($row->$pk ?? '?')." [$hit[0]] ...$snip...\n";
                }
            }
        }
    }
}
echo "\n--- settings keys ---\n";
foreach (DB::table('settings')->get(['id','key']) as $s) echo "$s->id $s->key\n";
echo "\n--- general (non-secret view) ---\n";
$g = DB::table('settings')->where('key','general')->value('value');
$g = is_string($g) ? json_decode($g, true) : $g;
foreach (['site_name','contact_phone','contact_whatsapp','contact_whatsapp_2','contact_email','office_address','seo_meta_title','seo_meta_desc','seo_pseo_origins','operating_hours'] as $k) echo "$k = ".json_encode($g[$k] ?? null, JSON_UNESCAPED_UNICODE)."\n";
echo "\n--- company ---\n";
$c = DB::table('settings')->where('key','company')->value('value');
echo (is_string($c) ? $c : json_encode($c, JSON_UNESCAPED_UNICODE))."\n";
echo "\n--- packages ---\n";
foreach (DB::table('packages')->get(['id','slug','name','duration','status']) as $p) echo "$p->id | $p->slug | $p->name | $p->duration | $p->status\n";
@unlink(__FILE__);
echo "\n[diag dihapus]\n";
