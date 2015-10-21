<?php

use Saki\Game\MockRound;
use Saki\Game\RoundPhase;
use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Fan1\AllRunsYaku;
use Saki\Win\Yaku\Fan1\AllSimplesYaku;
use Saki\Win\Yaku\Fan1\ConcealedSelfDrawYaku;
use Saki\Win\Yaku\Yaku;

class YakuTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider yakuProvider
     */
    function testYaku(YakuTestData $yakuTestData, Yaku $yaku, $expected) {
        $subTarget = $yakuTestData->toWinSubTarget();
        $this->assertEquals($expected, $yaku->existIn($subTarget), sprintf('%s, %s', $yakuTestData, $yaku));
    }

    function yakuProvider() {
        return [
            /**
             * 平和の成立条件は以下の4つである。
             * - 門前であること。すなわちチーをしてはいけない（下の条件2によりポンやカンは不可能である）。
             * - 符のつかない面子で手牌が構成されていること。すなわち4面子すべてが順子であること。
             * - 符のつかない対子が雀頭であること、すなわち役牌が雀頭の時は平和にならない。
             * - 符のつかない待ち、すなわち辺張待ち・嵌張待ち・単騎待ちではなく、両面待ちであること。
             */
            [new YakuTestData(YakuTestData::getEastPrivateRound(), '123m,456m,123s,55s', '789m', '1s'), AllRunsYaku::getInstance(), false],
            [new YakuTestData(YakuTestData::getEastPrivateRound(), '123m,456m,999m,123s,55s', null, '1s'), AllRunsYaku::getInstance(), false],
            [new YakuTestData(YakuTestData::getEastPrivateRound(), '123m,456m,789m,123s,EE', null, '1s'), AllRunsYaku::getInstance(), false],
            [new YakuTestData(YakuTestData::getEastPrivateRound(), '123m,456m,789m,123s,55s', null, '2s'), AllRunsYaku::getInstance(), false],
            [new YakuTestData(YakuTestData::getEastPrivateRound(), '123m,456m,789m,123s,55s', null, '1s'), AllRunsYaku::getInstance(), true],

            [new YakuTestData(YakuTestData::getEastPrivateRound(), '234m,456m,888s,55s', '678m'), AllSimplesYaku::getInstance(), true],
            // with terminal
            [new YakuTestData(YakuTestData::getEastPrivateRound(), '234m,456m,888s,55s', '789m'), AllSimplesYaku::getInstance(), false],
            // with honor
            [new YakuTestData(YakuTestData::getEastPrivateRound(), '234m,456m,888s,EE', '789m'), AllSimplesYaku::getInstance(), false],

            // todo analyzer case with TileSeries
            [new YakuTestData(YakuTestData::getEastPrivateRound(), '123m,456m,77m,88m,11s,55s', null, '1s'), ConcealedSelfDrawYaku::getInstance(), true],
            // not concealed
            [new YakuTestData(YakuTestData::getEastPrivateRound(), '123m,77m,88m,11s,55s', '333m', '1s'), ConcealedSelfDrawYaku::getInstance(), false],
            // not selfDraw
            // todo
        ];
    }
}

class YakuTestData {

    static function getEastPrivateRound() {
        static $eastPrivateRound;
        if (!$eastPrivateRound) {
            $eastPrivateRound = new MockRound();
        }
        return $eastPrivateRound;
    }

    private $mockRound;
    private $handMeldList;
    private $declareMeldList;
    private $targetTile;

    function __construct(MockRound $mockRound, $handMeldListString, $declareMeldListString = null, $targetTileString = null) {
        $this->mockRound = $mockRound;
        $this->handMeldList = MeldList::fromString($handMeldListString);
        $this->declareMeldList = MeldList::fromString($declareMeldListString !== null ? $declareMeldListString : "");
        $this->targetTile = $targetTileString !== null ? Tile::fromString($targetTileString) : $this->handMeldList[0][0];
    }

    function __toString() {
        return sprintf('handMeldList[%s], declaredMeldList[%s]',
            $this->handMeldList, $this->declareMeldList);
    }

    function toWinSubTarget() {
        $mockRound = $this->mockRound;
        $player = $mockRound->getCurrentPlayer();
        $handMeldList = $this->handMeldList;
        $declareMeldList = $this->declareMeldList;
        $targetTile = $this->targetTile;

        $player->getPlayerArea()->getHandTileSortedList()->setInnerArray($handMeldList->toSortedTileList()->toArray());
        $player->getPlayerArea()->getDeclaredMeldList()->setInnerArray($declareMeldList->toArray());
        if ($mockRound->getRoundPhase()->getValue() == RoundPhase::PRIVATE_PHASE) {
            $player->getPlayerArea()->setPrivateTargetTile($targetTile);
        } else {
            $mockRound->getRoundData()->getTileAreas()->setPublicTargetTile($targetTile);
        }

        return new WinSubTarget($handMeldList, $player, $mockRound->getRoundData());
    }
}