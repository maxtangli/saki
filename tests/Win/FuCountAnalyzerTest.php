<?php

use Saki\Game\RoundPhase;
use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Win\WaitingType;
use Saki\Tile\TileSortedList;
use Saki\Game\TileArea;

class FuCountAnalyzerTest extends PHPUnit_Framework_TestCase {

    function testFuCount() {
        $roundData = new \Saki\Game\RoundData();

        $player = $roundData->getTurnManager()->getCurrentPlayer();
        $roundData->getTurnManager()->debugSet($player, RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), 1);
        $roundData->getTileAreas()->debugSet($player, TileList::fromString('123pCCFFF'), MeldList::fromString('8888p,999m'), Tile::fromString('3p'));

        $handMeldList = MeldList::fromString('123p,CC,(FFF)');

        $subTarget = new \Saki\Win\WinSubTarget($handMeldList, $player, $roundData);
        $yakuList = new \Saki\Win\Yaku\YakuList([], true);
        $waitingType = WaitingType::getInstance(WaitingType::ONE_SIDE_RUN_WAITING);
        $target = new \Saki\Win\Fu\FuCountTarget($subTarget, $yakuList, $waitingType);
        $analyzer = \Saki\Win\Fu\FuCountAnalyzer::getInstance();
        $result = $analyzer->getResult($target);

        /**
         * 「中の対子＝2符」+「辺張待ち＝2符」+「發の暗刻＝8符」+「ツモ＝2符」+「八筒の明槓＝8符」+「九萬の明刻＝4符」
         * に副底20符を足し、合計で46符となる。
         * 46符は切り上げて50符として計算する。
         */

        $this->assertEquals(0, $result->getSpecialYakuTotalFuCount());
        $this->assertEquals(20, $result->getBaseFuCount());
        $winSetFuCountResults = $result->getWinSetFuCountResults();
        $this->assertCount(3, $winSetFuCountResults, var_export($winSetFuCountResults, true));
        $this->assertEquals(20, $result->getWinSetFuCount(), implode(',', $winSetFuCountResults));
        $this->assertEquals(2, $result->getPairFuCount());
        $this->assertEquals(2, $result->getWaitingTypeFuCount());
        $this->assertEquals(0, $result->getConcealedFuCount());
        $this->assertEquals(2, $result->getWinBySelfFuCount());
        $this->assertEquals(46, $result->getRoughTotalFuCount());
        $this->assertEquals(50, $result->getTotalFuCount());
    }
}