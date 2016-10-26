<?php

use Saki\Game\Meld\MeldList;
use Saki\Game\Tile\TileList;

class WaitingAnalyzerTest extends \SakiTestCase {
    /**
     * @dataProvider publicDataProvider
     */
    function testPublicData(string $expected, string $public, string $melded) {
        $round = $this->getInitRound();
        $waitingAnalyzer = $round->getRule()->getWinAnalyzer()->getWaitingAnalyzer();
        $waitingTileList = $waitingAnalyzer->analyzePublic(
            TileList::fromString($public),
            MeldList::fromString($melded)
        );
        $this->assertArrayList($expected, $waitingTileList);
    }

    function publicDataProvider() {
        return [
            // not reachable
            ['', '123456789m124sE', ''],
            // 4+1
            ['3s', '123456789m12sEE', ''],
            ['14s', '123456789m23sWW', ''],
            ['S', '123456789m123sS', ''],
            ['S', '123456789mS', '123s'],
            // 4+1 triple
            ['E', '111222333444sE', ''], // #5 20ms
            ['E', '111222333sE', '444s'], // #6 10ms
            // seven pairs
            ['1s', '133557799s1133m', ''],
            ['', '133557799s1111m', ''],
            // thirteen orphans
            ['19m19p19sESWNCPF', '19m19p19sESWNCPF', ''], // #9 10ms
            // complex
            ['24567m', '3333444455566m', ''], // #10 40ms
        ];
    }

    /**
     * @dataProvider privateDataProvider
     */
    function testPrivateData(string $expected, string $private, string $melded) {
        $round = $this->getInitRound();
        $waitingAnalyzer = $round->getRule()->getWinAnalyzer()->getWaitingAnalyzer();
        $futureWaitingList = $waitingAnalyzer->analyzePrivate(
            TileList::fromString($private),
            MeldList::fromString($melded)
        );
        $this->assertArrayList($expected, $futureWaitingList->toDiscardList());
    }

    function privateDataProvider() {
        return [
            // not reachable
            ['', '123456789m1249sE', ''], // #0 20ms
            // 4+1
            ['9s', '123456789m129sEE', ''], // #1 20ms
            // already win
            ['123sW', '123sWW', '123m,456m,789m'],
        ];
    }
}