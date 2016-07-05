<?php

use Saki\Game\Round;
use Saki\Game\RoundSerializer;

require_once __DIR__ . '/../bootstrap.php';

$r = new Round();
$s = new RoundSerializer();
var_dump($s->toJson($r));