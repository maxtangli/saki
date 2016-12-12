<?php

use Saki\Game\Meld\MeldList;
use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Win\Fu\FuAnalyzer;
use Saki\Win\Fu\FuTarget;
use Saki\Win\Waiting\WaitingType;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\YakuItemList;

class FuAnalyzerTest extends \SakiTestCase {
    function testFu() {
        $public = TileList::fromString('12pCCFFF');
        $targetTile = Tile::fromString('3p');
        $handMeldList = MeldList::fromString('123p,CC,(FFF)');
        $melded = MeldList::fromString('8888p,999m');

        $round = $this->getInitRound();
        $current = $round->getCurrentSeatWind();
        $actor = $current;
        $area = $round->getArea($actor);
        $area->setHand(
            $area->getHand()->toHand($public, $melded, $targetTile)
        );

        $subTarget = new WinSubTarget($round, $actor, $handMeldList);
        $yakuList = new YakuItemList();
        $waitingType = WaitingType::create(WaitingType::ONE_SIDE_CHOW_WAITING);
        $target = new FuTarget($subTarget, $yakuList, $waitingType);
        $analyzer = FuAnalyzer::create();
        $result = $analyzer->getResult($target);

        /**
         * 「中の対子＝2符」+「辺張待ち＝2符」+「發の暗刻＝8符」+「ツモ＝2符」+「八筒の明槓＝8符」+「九萬の明刻＝4符」
         * に副底20符を足し、合計で46符となる。
         * 46符は切り上げて50符として計算する。
         */

        $this->assertEquals(0, $result->getSpecialYakuTotalFu());
        $this->assertEquals(20, $result->getBaseFu());
        $winSetFuResults = $result->getWinSetFuResults();
        $this->assertCount(3, $winSetFuResults, var_export($winSetFuResults, true));
        $this->assertEquals(20, $result->getWinSetFu(), implode(',', $winSetFuResults));
        $this->assertEquals(2, $result->getPairFu());
        $this->assertEquals(2, $result->getWaitingTypeFu());
        $this->assertEquals(0, $result->getConcealedFu());
        $this->assertEquals(2, $result->getTsumoFu());
        $this->assertEquals(46, $result->getRoughTotalFu());
        $this->assertEquals(50, $result->getTotalFu());
    }
}