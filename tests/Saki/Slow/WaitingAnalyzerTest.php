<?php

use Saki\Meld\MeldList;
use Saki\Tile\TileList;

class WaitingAnalyzerTest extends \SakiTestCase {
    /**
     * @dataProvider publicDataProvider
     */
    function testPublicData(string $expected, string $public, string $melded) {
        $round = $this->getInitRound();
        $waitingAnalyzer = $round->getGameData()->getWinAnalyzer()->getWaitingAnalyzer();
        $waitingTileList = $waitingAnalyzer->analyzePublic(
            TileList::fromString($public),
            MeldList::fromString($melded)
        );
        $this->assertArrayList($expected, $waitingTileList);
    }

    function publicDataProvider() {
        return [
//            // not reachable
//            ['', '123456789m124sE', ''],
//            // 4+1
//            ['3s', '123456789m12sEE', ''],
//            ['14s', '123456789m23sWW', ''],
//            ['S', '123456789m123sS', ''],
//            ['S', '123456789mS', '123s'],
//            // 4+1 triple
//            ['E', '111222333444sE', ''], // #5 50ms
//            ['E', '111222333sE', '444s'], // #6 20ms
//            // seven pairs
//            ['1s', '133557799s1133m', ''],
//            ['', '133557799s1111m', ''],
//            // thirteen orphans
//            ['19m19p19sESWNCPF', '19m19p19sESWNCPF', ''], // #9 20ms
            // complex
            ['24567m', '3333444455566m', ''], // #10 100ms
        ];
    }

    /**
     * @dataProvider privateDataProvider
     */
    function testPrivateData(string $expected, string $private, string $melded) {
        $round = $this->getInitRound();
        $waitingAnalyzer = $round->getGameData()->getWinAnalyzer()->getWaitingAnalyzer();
        $futureWaitingList = $waitingAnalyzer->analyzePrivate(
            TileList::fromString($private),
            MeldList::fromString($melded)
        );
        $this->assertArrayList($expected, $futureWaitingList->toDiscardList());
    }

    function privateDataProvider() {
        return [
            // not reachable
            ['', '123456789m1249sE', ''], // #0 50ms
            // 4+1
            ['9s', '123456789m129sEE', ''], // #1 50ms
            // already win
            ['123sW', '123sWW', '123m,456m,789m'], // #2 10ms
        ];
    }
}