<?php

use Saki\Game\Phase;
use Saki\Game\PrevailingStatus;
use Saki\Game\PrevailingWind;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Win\WinSubTarget;
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
use Saki\Win\Yaku\Yaku;
use Saki\Win\Yaku\Yakuman\AllGreenYaku;
use Saki\Win\Yaku\Yakuman\AllHonorsYaku;
use Saki\Win\Yaku\Yakuman\AllTerminalsYaku;
use Saki\Win\Yaku\Yakuman\BigFourWindsYaku;
use Saki\Win\Yaku\Yakuman\BigThreeDragonsYaku;
use Saki\Win\Yaku\Yakuman\FourConcealedTriplesYaku;
use Saki\Win\Yaku\Yakuman\FourQuadsYaku;
use Saki\Win\Yaku\Yakuman\NineGatesYaku;
use Saki\Win\Yaku\Yakuman\SmallFourWindsYaku;
use Saki\Win\Yaku\Yakuman\ThirteenOrphansYaku;
use Saki\Win\Yaku\Yakuman2\FourConcealedTriplesOnePairWaitingYaku;
use Saki\Win\Yaku\Yakuman2\ThirteenOrphansPairWaitingYaku;

class YakuTest extends \PHPUnit_Framework_TestCase {
    static function assertYakuExist($expected, YakuTestData $yakuTestData, Yaku $yaku) {
        $subTarget = $yakuTestData->toWinSubTarget();
        self::assertEquals($expected, $yaku->existIn($subTarget),
            sprintf(
                "yaku        : %s"
                . "\nyakuTestData: %s"
                . "\nSubTarget   : currentSeatWind[%s], targetSeatWind[%s],%s\n",
                $yakuTestData,
                $yaku,
                $subTarget->getAreas()->getCurrentSeatWind()->getWindTile(),
                $subTarget->getSeatWindTile(),
                'isPrivatePhase:' . var_export($subTarget->isPrivatePhase(), true)
            )
        );
    }

    /**
     * @dataProvider fan1Provider
     */
    function testFan1(YakuTestData $yakuTestData, Yaku $yaku, $expected) {
        $this->assertYakuExist($expected, $yakuTestData, $yaku);
    }

