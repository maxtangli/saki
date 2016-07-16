<?php

use Saki\Game\Round;

require_once __DIR__ . '/../bootstrap.php';

var_dump((new Round())->toJson());