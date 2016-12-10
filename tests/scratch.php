<?php

use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Util\MsTimer;

require_once __DIR__ . '/../bootstrap.php';

$round = new Round();
$round->process('mockHand E 123456789m12344s');
echo MsTimer::create()->measure(function () use ($round) {
    $round->getProcessor()->getProvider()->provideActorAll(SeatWind::createEast()); // 50ms
});