    function fan1Provider() {
        return [
            // test AllRunsYaku
            [new YakuTestData('123m,456m,789m,123s,55s', null, '1s'), AllRunsYaku::create(), true],
            // not isConcealed
            [new YakuTestData('123m,456m,123s,55s', '789m', '1s'), AllRunsYaku::create(), false],
            // not 4 run
            [new YakuTestData('123m,456m,999m,123s,55s', null, '1s'), AllRunsYaku::create(), false],
            // not suit pair
            [new YakuTestData('123m,456m,789m,123s,EE', null, '1s'), AllRunsYaku::create(), false],
            // not two-pair waiting
            [new YakuTestData('123m,456m,789m,123s,55s', null, '2s'), AllRunsYaku::create(), false],

            // test AllSimplesYaku
            [new YakuTestData('234m,456m,888s,55s', '678m'), AllSimplesYaku::create(), true],
            // not without terminal
            [new YakuTestData('234m,456m,888s,55s', '789m'), AllSimplesYaku::create(), false],
            // not without honor
            [new YakuTestData('234m,456m,888s,EE', '789m'), AllSimplesYaku::create(), false],

            // test FullyConcealedHandYaku
            [new YakuTestData('123m,456m,77m,88m,11s,55s', null, '1s'), FullyConcealedHandYaku::create(), true],
            // not isConcealed
            [new YakuTestData('123m,77m,88m,11s,55s', '333m', '1s'), FullyConcealedHandYaku::create(), false],
            // not selfDraw
            [new YakuTestData('123m,456m,77m,88m,11s,55s', null, '1s', 'E', 'W'), FullyConcealedHandYaku::create(), false],

            // test DoubleRunYaku
            [new YakuTestData('123m,123m,77m,88m,11s,EE', null, 'E'), DoubleRunYaku::create(), true],
            // not isConcealed
            [new YakuTestData('123m,123m,EE', '123s,123s', 'E'), DoubleRunYaku::create(), false],

            // test TwoDoubleRunYaku
            [new YakuTestData('123m,123m,123s,123s,EE', null, 'E'), TwoDoubleRunYaku::create(), true],
            // not non-duplicate
            [new YakuTestData('123m,123m,123m,123m,EE', null, 'E'), TwoDoubleRunYaku::create(), false],
            // not isConcealed
            [new YakuTestData('123m,123m,EE', '123s,123s', 'E'), TwoDoubleRunYaku::create(), false],

            // reach and finalTileWin yakus is tested in separate functions

            // not reach
            [new YakuTestData('123m,456m,789m,123s,55s', null, '1s'), ReachYaku::create(), false],

            // test RedValueTilesYaku
            [new YakuTestData('123m,44m,55m,66m,55s', 'CCC', '5s'), RedValueTilesYaku::create(), true],
            [new YakuTestData('123m,44m,55m,66m,55s', '111s', '5s'), RedValueTilesYaku::create(), false],

            // test WhiteValueTilesYaku
            [new YakuTestData('123m,44m,55m,66m,55s', 'PPP', '5s'), WhiteValueTilesYaku::create(), true],
            [new YakuTestData('123m,44m,55m,66m,55s', '111s', '5s'), WhiteValueTilesYaku::create(), false],

            // test GreenValueTilesYaku
            [new YakuTestData('123m,44m,55m,66m,55s', 'FFF', '5s'), GreenValueTilesYaku::create(), true],
            [new YakuTestData('123m,44m,55m,66m,55s', '111s', '5s'), GreenValueTilesYaku::create(), false],

            // test PrevailingWindValueTilesYaku
            [new YakuTestData('123m,44m,55m,66m,55s', 'EEE', '5s'), PrevailingWindYaku::create(), true],
            // not prevailingWind
            [(new YakuTestData('123m,44m,55m,66m,55s', 'EEE', '5s', null, null, PrevailingWind::fromString('S')))
                , PrevailingWindYaku::create(), false],

            // test SeatWindValueTilesYaku
            [new YakuTestData('123m,44m,55m,66m,55s', 'SSS', '5s', 'E', 'S'), SeatWindYaku::create(), true],
            // not seatWind
            [new YakuTestData('123m,44m,55m,66m,55s', 'SSS', '5s', 'E', 'E'), SeatWindYaku::create(), false],
        ];
    }

    /**
     * @dataProvider fan2Provider
     */
    function testFan2(YakuTestData $yakuTestData, Yaku $yaku, $expected) {
        $this->assertYakuExist($expected, $yakuTestData, $yaku);
    }

