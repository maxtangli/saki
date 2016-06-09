<?php

use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\ArrayList;
use Saki\Win\Waiting\FutureWaiting;

class WaitingAnalyzerTest extends \SakiTestCase {
    /**
     * @dataProvider publicDataProvider
     */
    function testPublicData($onHandTileListString, $declareString, $expected) {
        $round = $this->getInitRound();
        $waitingAnalyzer = $round->getGameData()->getWinAnalyzer()->getWaitingAnalyzer();

        $public = TileList::fromString($onHandTileListString);
        $declare = MeldList::fromString($declareString);

        $waitingTileList = $waitingAnalyzer->analyzePublic($public, $declare);
        $waitingTileStrings = (new ArrayList())->fromSelect($waitingTileList, function (Tile $waitingTile) {
            return $waitingTile->__toString();
        })->toArray();
        $this->assertEquals($expected, $waitingTileStrings,
            sprintf('[%s],[%s],[%s],[%s]',
                $onHandTileListString, $declareString, implode(',', $expected), implode(',', $waitingTileStrings)));
    }

    function publicDataProvider() {
        return [
            // not reachable
            ['123456789m124sE', '', []],
            // 4+1
            ['123456789m12sEE', '', ['3s']],
            ['123456789m23sWW', '', ['1s', '4s']],
            ['123456789m123sS', '', ['S']],
            // 4+1 triple
            ['111222333444sE', '', ['E']],
        ];
    }

    /**
     * @dataProvider privateDataProvider
     */
    function testPrivateData($onHandTileListString, $declareString, $expected) {
        $round = $this->getInitRound();
        $waitingAnalyzer = $round->getGameData()->getWinAnalyzer()->getWaitingAnalyzer();
        $private = TileList::fromString($onHandTileListString);
        $declare = MeldList::fromString($declareString);

        $futureWaitingList = $waitingAnalyzer->analyzePrivate($private, $declare);
        $discardedTileStrings = (new ArrayList())->fromSelect($futureWaitingList, function (FutureWaiting $futureWaiting) {
            return $futureWaiting->getDiscard()->__toString();
        })->toArray();
        $this->assertEquals($expected, $discardedTileStrings,
            sprintf('[%s],[%s],[%s],[%s]',
                $onHandTileListString, $declareString, implode(',', $expected), implode(',', $discardedTileStrings)));
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