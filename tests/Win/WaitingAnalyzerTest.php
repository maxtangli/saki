<?php

use Saki\Tile\Tile;

class WaitingAnalyzerTest extends \PHPUnit_Framework_TestCase {

    function generateRoundData($isPrivate, $onHandTileListString, $declaredMeldListString, \Saki\Tile\Tile $winTile) {
        $round = new \Saki\Game\Round();
        $onHandTileList = \Saki\Tile\TileSortedList::fromString($onHandTileListString);
        $declaredMeldList = \Saki\Meld\MeldList::fromString($declaredMeldListString);
        $playerArea = $round->getPlayerList()->offsetGet(0)->getPlayerArea();
        if ($isPrivate) {
            $this->assertTrue($onHandTileList->isPrivatePhaseCount());
            $round->getRoundData()->getTileAreas()->setTargetTile($winTile);
            $playerArea->reset($onHandTileList, $declaredMeldList); // onHandTileList contains winTile
        } else {
            $this->assertTrue($onHandTileList->isPublicPhaseCount());
            $round->discard($round->getPlayerList()[0], $playerArea->getHandTileSortedList()[0]);
            $round->passPublicPhase();
            $playerArea->reset($onHandTileList, $declaredMeldList); // onHandTileList do not contains winTile

            $round->getPlayerList()->offsetGet(1)->getPlayerArea()->getHandTileSortedList()->replaceByIndex(0, $winTile);
            $round->discard($round->getPlayerList()->offsetGet(1), $winTile);
        }
        return $round->getRoundData();
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