    function fan2Provider() {
        return [
            // test AllTerminalsAndHonorsYaku
            [new YakuTestData('111m,999m,111s,EE', "999s"), AllTerminalsAndHonorsYaku::create(), true],
            [new YakuTestData('111m,999m,111s,11p', "999s"), AllTerminalsAndHonorsYaku::create(), true],
            // not all terminals
            [new YakuTestData('123m,999m,111s,EE', "999s"), AllTerminalsAndHonorsYaku::create(), false],
            // not 4+1
            [new YakuTestData('11m,99m,11p,99p,11s,99s,EE'), AllTerminalsAndHonorsYaku::create(), false],

            // test AllTriplesYaku
            [new YakuTestData('111m,999m,111s,EE', "999s"), AllTriplesYaku::create(), true],
            [new YakuTestData('111m,999m,111s,EE', "9999s"), AllTriplesYaku::create(), true],
            // not 4+1
            [new YakuTestData('111m,99m,11s,22s,EE', "999s"), AllTriplesYaku::create(), false],
            // not all triples
            [new YakuTestData('123m,999m,111s,EE', "999s"), AllTriplesYaku::create(), false],

            // DoubleReach is tested in fan1 with Reach

            // test FullStraightYaku
            [new YakuTestData('123m,456m,111s,EE', '789m'), FullStraightYaku::create(), true],
            // not full straight
            [new YakuTestData('123m,456m,111s,EE', '789s'), FullStraightYaku::create(), false],

            // test LittleThreeDragonsYaku
            [new YakuTestData('CCC,PPP,FF,11m,22m,33m'), LittleThreeDragonsYaku::create(), true],
            [new YakuTestData('CCC,FF,11m,22m,33m', 'PPPP'), LittleThreeDragonsYaku::create(), true],
            // not 2 pong + 1 pair
            [new YakuTestData('CCC,PPP,FFF,11m', '789m'), LittleThreeDragonsYaku::create(), false],

            // test MixedOutsideHandYaku
            [new YakuTestData('123m,789m,123s,EE', '789s'), MixedOutsideHandYaku::create(), true],
            [new YakuTestData('123m,789m,123s,11s', '789s'), MixedOutsideHandYaku::create(), true],
            // not any run
            [new YakuTestData('111m,999m,111p,11s', '9999p'), MixedOutsideHandYaku::create(), false],
            // not all outside
            [new YakuTestData('123m,789m,123s,11s', '678s'), MixedOutsideHandYaku::create(), false],
            // not 4+1
            [new YakuTestData('11m,99m,11p,99p,11s,99s,EE'), MixedOutsideHandYaku::create(), false],

            // test PureOutsideHandYaku
            [new YakuTestData('123m,789m,123s,11s', '789s'), PureOutsideHandYaku::create(), true],
            // not any run
            [new YakuTestData('111m,999m,111p,11s', '999p'), PureOutsideHandYaku::create(), false],
            // not all outside
            [new YakuTestData('123m,789m,123s,11s', '678s'), PureOutsideHandYaku::create(), false],
            // not pure outside
            [new YakuTestData('123m,789m,123s,EE', '789s'), PureOutsideHandYaku::create(), false],
            // not 4+1
            [new YakuTestData('11m,99m,11p,99p,11s,99s,11s'), PureOutsideHandYaku::create(), false],

            // test SevenPairsYaku
            [new YakuTestData('11m,22m,33s,44m,55p,EE,CC'), SevenPairsYaku::create(), true],
            // not different pairs
            [new YakuTestData('11m,22m,33s,44m,55p,EE,EE'), SevenPairsYaku::create(), false],
            // not all pairs
            [new YakuTestData('11m,222m,333s,55p,EE,CC'), SevenPairsYaku::create(), false],

            // test ThreeColorRunsYaku
            [new YakuTestData('234m,234p,EEE,NN', '234s'), ThreeColorRunsYaku::create(), true],
            // not all three color
            [new YakuTestData('234m,234m,EEE,NN', '234s'), ThreeColorRunsYaku::create(), false],

            // test ThreeColorTriplesYaku
            [new YakuTestData('333m,333s,NN', '333p,234s'), ThreeColorTriplesYaku::create(), true],
            [new YakuTestData('333m,333s,NN', '3333p,234s'), ThreeColorTriplesYaku::create(), true],
            [new YakuTestData('333m,333s,NN', '(3333p),234s'), ThreeColorTriplesYaku::create(), true],
            // not same number
            [new YakuTestData('333m,444s,NN', '3333p,234s'), ThreeColorTriplesYaku::create(), false],
            // not same number, with honor
            [new YakuTestData('333m,444s,NN', '3333p,CCCC'), ThreeColorTriplesYaku::create(), false],
            // not different color(though not practical)
            [new YakuTestData('333m,333m,NN', '3333p,234s'), ThreeColorTriplesYaku::create(), false],

            // test ThreeConcealedTriplesYaku
            [new YakuTestData('111m,222m,444m,123s,EE'), ThreeConcealedTriplesYaku::create(), true],
            [new YakuTestData('111m,222m,123s,EE', '(4444m)'), ThreeConcealedTriplesYaku::create(), true],
            // not isConcealed
            [new YakuTestData('111m,222m,123s,EE', '444m'), ThreeConcealedTriplesYaku::create(), false],
            // not three
            [new YakuTestData('111m,234m,456m,444m,EE'), ThreeConcealedTriplesYaku::create(), false],
            [new YakuTestData('111m,222m,333m,444m,EE'), ThreeConcealedTriplesYaku::create(), false],

            // test ThreeQuadsYaku
            [new YakuTestData('123s,44s', '1111m,2222m,3333m'), ThreeQuadsYaku::create(), true],
            [new YakuTestData('123s,44s', '1111m,2222m,(3333m)'), ThreeQuadsYaku::create(), true],
            // not three
            [new YakuTestData('123s,44s', '1111m,2222m,333m'), ThreeQuadsYaku::create(), false],
            [new YakuTestData('44s', '1111m,2222m,3333m,4444m'), ThreeQuadsYaku::create(), false],
        ];
    }

