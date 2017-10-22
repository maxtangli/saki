<?php

require_once __DIR__ . '/../bootstrap.php';

// c 14 / 9x4

$a = [];
for ($i = 1; $i < 10; ++$i) {
    $s = '' . $i;
    if (strpos('0', $s) !== false) continue;

    $parts = str_split($s);
    sort($parts);
    $key = implode('', $parts);

    $a[$key] = $key;
}
//echo count($a);
// echo 996 * pow(3, 10);
//echo 19440 * pow(3,7);

// 1 10 9
// 2 100 63
// 3 1000 282
// 4 10000 996
// 5 100000 2997
// 6 1000000 8001
// 7 10000000 19440
// 8
// 9
// 10
// 11
// 12
// 13
// 14 < 996 * 3^11 = 58812804; < 19440 x 3^7 = 42515280