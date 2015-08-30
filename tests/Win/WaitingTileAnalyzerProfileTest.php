<?php
//
//// to be removed
//class WaitingTileAnalyzerProfileTest extends \PHPUnit_Framework_TestCase {
//
//    function generateRoundData($isPrivate, $onHandTileListString, $declaredMeldListString, \Saki\Tile\Tile $winTile) {
//        $round = new \Saki\Game\Round();
//        $onHandTileList = \Saki\Tile\TileSortedList::fromString($onHandTileListString);
//        $declaredMeldList = \Saki\Meld\MeldList::fromString($declaredMeldListString);
//        $playerArea = $round->getPlayerList()->offsetGet(0)->getPlayerArea();
//        if ($isPrivate) {
//            $this->assertTrue($onHandTileList->validPrivatePhaseCount());
//            $playerArea->init($onHandTileList, $winTile, $declaredMeldList); // onHandTileList contains winTile
//        } else {
//            $this->assertTrue($onHandTileList->validPublicPhaseCount());
//            $round->discard($round->getPlayerList()[0], $playerArea->getHandTileSortedList()[0]);
//            $round->passPublicPhase();
//            $playerArea->init($onHandTileList, null, $declaredMeldList); // onHandTileList do not contains winTile
//
//            $round->getPlayerList()->offsetGet(1)->getPlayerArea()->getHandTileSortedList()->replaceByIndex(0, $winTile);
//            $round->discard($round->getPlayerList()->offsetGet(1), $winTile);
//        }
//        return $round->getRoundData();
//    }
//
//    function generateWinTarget($isPrivate, $onHandTileListString, $declaredMeldListString, \Saki\Tile\Tile $winTile) {
//        $roundData = $this->generateRoundData($isPrivate, $onHandTileListString, $declaredMeldListString, $winTile);
//        return new \Saki\Win\WinTarget($roundData->getPlayerList()[0], $roundData);
//    }
//
//    /**
//     * @dataProvider privateDataProvider
//     */
//    function testPrivateData($onHandTileListString, $declaredMeldListString, $expected) {
//        $winAnalyzer = new \Saki\Win\WinAnalyzer();
//        $waitingTileAnalyzer = new \Saki\Win\WaitingAnalyzer($winAnalyzer);
//        $target = $this->generateWinTarget(true, $onHandTileListString, $declaredMeldListString, \Saki\Tile\Tile::fromString('P'));
//
//        $time = microtime(true);
//        $futureWaitingList = $waitingTileAnalyzer->analyzePrivatePhaseFutureWaitingList($target);
//        $time = microtime(true) - $time;
//        echo "generateWinTarget: $time \n";
//
//        $discardedTileStrings = $futureWaitingList->toArray(function (\Saki\Win\FutureWaiting $futureWaiting) {
//            return $futureWaiting->getDiscardedTile()->__toString();
//        });
//        $this->assertEquals($expected, $discardedTileStrings,
//            sprintf('[%s],[%s],[%s],[%s]',
//                $onHandTileListString, $declaredMeldListString, implode(',', $expected), implode(',', $discardedTileStrings)));
//
//        echo 'meld analyzer: ' . \Saki\Meld\MeldCompositionsAnalyzer::$debug_time_cost . "\n";
//
//        /*
//         * generateWinTarget: 1.3373689651489
//         * total meld analyzer: 0.60766196250916
//         * one discard card loop: 0.06s
//         */
//    }
//
//    function privateDataProvider() {
//        return [
//            // not reachable
//            ['123456789m1249sE', '', []], // 715ms
//            // 4+1
//            //['123456789m129sEE', '', ['9s']], // 683ms
//            // already win
//            //['123sWW', '123m,456m,789m', ['1s','2s','3s','W']], // 115ms
//        ];
//    }
//}