    /**
     * @dataProvider fan3AndFan6Provider
     */
    function testFan3AndFan6(YakuTestData $yakuTestData, Yaku $yaku, $expected) {
        $this->assertYakuExist($expected, $yakuTestData, $yaku);
    }

    function fan3AndFan6Provider() {
        return [
            [new YakuTestData('123m,33m,44m,55m,EEE,SS'), HalfFlushYaku::create(), true],
            // no honor
            [new YakuTestData('123m,33m,44m,55m,123m,11m'), HalfFlushYaku::create(), false],
            // no suit types
            [new YakuTestData('EEE,SSS,WWW,NNN,CC'), HalfFlushYaku::create(), false],
            // not same Suit types
            [new YakuTestData('123m,33m,44m,55m,123m,11s'), HalfFlushYaku::create(), false],

            [new YakuTestData('123m,33m,44m,55m,123m,11m'), FullFlushYaku::create(), true],
            // not all suit
            [new YakuTestData('123m,33m,44m,55m,EEE,SS'), FullFlushYaku::create(), false],
            // no suit types
            [new YakuTestData('EEE,SSS,WWW,NNN,CC'), FullFlushYaku::create(), false],
            // not same Suit types
            [new YakuTestData('123m,33m,44m,55m,123m,11s'), FullFlushYaku::create(), false],

            // PureOutsideHand tested in fan2 with MixedOutsideHand
            // TwoDoubleRun tested in fan1 with DoubleRun
        ];
    }

    /**
     * @dataProvider yakumanProvider
     */
    function testYakuman(YakuTestData $yakuTestData, Yaku $yaku, $expected) {
        $this->assertYakuExist($expected, $yakuTestData, $yaku);
    }

