<?php

use Saki\Game\MockRound;
use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Fan1\AllRunsYaku;
use Saki\Win\Yaku\Fan1\AllSimplesYaku;
use Saki\Win\Yaku\Fan1\ConcealedSelfDrawYaku;
use Saki\Win\Yaku\Fan1\DoubleRunYaku;
use Saki\Win\Yaku\Fan1\GreenValueTilesYaku;
use Saki\Win\Yaku\Fan1\ReachYaku;
use Saki\Win\Yaku\Fan1\RedValueTilesYaku;
use Saki\Win\Yaku\Fan1\RoundWindValueTilesYaku;
use Saki\Win\Yaku\Fan1\SelfWindValueTilesYaku;
use Saki\Win\Yaku\Fan1\WhiteValueTilesYaku;
use Saki\Win\Yaku\Fan3\TwoDoubleRunYaku;
use Saki\Win\Yaku\Yaku;

class YakuTest extends PHPUnit_Framework_TestCase
{
    static function assertYakuExist($expected, YakuTestData $yakuTestData, Yaku $yaku)
    {
        $subTarget = $yakuTestData->toWinSubTarget();
        self::assertEquals($expected, $yaku->existIn($subTarget), sprintf('%s, %s', $yakuTestData, $yaku));
    }

    /**
     * @dataProvider fan1Provider
     */
    function testFan1(YakuTestData $yakuTestData, Yaku $yaku, $expected)
    {
        $this->assertYakuExist($expected, $yakuTestData, $yaku);
    }

//    /**
//     * @dataProvider fan2Provider
//     */
//    function testFan2(YakuTestData $yakuTestData, Yaku $yaku, $expected)
//    {
//        $this->assertYakuExist($expected, $yakuTestData, $yaku);
//    }

    function fan1Provider()
    {
        return [
            [new YakuTestData('123m,456m,789m,123s,55s', null, '1s'), AllRunsYaku::getInstance(), true],
            // not concealed
            [new YakuTestData('123m,456m,123s,55s', '789m', '1s'), AllRunsYaku::getInstance(), false],
            // not 4 run
            [new YakuTestData('123m,456m,999m,123s,55s', null, '1s'), AllRunsYaku::getInstance(), false],
            // not suit pair
            [new YakuTestData('123m,456m,789m,123s,EE', null, '1s'), AllRunsYaku::getInstance(), false],
            // not two-pair waiting
            [new YakuTestData('123m,456m,789m,123s,55s', null, '2s'), AllRunsYaku::getInstance(), false],

            [new YakuTestData('234m,456m,888s,55s', '678m'), AllSimplesYaku::getInstance(), true],
            // not without terminal
            [new YakuTestData('234m,456m,888s,55s', '789m'), AllSimplesYaku::getInstance(), false],
            // not without honor
            [new YakuTestData('234m,456m,888s,EE', '789m'), AllSimplesYaku::getInstance(), false],

            // !assume TileSeries exist
            [new YakuTestData('123m,456m,77m,88m,11s,55s', null, '1s'), ConcealedSelfDrawYaku::getInstance(), true],
            // not concealed
            [new YakuTestData('123m,77m,88m,11s,55s', '333m', '1s'), ConcealedSelfDrawYaku::getInstance(), false],
            // not selfDraw
            [new YakuTestData('123m,456m,77m,88m,11s,55s', null, '1s', 'E', 'W'), ConcealedSelfDrawYaku::getInstance(), false],

            // !assume TileSeries exist
            [new YakuTestData('123m,123m,77m,88m,11s,EE', null, 'E'), DoubleRunYaku::getInstance(), true],
            // not concealed
            [new YakuTestData('123m,123m,EE', '123s,123s', 'E'), DoubleRunYaku::getInstance(), false],

            [new YakuTestData('123m,123m,123s,123s,EE', null, 'E'), TwoDoubleRunYaku::getInstance(), true],
            // not non-duplicate
            [new YakuTestData('123m,123m,123m,123m,EE', null, 'E'), TwoDoubleRunYaku::getInstance(), false],
            // not concealed
            [new YakuTestData('123m,123m,EE', '123s,123s', 'E'), TwoDoubleRunYaku::getInstance(), false],

            // todo is reach true

            // not reach
            [new YakuTestData('123m,456m,789m,123s,55s', null, '1s'), ReachYaku::getInstance(), false],

            // !assume TileSeries exist
            [new YakuTestData('123m,44m,55m,66m,55s', 'CCC', '5s'), RedValueTilesYaku::getInstance(), true],
            [new YakuTestData('123m,44m,55m,66m,55s', '111s', '5s'), RedValueTilesYaku::getInstance(), false],

            // !assume TileSeries exist
            [new YakuTestData('123m,44m,55m,66m,55s', 'PPP', '5s'), WhiteValueTilesYaku::getInstance(), true],
            [new YakuTestData('123m,44m,55m,66m,55s', '111s', '5s'), WhiteValueTilesYaku::getInstance(), false],

            // !assume TileSeries exist
            [new YakuTestData('123m,44m,55m,66m,55s', 'FFF', '5s'), GreenValueTilesYaku::getInstance(), true],
            [new YakuTestData('123m,44m,55m,66m,55s', '111s', '5s'), GreenValueTilesYaku::getInstance(), false],

            // !assume TileSeries exist
            [new YakuTestData('123m,44m,55m,66m,55s', 'EEE', '5s'), RoundWindValueTilesYaku::getInstance(), true],
            // not roundWind
            [(new YakuTestData('123m,44m,55m,66m,55s', 'EEE', '5s'))->setRoundWind(Tile::fromString('S')), RoundWindValueTilesYaku::getInstance(), false],

            // !assume TileSeries exist
            [new YakuTestData('123m,44m,55m,66m,55s', 'SSS', '5s', 'E', 'S'), SelfWindValueTilesYaku::getInstance(), true],
            // not selfWind
            [new YakuTestData('123m,44m,55m,66m,55s', 'SSS', '5s', 'E', 'E'), SelfWindValueTilesYaku::getInstance(), false],
        ];
    }

