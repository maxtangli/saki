<?php

use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\ArrayList;
use Saki\Win\FutureWaiting;
use Saki\Win\WaitingAnalyzer;
use Saki\Win\WinAnalyzer;
use Saki\Win\Yaku\YakuSet;

class WaitingAnalyzerTest extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider publicDataProvider
     */
    function testPublicData($onHandTileListString, $declaredMeldListString, $expected) {
        $waitingTileAnalyzer = new WaitingAnalyzer();

        $handTileList = TileList::fromString($onHandTileListString);
        $declaredMeldList = MeldList::fromString($declaredMeldListString);

        $waitingTileList = $waitingTileAnalyzer->analyzePublic($handTileList, $declaredMeldList);
        $waitingTileStrings = (new ArrayList())->fromSelected($waitingTileList, function (Tile $waitingTile) {
            return $waitingTile->__toString();
        })->toArray();
        $this->assertEquals($expected, $waitingTileStrings,
            sprintf('[%s],[%s],[%s],[%s]',
                $onHandTileListString, $declaredMeldListString, implode(',', $expected), implode(',', $waitingTileStrings)));
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
    function testPrivateData($onHandTileListString, $declaredMeldListString, $expected) {
        $winAnalyzer = new WinAnalyzer(YakuSet::getStandardYakuSet());
        $waitingTileAnalyzer = new WaitingAnalyzer($winAnalyzer);
        $handTileList = TileList::fromString($onHandTileListString);
        $declaredMeldList = MeldList::fromString($declaredMeldListString);

        $futureWaitingList = $waitingTileAnalyzer->analyzePrivate($handTileList, $declaredMeldList);
        $discardedTileStrings = (new ArrayList())->fromSelected($futureWaitingList, function (FutureWaiting $futureWaiting) {
            return $futureWaiting->getDiscardedTile()->__toString();
        })->toArray();
        $this->assertEquals($expected, $discardedTileStrings,
            sprintf('[%s],[%s],[%s],[%s]',
                $onHandTileListString, $declaredMeldListString, implode(',', $expected), implode(',', $discardedTileStrings)));
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