    function yakumanProvider() {
        return [
            // test AllGreenYaku
            [new YakuTestData('234s,234s,FF', '666s,888s'), AllGreenYaku::create(), true],
            [new YakuTestData('222s,333s,444s,666s,88s'), AllGreenYaku::create(), true],
            // not 41 tileSeries
            [new YakuTestData('22s,22s,33s,44s,66s,88s,FF'), AllGreenYaku::create(), false],
            // not all green tiles
            [new YakuTestData('222s,333s,FF', '666s,111s'), AllGreenYaku::create(), false],

            // test AllHonorsYaku
            [new YakuTestData('CC', 'EEE,SSS,WWW,NNN'), AllHonorsYaku::create(), true],
            [new YakuTestData('EEE,SSS,WWW,NNN,CC'), AllHonorsYaku::create(), true],
            [new YakuTestData('EE,SS,WW,NN,CC,PP,FF'), AllHonorsYaku::create(), true],
            // not all honors
            [new YakuTestData('EEE,SSS,WWW,NNN,11s'), AllHonorsYaku::create(), false],

            // test AllTerminalsYaku
            [new YakuTestData('111m,999m,11s', '111p,999p'), AllTerminalsYaku::create(), true],
            // not 41 tileSeries
            [new YakuTestData('11m,11m,99m,11p,99p,11s,99s'), AllTerminalsYaku::create(), false],
            // not all terminals
            [new YakuTestData('EEE,999m,11s', '111p,999p'), AllTerminalsYaku::create(), false],

            // test BigFourWindsYaku
            [new YakuTestData('EEE,SSS,WWW,11s', 'NNN'), BigFourWindsYaku::create(), true],
            [new YakuTestData('EEE,SSS,WWW,11s', 'NNNN'), BigFourWindsYaku::create(), true],
            // not 4 winds
            [new YakuTestData('EEE,SSS,WW', 'NNN,123s'), BigFourWindsYaku::create(), false],

            // test BigThreeDragonsYaku
            [new YakuTestData('CCC,PPP,123s,11s', 'FFF'), BigThreeDragonsYaku::create(), true],
            [new YakuTestData('CCC,PPP,123s,11s', 'FFFF'), BigThreeDragonsYaku::create(), true],
            [new YakuTestData('CCC,PPP,123s,11s', '(FFFF)'), BigThreeDragonsYaku::create(), true],
            // not 3 tripleOrQuads
            [new YakuTestData('CC,PPP,123s,EEE', 'FFF'), BigThreeDragonsYaku::create(), false],

            // test FourConcealedTriplesYaku
            [new YakuTestData('111s,222s,333s,444s,55s'), FourConcealedTriplesYaku::create(), true],
            [new YakuTestData('111s,222s,333s,55s', '(4444s)'), FourConcealedTriplesYaku::create(), true],
            // not 4 isConcealed
            [new YakuTestData('111s,222s,333s,55s', '444s'), FourConcealedTriplesYaku::create(), false],
            // not 4
            [new YakuTestData('111s,222s,333s,456s,55s'), FourConcealedTriplesYaku::create(), false],

            // test FourQuadsYaku
            [new YakuTestData('11s', '2222s,3333s,4444s,5555s'), FourQuadsYaku::create(), true],
            [new YakuTestData('11s', '2222s,3333s,4444s,(5555s)'), FourQuadsYaku::create(), true],
            // not 4 quads
            [new YakuTestData('11s', '2222s,3333s,4444s,555s'), FourQuadsYaku::create(), false],

            // test NineGatesYaku
            [new YakuTestData('111s,123s,456s,789s,99s', null, '1s'), NineGatesYaku::create(), true],
            [new YakuTestData('111s,22s,345s,678s,999s', null, '2s'), NineGatesYaku::create(), true],
            [new YakuTestData('111s,22s,345s,678s,999s', null, '3s'), NineGatesYaku::create(), true],
            // not concealed
            [new YakuTestData('111s,22s,345s,678s', '999s'), NineGatesYaku::create(), false],
            // not all suit
            [new YakuTestData('111s,22s,345s,678s,EEE'), NineGatesYaku::create(), false],
            // not same color suit
            [new YakuTestData('111s,22s,345s,678s,999m'), NineGatesYaku::create(), false],

            // test SmallFourWindsYaku
            [new YakuTestData('EEE,SSS,WW', 'NNN,123s'), SmallFourWindsYaku::create(), true],
            [new YakuTestData('EEE,SSS,WW', 'NNNN,123s'), SmallFourWindsYaku::create(), true],
            // not 3+1 winds
            [new YakuTestData('EEE,SSS,WWW,11s', 'NNN'), SmallFourWindsYaku::create(), false],

            // test ThirteenOrphansYaku
            [new YakuTestData('119m19p19sESWNCFP', null, '1m'), ThirteenOrphansYaku::create(), true],
            [new YakuTestData('119m19p19sESWNCFP', null, '9m'), ThirteenOrphansYaku::create(), true],
            [new YakuTestData('EEE,SSS,WW', 'NNN,123s'), ThirteenOrphansYaku::create(), false],
        ];

        /**
         * @dataProvider yakuman2Provider
         */
        function testYakuman2(YakuTestData $yakuTestData, Yaku $yaku, $expected) {
            $this->assertYakuExist($expected, $yakuTestData, $yaku);
        }

        function yakuman2Provider() {
            return [
                // test FourConcealedTriplesOnePairWaitingYaku
                [new YakuTestData('111s,222s,333s,444s,55s', null, '5s'), FourConcealedTriplesOnePairWaitingYaku::create(), true],
                [new YakuTestData('111s,222s,333s,55s', '(4444s)', '5s'), FourConcealedTriplesOnePairWaitingYaku::create(), true],
                // not one pair waiting
                [new YakuTestData('111s,222s,333s,444s,55s', null, '4s'), FourConcealedTriplesOnePairWaitingYaku::create(), false],
                [new YakuTestData('111s,222s,333s,55s', '(4444s)', '4s'), FourConcealedTriplesOnePairWaitingYaku::create(), false],
                // not 4 isConcealed
                [new YakuTestData('111s,222s,333s,55s', '444s', '5s'), FourConcealedTriplesOnePairWaitingYaku::create(), false],
                // not 4
                [new YakuTestData('111s,222s,333s,456s,55s', null, '5s'), FourConcealedTriplesOnePairWaitingYaku::create(), false],

                // test PureNineGatesYaku
                [new YakuTestData('111s,123s,456s,789s,99s', null, '1s'), NineGatesYaku::create(), true],
                [new YakuTestData('111s,22s,345s,678s,999s', null, '2s'), NineGatesYaku::create(), true],
                // not pure
                [new YakuTestData('111s,22s,345s,678s,999s', null, '3s'), NineGatesYaku::create(), false],
                // not concealed
                [new YakuTestData('111s,22s,345s,678s', '999s'), NineGatesYaku::create(), false],
                // not all suit
                [new YakuTestData('111s,22s,345s,678s,EEE'), NineGatesYaku::create(), false],
                // not same color suit
                [new YakuTestData('111s,22s,345s,678s,999m'), NineGatesYaku::create(), false],

                // test ThirteenOrphansPairWaitingYaku
                [new YakuTestData('119m19p19sESWNCFP', null, '1m'), ThirteenOrphansPairWaitingYaku::create(), true],
                [new YakuTestData('119m19p19sESWNCFP', null, '9m'), ThirteenOrphansPairWaitingYaku::create(), false],
            ];
        }
    }