    function fan2Provider()
    {
        return [

        ];
    }
}

class YakuTestData
{
    private static $mockRound;

    private $handMeldList;
    private $declareMeldList;
    private $targetTile;

    private $currentPlayerWind;
    private $targetPlayerWind;

    private $globalTurn;
    private $roundWind;
    private $isReach;

    function __construct($handMeldListString, $declareMeldListString = null, $targetTileString = null,
                         $currentPlayerWindString = null, $targetPlayerWindString = null)
    {
        $this->handMeldList = MeldList::fromString($handMeldListString);
        $this->declareMeldList = MeldList::fromString($declareMeldListString !== null ? $declareMeldListString : "");
        $this->targetTile = $targetTileString !== null ? Tile::fromString($targetTileString) : $this->handMeldList[0][0];

        $this->currentPlayerWind = $currentPlayerWindString !== null ? Tile::fromString($currentPlayerWindString) : Tile::fromString('E');
        $this->targetPlayerWind = $targetPlayerWindString !== null ? Tile::fromString($targetPlayerWindString) : $this->currentPlayerWind;

        $this->globalTurn = 1;
        $this->roundWind = Tile::fromString('E');
    }

    function __toString()
    {
        return sprintf('handMeldList[%s], declaredMeldList[%s]', $this->handMeldList, $this->declareMeldList);
    }

    function setGlobalTurn($globalTurn)
    {
        $this->globalTurn = $globalTurn;
        return $this;
    }

    function setRoundWind($roundWind)
    {
        $this->roundWind = $roundWind;
        return $this;
    }

    function setIsReach($isReach)
    {
        $this->isReach = $isReach;
        return $this;
    }

    function toWinSubTarget()
    {
        if (!self::$mockRound) {
            self::$mockRound = new MockRound(); // for 10 test cases, 1.2s => 0.2s which is 6x faster
        }
        $mockRound = self::$mockRound;

        // set phase
        $currentPlayer = $mockRound->getPlayerList()->getSelfWindPlayer($this->currentPlayerWind);
        $targetPlayer = $mockRound->getPlayerList()->getSelfWindPlayer($this->targetPlayerWind);
        $isPublicPhase = $currentPlayer !== $targetPlayer;
        $isPrivatePhase = !$isPublicPhase;

        $mockRound->debugSetTurn($currentPlayer, $isPublicPhase, $this->globalTurn);
        $mockRound->debugSetRoundWindData($this->roundWind);


        // set tiles
        $handMeldList = $this->handMeldList;
        $targetTile = $this->targetTile;
        $handTileList = $handMeldList->toSortedTileList()->toPrivateOrPublicPhaseTileSortedList($isPrivatePhase, $targetTile);

        $targetPlayer->getPlayerArea()->reset($handTileList, $this->declareMeldList);
        $mockRound->debugSetTargetTile($targetTile);

        return new WinSubTarget($this->handMeldList, $targetPlayer, $mockRound->getRoundData());
    }
}