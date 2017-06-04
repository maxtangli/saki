<?php

use Saki\Game\Meld\MeldList;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Game\Tile\TileList;
use Saki\Util\MsTimer;

require_once __DIR__ . '/../bootstrap.php';

//$round = new Round();
//$round->process('mockHand E 123456789m12344s');
//$waitingAnalyzer = $round->getRule()->getWinAnalyzer()->getWaitingAnalyzer();
//
//// 2ms
//echo MsTimer::create()->measure(function () use ($waitingAnalyzer) {
//        $waitingAnalyzer->analyzePublic(TileList::fromString('123456789m1234s'), MeldList::fromString(''));
//    }) . "\n";
//
//// 20ms
//echo MsTimer::create()->measure(function () use ($waitingAnalyzer) {
//        $waitingAnalyzer->analyzePrivate(TileList::fromString('123456789m12344s'), MeldList::fromString(''));
//    }) . "\n";
//
//// 50ms
//echo MsTimer::create()->measure(function () use ($round) {
//        $round->getProcessor()->getProvider()->provideActorAll(SeatWind::createEast());
//    }) . "\n";

$client = new MongoDB\Client("mongodb://localhost:27017");
foreach ($client->listDatabases() as $db) {
    echo $db->getName() . "\n";
}