    function testReach() {
        $r = YakuTestData::getInitedRound();
        $pro = $r->getProcessor();

        // pass first round
        $pro->process('discard E E:s-E:E; passAll');
        $pro->process('discard S S:s-E:E; passAll');
        $pro->process('discard W W:s-E:E; passAll');
        $pro->process('discard N N:s-N:N; passAll');

        // E reach
        $pro->process('reach E E:s-123456789s2355mE:E; passAll');

        // S discard, E may win
        $pro->process('discard S S:s-1m:1m');

        $yakuList = $r->getWinReport(SeatWind::createEast())->getYakuList()->toYakuList();

        $this->assertContains(ReachYaku::create(), $yakuList, $yakuList);
    }

    function testDoubleReach() {
        $r = YakuTestData::getInitedRound();
        $pro = $r->getProcessor();
        // E double reach
        $pro->process('reach E E:s-123456789s2355mE:E; passAll');

        // S discard, E may win
        $pro->process('discard S S:s-1m:1m');

        $yakuList = $r->getWinReport(SeatWind::createEast())->getYakuList()->toYakuList();

        $this->assertNotContains(ReachYaku::create(), $yakuList, $yakuList);
        $this->assertContains(DoubleReachYaku::create(), $yakuList, $yakuList);
    }

    function testFirstTurnWin() {
        $r = YakuTestData::getInitedRound();
        $pro = $r->getProcessor();

        $pro->process('discard E E:s-E:E; passAll');
        $pro->process('reach S S:s-123456789s2355mS:S; passAll'); // S double reach
        $pro->process('discard W W:s-E:E; passAll');
        $pro->process('discard N N:s-E:E; passAll');

        $pro->process('discard E E:s-E:E; mockNextDraw 1m; passAll');
        $areaS = $r->getAreas()->getArea(SeatWind::createSouth());
        $this->assertEquals(Tile::fromString('1m'), $areaS->getHand()->getTarget()->getTile());

        // S winBySelf FirstTurnWin
        $yakuList = $r->getWinReport($r->getAreas()->getCurrentSeatWind())->getYakuList()->toYakuList();
        $this->assertContains(FirstTurnWinYaku::create(), $yakuList, $yakuList);
    }

