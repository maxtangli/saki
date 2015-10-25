<?php

use Saki\Game\MockRound;
use Saki\Game\RoundPhase;
use Saki\Tile\Tile;

class WaitingAnalyzerTest extends \PHPUnit_Framework_TestCase {

    function generateRoundData($isPrivate, $handTileListString, $declaredMeldListString, \Saki\Tile\Tile $winTile) {
        $r = new MockRound();
        $handTileList = \Saki\Tile\TileSortedList::fromString($handTileListString);
        $declaredMeldList = \Saki\Meld\MeldList::fromString($declaredMeldListString);

        // todo not safe?
        $r->getRoundData()->getTurnManager()->debugSet($r->getCurrentPlayer(), RoundPhase::getPrivateOrPublicInstance($isPrivate), 1);
        $r->getRoundData()->getTileAreas()->debugSet($r->getCurrentPlayer(), $handTileList, $declaredMeldList, $winTile);

        return $r->getRoundData();
    }

    function generateWinTarget($isPrivate, $onHandTileListString, $declaredMeldListString, \Saki\Tile\Tile $winTile) {
        $roundData = $this->generateRoundData($isPrivate, $onHandTileListString, $declaredMeldListString, $winTile);
        return new \Saki\Win\WinTarget($roundData->getPlayerList()[0], $roundData);
    }

    /**
     * @dataProvider publicDataProvider
     */
    function testPublicData($onHandTileListString, $declaredMeldListString, $expected) {
        $waitingTileAnalyzer = new \Saki\Win\WaitingAnalyzer();

        $handTileList = \Saki\Tile\TileSortedList::fromString($onHandTileListString);
        $declaredMeldList = \Saki\Meld\MeldList::fromString($declaredMeldListString);

        $waitingTileList = $waitingTileAnalyzer->analyzePublicPhaseHandWaitingTileList($handTileList, $declaredMeldList);
        $waitingTileStrings = $waitingTileList->toArray(function (Tile $waitingTile) {
            return $waitingTile->__toString();
        });
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
        $winAnalyzer = new \Saki\Win\WinAnalyzer();
        $waitingTileAnalyzer = new \Saki\Win\WaitingAnalyzer($winAnalyzer);
        $handTileList = \Saki\Tile\TileSortedList::fromString($onHandTileListString);
        $declaredMeldList = \Saki\Meld\MeldList::fromString($declaredMeldListString);

        $futureWaitingList = $waitingTileAnalyzer->analyzePrivatePhaseFutureWaitingList($handTileList, $declaredMeldList);
        $discardedTileStrings = $futureWaitingList->toArray(function (\Saki\Win\FutureWaiting $futureWaiting) {
            return $futureWaiting->getDiscardedTile()->__toString();
        });
        $this->assertEquals($expected, $discardedTileStrings,
            sprintf('[%s],[%s],[%s],[%s]',
                $onHandTileListString, $declaredMeldListString, implode(',', $expected), implode(',', $discardedTileStrings)));
    }

    function privateDataProvider() {
        return [
            // not reachable
            ['123456789m1249sE', '', []], // 715ms
            // 4+1
            ['123456789m129sEE', '', ['9s']], // 683ms
            // already win
            ['123sWW', '123m,456m,789m', ['1s','2s','3s','W']], // 115ms
        ];
    }
}
