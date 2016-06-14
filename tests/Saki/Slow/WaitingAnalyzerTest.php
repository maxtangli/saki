<?php

use Saki\Meld\MeldList;
use Saki\Tile\TileList;
use Saki\Util\ArrayList;
use Saki\Win\Waiting\FutureWaiting;

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
            // not reachable
            ['', '123456789m124sE', ''],
            // 4+1
            ['3s', '123456789m12sEE', ''],
            ['14s', '123456789m23sWW', ''],
            ['S', '123456789m123sS', ''],
            ['S', '123456789mS', '123s'],
            // 4+1 triple
            ['E', '111222333444sE', ''],
            ['E', '111222333sE', '444s'],
            // seven pairs
            ['1s', '133557799s1133m', ''],
            ['', '133557799s1111m', ''],
            // thirteen orphans todo
            ['19m19p19sESWNCPF', '19m19p19sESWNCPF', ''],
            // complex
            ['24567m', '3333444455566m', ''],
        ];
    }

    /**
     * @dataProvider privateDataProvider
     */
    function testPrivateData($onHandTileListString, $meldedString, $expected) {
        $round = $this->getInitRound();
        $waitingAnalyzer = $round->getGameData()->getWinAnalyzer()->getWaitingAnalyzer();
        $private = TileList::fromString($onHandTileListString);
        $melded = MeldList::fromString($meldedString);

        $futureWaitingList = $waitingAnalyzer->analyzePrivate($private, $melded);
        $discardedTileStrings = (new ArrayList())->fromSelect($futureWaitingList, function (FutureWaiting $futureWaiting) {
            return $futureWaiting->getDiscard()->__toString();
        })->toArray();
        $this->assertEquals($expected, $discardedTileStrings,
            sprintf('[%s],[%s],[%s],[%s]',
                $onHandTileListString, $meldedString, implode(',', $expected), implode(',', $discardedTileStrings)));
    }

    function privateDataProvider() {
        return [
            // not reachable
            ['123456789m1249sE', '', []],
            // 4+1
            ['123456789m129sEE', '', ['9s']],
            // already win
            ['123sWW', '123m,456m,789m', ['1s', '2s', '3s', 'W']],
        ];
    }
}