    function testKingSTileWin() {
        $r = YakuTestData::getInitedRound();
        $pro = $r->getProcessor();

        $pro->process('mockNextReplace 5m; concealedKong E E:s-123s456s789s7777m5m:7m');
        $yakuList = $r->getWinReport($r->getAreas()->getCurrentSeatWind())->getYakuList()->toYakuList();
        $this->assertContains(KingSTileWinYaku::create(), $yakuList, $yakuList);
    }

    function testRobbingAQuad() {
        $r = YakuTestData::getInitedRound();
        $pro = $r->getProcessor();

        $pro->process(
            'skip 4',
            'mockHand W 23m123456789s11p',
            'discard E E:s-1m:1m',
            'mockHand S 11m; pong S; plusKong S S:s-1m:1m'
        );
        $areaW = $r->getAreas()->getArea(SeatWind::createWest());

        // target tile changed
        $this->assertEquals(Tile::fromString('1m'), $areaW->getHand()->getTarget()->getTile());

        // robAQuad exist
        $yakuList = $r->getWinReport(SeatWind::createWest())->getYakuList()->toYakuList();
        $this->assertContains(RobbingAQuadYaku::create(), $yakuList, $yakuList);
    }

    function testFinalTileWinMoon() {
        // todo
    }

    function testFinalTileWinFish() {
        // todo
    }

    function testDora() {
        $r = YakuTestData::getInitedRound();
        $pro = $r->getProcessor();
        $pro->process('skip 4; mockDeadWall EEEE1919293949s 5 false; mockHand E 222789s789m12345m');

        // rely other yakus
        $yakuList = $r->getWinReport(SeatWind::createEast())->getYakuList()->toYakuList();
        $this->assertEmpty($yakuList);

        // fan count
        $pro->process('mockHand E 222789s789m12355m');
        $yakuItemList = $r->getWinReport(SeatWind::createEast())->getYakuList();
        $yakuList = $yakuItemList->toYakuList();
        $this->assertContains(DoraYaku::create(), $yakuList, $yakuList);
        $expectFan = 1 + 6; // selfDraw + 6 dora
        $this->assertEquals($expectFan, $yakuItemList->getTotalFan(), $yakuItemList);
    }

    function testUraDora() {
        $r = YakuTestData::getInitedRound();
        $pro = $r->getProcessor();
        $pro->process('skip 4; mockDeadWall EEEE9191929394s 5 true; mockHand E 222789s789m12345m');

        // rely other yakus
        $yakuList = $r->getWinReport(SeatWind::createEast())->getYakuList()->toYakuList();
        $this->assertEmpty($yakuList);

        // fan count
        $pro->process('mockHand E 222789s789m12355m');
        $yakuItemList = $r->getWinReport(SeatWind::createEast())->getYakuList();
        $yakuList = $yakuItemList->toYakuList();
        $this->assertContains(UraDoraYaku::create(), $yakuList, $yakuList);
        $expectFan = 1 + 6; // selfDraw + 6 uraDora
        $this->assertEquals($expectFan, $yakuItemList->getTotalFan(), $yakuItemList);
    }

