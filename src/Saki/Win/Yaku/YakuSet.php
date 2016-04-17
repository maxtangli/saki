<?php
namespace Saki\Win\Yaku;

use Saki\Util\ArrayList;
use Saki\Util\ReadonlyArrayList;
use Saki\Win\TileSeries;
use Saki\Win\Yaku\Fan1\AllRunsYaku;
use Saki\Win\Yaku\Fan1\AllSimplesYaku;
use Saki\Win\Yaku\Fan1\FullyConcealedHandYaku;
use Saki\Win\Yaku\Fan1\DoraYaku;
use Saki\Win\Yaku\Fan1\DoubleRunYaku;
use Saki\Win\Yaku\Fan1\FirstTurnWinYaku;
use Saki\Win\Yaku\Fan1\GreenValueTilesYaku;
use Saki\Win\Yaku\Fan1\KingSTileWinYaku;
use Saki\Win\Yaku\Fan1\PrevailingWindYaku;
use Saki\Win\Yaku\Fan1\ReachYaku;
use Saki\Win\Yaku\Fan1\RedDoraYaku;
use Saki\Win\Yaku\Fan1\RedValueTilesYaku;
use Saki\Win\Yaku\Fan1\RobbingAQuadYaku;
use Saki\Win\Yaku\Fan1\SeatWindYaku;
use Saki\Win\Yaku\Fan1\UraDoraYaku;
use Saki\Win\Yaku\Fan1\WhiteValueTilesYaku;
use Saki\Win\Yaku\Fan2\AllTerminalsAndHonorsYaku;
use Saki\Win\Yaku\Fan2\AllTriplesYaku;
use Saki\Win\Yaku\Fan2\DoubleReachYaku;
use Saki\Win\Yaku\Fan2\FullStraightYaku;
use Saki\Win\Yaku\Fan2\LittleThreeDragonsYaku;
use Saki\Win\Yaku\Fan2\MixedOutsideHandYaku;
use Saki\Win\Yaku\Fan2\SevenPairsYaku;
use Saki\Win\Yaku\Fan2\ThreeColorRunsYaku;
use Saki\Win\Yaku\Fan2\ThreeColorTriplesYaku;
use Saki\Win\Yaku\Fan2\ThreeConcealedTriplesYaku;
use Saki\Win\Yaku\Fan2\ThreeQuadsYaku;
use Saki\Win\Yaku\Fan3\HalfFlushYaku;
use Saki\Win\Yaku\Fan3\PureOutsideHandYaku;
use Saki\Win\Yaku\Fan3\TwoDoubleRunYaku;
use Saki\Win\Yaku\Fan6\FullFlushYaku;
use Saki\Win\Yaku\Yakuman\AllGreenYaku;
use Saki\Win\Yaku\Yakuman\AllHonorsYaku;
use Saki\Win\Yaku\Yakuman\AllTerminalsYaku;
use Saki\Win\Yaku\Yakuman\BigFourWindsYaku;
use Saki\Win\Yaku\Yakuman\BigThreeDragonsYaku;
use Saki\Win\Yaku\Yakuman\EarthlyWinYaku;
use Saki\Win\Yaku\Yakuman\FourConcealedTriplesYaku;
use Saki\Win\Yaku\Yakuman\FourQuadsYaku;
use Saki\Win\Yaku\Yakuman\HeavenlyWinYaku;
use Saki\Win\Yaku\Yakuman\HumanlyWinYaku;
use Saki\Win\Yaku\Yakuman\NineGatesYaku;
use Saki\Win\Yaku\Yakuman\SmallFourWindsYaku;
use Saki\Win\Yaku\Yakuman\ThirteenOrphansYaku;
use Saki\Win\Yaku\Yakuman2\FourConcealedTriplesOnePairWaitingYaku;
use Saki\Win\Yaku\Yakuman2\PureNineGatesYaku;
use Saki\Win\Yaku\Yakuman2\ThirteenOrphansPairWaitingYaku;

/**
 * The yakus set used in a game.
 * @package Saki\Win\Yaku
 */
class YakuSet extends ArrayList {
    use ReadonlyArrayList;
    private static $standardInstance;

    /**
     * @return YakuSet
     */
    static function createStandard() {
        self::$standardInstance = self::$standardInstance ?? new self([
                // Fan1
                AllRunsYaku::create(),
                AllSimplesYaku::create(),
                FullyConcealedHandYaku::create(),
                DoraYaku::create(),
                DoubleRunYaku::create(),
                FirstTurnWinYaku::create(),
                GreenValueTilesYaku::create(),
                KingSTileWinYaku::create(),
                ReachYaku::create(),
                RedDoraYaku::create(),
                RedValueTilesYaku::create(),
                RobbingAQuadYaku::create(),
                PrevailingWindYaku::create(),
                SeatWindYaku::create(),
                UraDoraYaku::create(),
                WhiteValueTilesYaku::create(),
                // Fan2
                AllTerminalsAndHonorsYaku::create(),
                AllTriplesYaku::create(),
                DoubleReachYaku::create(),
                FullStraightYaku::create(),
                LittleThreeDragonsYaku::create(),
                MixedOutsideHandYaku::create(),
                SevenPairsYaku::create(),
                ThreeColorRunsYaku::create(),
                ThreeColorTriplesYaku::create(),
                ThreeConcealedTriplesYaku::create(),
                ThreeQuadsYaku::create(),
                // Fan3
                HalfFlushYaku::create(),
                PureOutsideHandYaku::create(),
                TwoDoubleRunYaku::create(),
                // Fan6
                FullFlushYaku::create(),
                // Yakuman
                AllGreenYaku::create(),
                AllHonorsYaku::create(),
                AllTerminalsYaku::create(),
                BigFourWindsYaku::create(),
                BigThreeDragonsYaku::create(),
                EarthlyWinYaku::create(),
                FourConcealedTriplesYaku::create(),
                FourQuadsYaku::create(),
                HeavenlyWinYaku::create(),
                HumanlyWinYaku::create(),
                NineGatesYaku::create(),
                SmallFourWindsYaku::create(),
                ThirteenOrphansYaku::create(),
                // Yakuman2
                FourConcealedTriplesOnePairWaitingYaku::create(),
                PureNineGatesYaku::create(),
                ThirteenOrphansPairWaitingYaku::create(),
            ]);
        return self::$standardInstance;
    }

    /**
     * @return ArrayList An ArrayList of TileSeries required by any Yaku in YakuSet.
     */
    function getTileSeriesList() {
        return (new ArrayList())->fromSelectMany($this, function (Yaku $yaku) {
            return $yaku->getRequiredTileSeries();
        })->insertFirst(TileSeries::create(TileSeries::FOUR_WIN_SET_AND_ONE_PAIR))
            ->distinct();
    }
}