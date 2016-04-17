<?php

use Saki\Game\Round;
use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Win\Fu\FuAnalyzer;
use Saki\Win\Fu\FuTarget;
use Saki\Win\WaitingType;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\YakuItemList;

class FuAnalyzerTest extends PHPUnit_Framework_TestCase {
    function testFu() {
        $targetTile = Tile::fromString('3p');
        $declareMeldList = MeldList::fromString('8888p,999m');
        $hand = TileList::fromString('123pCCFFF');
        $handMeldList = MeldList::fromString('123p,CC,(FFF)');

        $r = new Round();
        $current = $r->getAreas()->getCurrentSeatWind();
        $actor = $current;
        $r->getAreas()->debugSetPrivate($current, $hand, $declareMeldList, $targetTile);

        $subTarget = new WinSubTarget($handMeldList, $actor, $r);
        $yakuList = new YakuItemList();
        $waitingType = WaitingType::create(WaitingType::ONE_SIDE_RUN_WAITING);
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