<?php
namespace Saki\Win\Yaku;

use Saki\Util\ArrayLikeObject;
use Saki\Win\Yaku\Fan1\AllRunsYaku;
use Saki\Win\Yaku\Fan1\AllSimplesYaku;
use Saki\Win\Yaku\Fan1\ConcealedSelfDrawYaku;
use Saki\Win\Yaku\Fan1\DoubleRunYaku;
use Saki\Win\Yaku\Fan1\FirstTurnWinYaku;
use Saki\Win\Yaku\Fan1\GreenValueTilesYaku;
use Saki\Win\Yaku\Fan1\ReachYaku;
use Saki\Win\Yaku\Fan1\RedValueTilesYaku;
use Saki\Win\Yaku\Fan1\RoundWindValueTilesYaku;
use Saki\Win\Yaku\Fan1\SelfWindValueTilesYaku;
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
use Saki\Win\Yaku\Yakuman\FourConcealedTriplesYaku;
use Saki\Win\Yaku\Yakuman\FourQuadsYaku;
use Saki\Win\Yaku\Yakuman\SmallFourWindsYaku;
use Saki\Win\Yaku\Yakuman2\FourConcealedTriplesOnePairWaitingYaku;

class YakuSet extends ArrayLikeObject {
    static function getStandardYakuSet() {
        $standardYakus = [
            // Fan1
            AllRunsYaku::getInstance(),
            AllSimplesYaku::getInstance(),
            ConcealedSelfDrawYaku::getInstance(),
            DoubleRunYaku::getInstance(),
            FirstTurnWinYaku::getInstance(),
            GreenValueTilesYaku::getInstance(),
            ReachYaku::getInstance(),
            RedValueTilesYaku::getInstance(),
            RoundWindValueTilesYaku::getInstance(),
            SelfWindValueTilesYaku::getInstance(),
            WhiteValueTilesYaku::getInstance(),
            // Fan2
            AllTerminalsAndHonorsYaku::getInstance(),
            AllTriplesYaku::getInstance(),
            DoubleReachYaku::getInstance(),
            FullStraightYaku::getInstance(),
            LittleThreeDragonsYaku::getInstance(),
            MixedOutsideHandYaku::getInstance(),
            SevenPairsYaku::getInstance(),
            ThreeColorRunsYaku::getInstance(),
            ThreeColorTriplesYaku::getInstance(),
            ThreeConcealedTriplesYaku::getInstance(),
            ThreeQuadsYaku::getInstance(),
            // Fan3
            HalfFlushYaku::getInstance(),
            PureOutsideHandYaku::getInstance(),
            TwoDoubleRunYaku::getInstance(),
            // Fan6
            FullFlushYaku::getInstance(),
            // Yakuman
            AllGreenYaku::getInstance(),
            AllHonorsYaku::getInstance(),
            AllTerminalsYaku::getInstance(),
            BigFourWindsYaku::getInstance(),
            BigThreeDragonsYaku::getInstance(),
            FourConcealedTriplesYaku::getInstance(),
            FourQuadsYaku::getInstance(),
            SmallFourWindsYaku::getInstance(),
            // Yakuman2
            FourConcealedTriplesOnePairWaitingYaku::getInstance(),
        ];
        return new YakuSet($standardYakus);
    }

    function __construct(array $yakus) {
        parent::__construct($yakus, false);
    }
}