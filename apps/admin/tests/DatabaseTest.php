<?php

require __DIR__ . '/bootstrap.php';

use App\Core\Database\DB;

$result = DB::query('SELECT 1 AS test');

assertEquals(
  1,
  $result[0]['test'],
  'DB::query should fetch result'
);

$row = DB::one('SELECT 1 AS value');
assertEquals(
  1,
  $row['value'],
  'DB::one should return single row'
);

echo "DatabaseTest passed\n";