    function testRedDora() {
        $r = YakuTestData::getInitedRound();
        $pro = $r->getProcessor();
        $pro->process('skip 4; mockDeadWall EEEE9999999999s 1 false; mockHand E 222789s789m12340m');

        // rely other yakus
        $yakuList = $r->getWinReport(SeatWind::createEast())->getYakuList()->toYakuList();
        $this->assertEmpty($yakuList);

        // fan count
        $pro->process('mockHand E 222789s789m12300m');
        $yakuItemList = $r->getWinReport(SeatWind::createEast())->getYakuList();
        $yakuList = $yakuItemList->toYakuList();
        $this->assertContains(RedDoraYaku::create(), $yakuList, $yakuList);
        $expectFan = 3; // selfDraw + 6 uraDora
        $this->assertEquals($expectFan, $yakuItemList->getTotalFan(), $yakuItemList);
        // todo for first time execution, failed and return 2 rather than 3? RedTile impl trick's dangerous smell...
    }

    function testHeavenlyWin() {
        // todo
    }

    function testEarthlyWin() {
        // todo
    }

    function testHumanlyWin() {
        // todo
    }
}

/**
 * Convenient adaptor for write yaku test cases.
 * todo remove
 * goal
 * - YakuTestCase: RoundDebugSetData RoundDebugSkipToData targetPlayer yaku isExist getRound
 */
class YakuTestData {
    private static $r;

    static function getInitedRound(PrevailingStatus $rebugResetData = null) {
        self::$r = self::$r ?? new Round();
        self::$r->debugInit($rebugResetData ?? PrevailingStatus::createFirst());
        return self::$r;
    }

    private $handMeldList;
    private $declareMeldList;
    private $targetTile;
    private $currentSeatWind;
    private $actorSeatWind;

    function __construct(string $handMeldListString, string $declareString = null, string $targetTileString = null,
                         string $currentString = null, string $actorString = null, string $prevailingWindString = null) {
        $this->handMeldList = MeldList::fromString($handMeldListString)->toConcealed(true);
        $this->declareMeldList = MeldList::fromString($declareString !== null ? $declareString : "");
        $this->targetTile = $targetTileString !== null ? Tile::fromString($targetTileString) : $this->handMeldList[0][0];

        $this->currentSeatWind = SeatWind::fromString($currentString ?? 'E');
        $this->actorSeatWind = $actorString !== null ? SeatWind::fromString($actorString) : $this->currentSeatWind;
        
        $prevailingWind = PrevailingWind::fromString($prevailingWindString ?? 'E');
        $this->roundDebugResetData = new PrevailingStatus($prevailingWind, 1, 0);
    }

    function __toString() {
        return sprintf('handMeldList[%s], declaredMeldList[%s], currentSeatWind[%s], targetSeatWind[%s]'
            , $this->handMeldList, $this->declareMeldList, $this->currentSeatWind, $this->actorSeatWind);
    }

    function toWinSubTarget() {
        $r = self::getInitedRound($this->roundDebugResetData);

        // set phase
        $currentSeatWind = $this->currentSeatWind;
        $actorSeatWind = $this->actorSeatWind;
        $isPrivate = $currentSeatWind == $actorSeatWind;

        // set tiles
        $handMeldList = $this->handMeldList;
        $targetTile = $this->targetTile;
        $areas = $r->getAreas();

        $rPhase = $isPrivate ? Phase::createPrivate() : Phase::createPublic();
        $r->debugSkipTo($currentSeatWind, $rPhase, null, null, $targetTile);
        $actorArea = $areas->getArea($actorSeatWind);
        if ($isPrivate) { // target tile not set
            $private = $handMeldList->toTileList();
            $areas->debugSetPrivate($actorArea->getSeatWind(), $private, $this->declareMeldList, $targetTile);
        } else { // targetTile already set by debugSkipTo
            $public = $handMeldList->toTileList()->remove($targetTile);
            $actorArea->debugSet($public, $this->declareMeldList);
        }

        return new WinSubTarget($this->handMeldList, $actorSeatWind, $r);
    }
}