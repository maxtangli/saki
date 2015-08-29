<?php

class WaitingTileAnalyzerTest extends \PHPUnit_Framework_TestCase {

    function generateRoundData($isPrivate, $onHandTileListString, $declaredMeldListString, \Saki\Tile\Tile $winTile) {
        $round = new \Saki\Game\Round();
        $onHandTileList = \Saki\Tile\TileSortedList::fromString($onHandTileListString);
        $declaredMeldList = \Saki\Meld\MeldList::fromString($declaredMeldListString);
        $playerArea = $round->getPlayerList()->offsetGet(0)->getPlayerArea();
        if ($isPrivate) {
            $this->assertTrue($onHandTileList->validPrivatePhaseCount());
            $playerArea->init($onHandTileList, $winTile, $declaredMeldList); // onHandTileList contains winTile
        } else {
            $this->assertTrue($onHandTileList->validPublicPhaseCount());
            $round->discard($round->getPlayerList()[0], $playerArea->getHandTileSortedList()[0]);
            $round->passPublicPhase();
            $playerArea->init($onHandTileList, null, $declaredMeldList); // onHandTileList do not contains winTile

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
        $winAnalyzer = new \Saki\Win\WinAnalyzer();
        $waitingTileAnalyzer = new \Saki\Win\WaitingTileAnalyzer($winAnalyzer);
        $target = $this->generateWinTarget(false, $onHandTileListString, $declaredMeldListString, \Saki\Tile\Tile::fromString('P'));
        $waitingTileList = $waitingTileAnalyzer->analyzePublicPhaseWaitingList($target);
        $waitingTileStrings = $waitingTileList->toArray(function (\Saki\Win\WaitingTile $waitingTile) {
            return $waitingTile->getWaitingTile()->__toString();
        });
        $this->assertEquals($expected, $waitingTileStrings,
            sprintf('[%s],[%s],[%s],[%s]',
                $onHandTileListString, $declaredMeldListString, implode(',',$expected), implode(',',$waitingTileStrings)));
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
}
