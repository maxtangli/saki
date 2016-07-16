<?php
namespace Saki\Win\Yaku;

use Saki\Util\ArrayList;
use Saki\Util\ReadonlyArrayList;
use Saki\Win\Series\Series;
use Saki\Win\Yaku\Fan1\AfterAKongWinYaku;
use Saki\Win\Yaku\Fan1\AllSimplesYaku;
use Saki\Win\Yaku\Fan1\BottomOfTheSeaFishYaku;
use Saki\Win\Yaku\Fan1\BottomOfTheSeaMoonYaku;
use Saki\Win\Yaku\Fan1\DoraYaku;
use Saki\Win\Yaku\Fan1\DragonPungGreenYaku;
use Saki\Win\Yaku\Fan1\DragonPungRedYaku;
use Saki\Win\Yaku\Fan1\DragonPungWhiteYaku;
use Saki\Win\Yaku\Fan1\FirstTurnWinYaku;
use Saki\Win\Yaku\Fan1\FullyConcealedHandYaku;
use Saki\Win\Yaku\Fan1\PinfuYaku;
use Saki\Win\Yaku\Fan1\PrevailingWindYaku;
use Saki\Win\Yaku\Fan1\PureDoubleChowYaku;
use Saki\Win\Yaku\Fan1\RedDoraYaku;
use Saki\Win\Yaku\Fan1\RiichiYaku;
use Saki\Win\Yaku\Fan1\RobbingAKongYaku;
use Saki\Win\Yaku\Fan1\SeatWindYaku;
use Saki\Win\Yaku\Fan1\UraDoraYaku;
use Saki\Win\Yaku\Fan2\AllPungsYaku;
use Saki\Win\Yaku\Fan2\AllTerminalsAndHonoursYaku;
use Saki\Win\Yaku\Fan2\DoubleRiichiYaku;
use Saki\Win\Yaku\Fan2\LittleThreeDragonsYaku;
use Saki\Win\Yaku\Fan2\MixedTripleChowYaku;
use Saki\Win\Yaku\Fan2\OutsideHandYaku;
use Saki\Win\Yaku\Fan2\PureStraightYaku;
use Saki\Win\Yaku\Fan2\SevenPairsYaku;
use Saki\Win\Yaku\Fan2\ThreeConcealedPungsYaku;
use Saki\Win\Yaku\Fan2\ThreeKongsYaku;
use Saki\Win\Yaku\Fan2\TriplePungYaku;
use Saki\Win\Yaku\Fan3\HalfFlushYaku;
use Saki\Win\Yaku\Fan3\TerminalsInAllSetsYaku;
use Saki\Win\Yaku\Fan3\TwicePureDoubleChowYaku;
use Saki\Win\Yaku\Fan6\FullFlushYaku;
use Saki\Win\Yaku\Yakuman\AllGreenYaku;
use Saki\Win\Yaku\Yakuman\AllHonoursYaku;
use Saki\Win\Yaku\Yakuman\AllTerminalsYaku;
use Saki\Win\Yaku\Yakuman\BigFourWindsYaku;
use Saki\Win\Yaku\Yakuman\BigThreeDragonsYaku;
use Saki\Win\Yaku\Yakuman\BlessingOfEarthYaku;
use Saki\Win\Yaku\Yakuman\BlessingOfHeavenYaku;
use Saki\Win\Yaku\Yakuman\BlessingOfManYaku;
use Saki\Win\Yaku\Yakuman\FourConcealedPungsYaku;
use Saki\Win\Yaku\Yakuman\FourKongsYaku;
use Saki\Win\Yaku\Yakuman\LittleFourWindsYaku;
use Saki\Win\Yaku\Yakuman\NineGatesYaku;
use Saki\Win\Yaku\Yakuman\ThirteenOrphansYaku;
use Saki\Win\Yaku\Yakuman2\PureFourConcealedPungsYaku;
use Saki\Win\Yaku\Yakuman2\PureNineGatesYaku;
use Saki\Win\Yaku\Yakuman2\PureThirteenOrphansYaku;

/**
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
                AfterAKongWinYaku::create(),
                AllSimplesYaku::create(),
                BottomOfTheSeaFishYaku::create(),
                BottomOfTheSeaMoonYaku::create(),
                DoraYaku::create(),
                DragonPungGreenYaku::create(),
                DragonPungRedYaku::create(),
                DragonPungWhiteYaku::create(),
                FirstTurnWinYaku::create(),
                FullyConcealedHandYaku::create(),
                PinfuYaku::create(),
                PrevailingWindYaku::create(),
                PureDoubleChowYaku::create(),
                RedDoraYaku::create(),
                RiichiYaku::create(),
                RobbingAKongYaku::create(),
                SeatWindYaku::create(),
                UraDoraYaku::create(),
                // Fan2
                AllPungsYaku::create(),
                AllTerminalsAndHonoursYaku::create(),
                DoubleRiichiYaku::create(),
                LittleThreeDragonsYaku::create(),
                MixedTripleChowYaku::create(),
                OutsideHandYaku::create(),
                PureStraightYaku::create(),
                SevenPairsYaku::create(),
                ThreeConcealedPungsYaku::create(),
                ThreeKongsYaku::create(),
                TriplePungYaku::create(),
                // Fan3
                HalfFlushYaku::create(),
                TerminalsInAllSetsYaku::create(),
                TwicePureDoubleChowYaku::create(),
                // Fan6
                FullFlushYaku::create(),
                // Yakuman
                AllGreenYaku::create(),
                AllHonoursYaku::create(),
                AllTerminalsYaku::create(),
                BigFourWindsYaku::create(),
                BigThreeDragonsYaku::create(),
                BlessingOfEarthYaku::create(),
                BlessingOfHeavenYaku::create(),
                BlessingOfManYaku::create(),
                FourConcealedPungsYaku::create(),
                FourKongsYaku::create(),
                LittleFourWindsYaku::create(),
                NineGatesYaku::create(),
                ThirteenOrphansYaku::create(),
                // Yakuman2
                PureFourConcealedPungsYaku::create(),
                PureNineGatesYaku::create(),
                PureThirteenOrphansYaku::create(),
            ]);
        return self::$standardInstance;
    }

    /**
     * @return ArrayList An ArrayList of Series required by this YakuSet.
     */
    function getSeriesList() {
        $getRequiredSeries = function (Yaku $yaku) {
            return $yaku->getRequiredSeries();
        };
        return (new ArrayList())->fromSelectMany($this, $getRequiredSeries)
            ->insertFirst(Series::create(Series::FOUR_WIN_SET_AND_ONE_PAIR))
            ->distinct();
    }
}