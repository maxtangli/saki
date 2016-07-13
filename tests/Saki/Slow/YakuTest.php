<?php

use Saki\Game\PrevailingStatus;
use Saki\Game\PrevailingWind;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Fan1\AfterAKongWinYaku;
use Saki\Win\Yaku\Fan1\AllSimplesYaku;
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
use Saki\Win\Yaku\Yaku;
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
use Saki\Win\Yaku\Yakuman2\PureThirteenOrphansYaku;

class YakuTest extends \SakiTestCase {
    static function assertYakuExist($expected, YakuTestData $yakuTestData, Yaku $yaku) {
        $subTarget = $yakuTestData->toWinSubTarget();
        self::assertEquals($expected, $yaku->existIn($subTarget),
            sprintf(
                "yaku        : %s"
                . "\nyakuTestData: %s"
                . "\nSubTarget   : currentSeatWind[%s], targetSeatWind[%s],%s\n",
                $yakuTestData,
                $yaku,
                $subTarget->getRound()->getCurrentSeatWind()->getWindTile(),
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
            // test AllSimplesYaku
            [new YakuTestData('234m,456m,888s,55s', '678m'), AllSimplesYaku::create(), true],
            // not without term
            [new YakuTestData('234m,456m,888s,55s', '789m'), AllSimplesYaku::create(), false],
            // not without honour
            [new YakuTestData('234m,456m,888s,EE', '789m'), AllSimplesYaku::create(), false],

            // test FullyConcealedHandYaku
            [new YakuTestData('123m,456m,77m,88m,11s,55s', null, '1s'), FullyConcealedHandYaku::create(), true],
            // not isConcealed
            [new YakuTestData('123m,77m,88m,11s,55s', '333m', '1s'), FullyConcealedHandYaku::create(), false],
            // not selfDraw
            [new YakuTestData('123m,456m,77m,88m,11s,55s', null, '1s', 'E', 'W'), FullyConcealedHandYaku::create(), false],

            // test DoubleChowYaku
            [new YakuTestData('123m,123m,77m,88m,11s,EE', null, 'E'), PureDoubleChowYaku::create(), true],
            // not isConcealed
            [new YakuTestData('123m,123m,EE', '123s,123s', 'E'), PureDoubleChowYaku::create(), false],

            // test TwoDoubleChowYaku
            [new YakuTestData('123m,123m,123s,123s,EE', null, 'E'), TwicePureDoubleChowYaku::create(), true],
            // not non-duplicate
            [new YakuTestData('123m,123m,123m,123m,EE', null, 'E'), TwicePureDoubleChowYaku::create(), false],
            // not isConcealed
            [new YakuTestData('123m,123m,EE', '123s,123s', 'E'), TwicePureDoubleChowYaku::create(), false],

            // reach and bottomOfTheSea yakus is tested in separate functions

            // not reach
            [new YakuTestData('123m,456m,789m,123s,55s', null, '1s'), RiichiYaku::create(), false],

            // test RedValueTilesYaku
            [new YakuTestData('123m,44m,55m,66m,55s', 'CCC', '5s'), DragonPungRedYaku::create(), true],
            [new YakuTestData('123m,44m,55m,66m,55s', '111s', '5s'), DragonPungRedYaku::create(), false],

            // test WhiteValueTilesYaku
            [new YakuTestData('123m,44m,55m,66m,55s', 'PPP', '5s'), DragonPungWhiteYaku::create(), true],
            [new YakuTestData('123m,44m,55m,66m,55s', '111s', '5s'), DragonPungWhiteYaku::create(), false],

            // test GreenValueTilesYaku
            [new YakuTestData('123m,44m,55m,66m,55s', 'FFF', '5s'), DragonPungGreenYaku::create(), true],
            [new YakuTestData('123m,44m,55m,66m,55s', '111s', '5s'), DragonPungGreenYaku::create(), false],

            // test PinfuYaku
            [new YakuTestData('123m,456m,789m,123s,55s', null, '1s'), PinfuYaku::create(), true],
            // not isConcealed
            [new YakuTestData('123m,456m,123s,55s', '789m', '1s'), PinfuYaku::create(), false],
            // not 4 run
            [new YakuTestData('123m,456m,999m,123s,55s', null, '1s'), PinfuYaku::create(), false],
            // not suit pair
            [new YakuTestData('123m,456m,789m,123s,EE', null, '1s'), PinfuYaku::create(), false],
            // not two-pair waiting
            [new YakuTestData('123m,456m,789m,123s,55s', null, '2s'), PinfuYaku::create(), false],

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
            // test AllTerminalsAndHonoursYaku
            [new YakuTestData('111m,999m,111s,EE', "999s"), AllTerminalsAndHonoursYaku::create(), true],
            [new YakuTestData('111m,999m,111s,11p', "999s"), AllTerminalsAndHonoursYaku::create(), true],
            // not all terminals
            [new YakuTestData('123m,999m,111s,EE', "999s"), AllTerminalsAndHonoursYaku::create(), false],
            // not 4+1
            [new YakuTestData('11m,99m,11p,99p,11s,99s,EE'), AllTerminalsAndHonoursYaku::create(), false],

            // test AllTriplesYaku
            [new YakuTestData('111m,999m,111s,EE', "999s"), AllPungsYaku::create(), true],
            [new YakuTestData('111m,999m,111s,EE', "9999s"), AllPungsYaku::create(), true],
            // not 4+1
            [new YakuTestData('111m,99m,11s,22s,EE', "999s"), AllPungsYaku::create(), false],
            // not all triples
            [new YakuTestData('123m,999m,111s,EE', "999s"), AllPungsYaku::create(), false],

            // DoubleRiichi is tested in fan1 with Riichi

            // test FullStraightYaku
            [new YakuTestData('123m,456m,111s,EE', '789m'), PureStraightYaku::create(), true],
            // not full straight
            [new YakuTestData('123m,456m,111s,EE', '789s'), PureStraightYaku::create(), false],

            // test LittleThreeDragonsYaku
            [new YakuTestData('CCC,PPP,FF,11m,22m,33m'), LittleThreeDragonsYaku::create(), true],
            [new YakuTestData('CCC,FF,11m,22m,33m', 'PPPP'), LittleThreeDragonsYaku::create(), true],
            // not 2 pung + 1 pair
            [new YakuTestData('CCC,PPP,FFF,11m', '789m'), LittleThreeDragonsYaku::create(), false],

            // test MixedOutsideHandYaku
            [new YakuTestData('123m,789m,123s,EE', '789s'), OutsideHandYaku::create(), true],
            [new YakuTestData('123m,789m,123s,11s', '789s'), OutsideHandYaku::create(), true],
            // not any run
            [new YakuTestData('111m,999m,111p,11s', '9999p'), OutsideHandYaku::create(), false],
            // not all outside
            [new YakuTestData('123m,789m,123s,11s', '678s'), OutsideHandYaku::create(), false],
            // not 4+1
            [new YakuTestData('11m,99m,11p,99p,11s,99s,EE'), OutsideHandYaku::create(), false],

            // test PureOutsideHandYaku
            [new YakuTestData('123m,789m,123s,11s', '789s'), TerminalsInAllSetsYaku::create(), true],
            // not any run
            [new YakuTestData('111m,999m,111p,11s', '999p'), TerminalsInAllSetsYaku::create(), false],
            // not all outside
            [new YakuTestData('123m,789m,123s,11s', '678s'), TerminalsInAllSetsYaku::create(), false],
            // not pure outside
            [new YakuTestData('123m,789m,123s,EE', '789s'), TerminalsInAllSetsYaku::create(), false],
            // not 4+1
            [new YakuTestData('11m,99m,11p,99p,11s,99s,11s'), TerminalsInAllSetsYaku::create(), false],

            // test SevenPairsYaku
            [new YakuTestData('11m,22m,33s,44m,55p,EE,CC'), SevenPairsYaku::create(), true],
            // not different pairs
            [new YakuTestData('11m,22m,33s,44m,55p,EE,EE'), SevenPairsYaku::create(), false],
            // not all pairs
            [new YakuTestData('11m,222m,333s,55p,EE,CC'), SevenPairsYaku::create(), false],

            // test ThreeColorRunsYaku
            [new YakuTestData('234m,234p,EEE,NN', '234s'), MixedTripleChowYaku::create(), true],
            // not all three color
            [new YakuTestData('234m,234m,EEE,NN', '234s'), MixedTripleChowYaku::create(), false],

            // test ThreeColorTriplesYaku
            [new YakuTestData('333m,333s,NN', '333p,234s'), TriplePungYaku::create(), true],
            [new YakuTestData('333m,333s,NN', '3333p,234s'), TriplePungYaku::create(), true],
            [new YakuTestData('333m,333s,NN', '(3333p),234s'), TriplePungYaku::create(), true],
            // not same number
            [new YakuTestData('333m,444s,NN', '3333p,234s'), TriplePungYaku::create(), false],
            // not same number, with honour
            [new YakuTestData('333m,444s,NN', '3333p,CCCC'), TriplePungYaku::create(), false],
            // not different color(though not practical)
            [new YakuTestData('333m,333m,NN', '3333p,234s'), TriplePungYaku::create(), false],

            // test ThreeConcealedTriplesYaku
            [new YakuTestData('111m,222m,444m,123s,EE'), ThreeConcealedPungsYaku::create(), true],
            [new YakuTestData('111m,222m,123s,EE', '(4444m)'), ThreeConcealedPungsYaku::create(), true],
            // not isConcealed
            [new YakuTestData('111m,222m,123s,EE', '444m'), ThreeConcealedPungsYaku::create(), false],
            // not three
            [new YakuTestData('111m,234m,456m,444m,EE'), ThreeConcealedPungsYaku::create(), false],
            [new YakuTestData('111m,222m,333m,444m,EE'), ThreeConcealedPungsYaku::create(), false],

            // test ThreeQuadsYaku
            [new YakuTestData('123s,44s', '1111m,2222m,3333m'), ThreeKongsYaku::create(), true],
            [new YakuTestData('123s,44s', '1111m,2222m,(3333m)'), ThreeKongsYaku::create(), true],
            // not three
            [new YakuTestData('123s,44s', '1111m,2222m,333m'), ThreeKongsYaku::create(), false],
            [new YakuTestData('44s', '1111m,2222m,3333m,4444m'), ThreeKongsYaku::create(), false],
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
            // no honour
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
            // TwoDoubleChow tested in fan1 with DoubleChow
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
            // not 41 series
            [new YakuTestData('22s,22s,33s,44s,66s,88s,FF'), AllGreenYaku::create(), false],
            // not all green tiles
            [new YakuTestData('222s,333s,FF', '666s,111s'), AllGreenYaku::create(), false],

            // test AllHonoursYaku
            [new YakuTestData('CC', 'EEE,SSS,WWW,NNN'), AllHonoursYaku::create(), true],
            [new YakuTestData('EEE,SSS,WWW,NNN,CC'), AllHonoursYaku::create(), true],
            [new YakuTestData('EE,SS,WW,NN,CC,PP,FF'), AllHonoursYaku::create(), true],
            // not all honours
            [new YakuTestData('EEE,SSS,WWW,NNN,11s'), AllHonoursYaku::create(), false],

            // test AllTerminalsYaku
            [new YakuTestData('111m,999m,11s', '111p,999p'), AllTerminalsYaku::create(), true],
            // not 41 series
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
            [new YakuTestData('111s,222s,333s,444s,55s'), FourConcealedPungsYaku::create(), true],
            [new YakuTestData('111s,222s,333s,55s', '(4444s)'), FourConcealedPungsYaku::create(), true],
            // not 4 isConcealed
            [new YakuTestData('111s,222s,333s,55s', '444s'), FourConcealedPungsYaku::create(), false],
            // not 4
            [new YakuTestData('111s,222s,333s,456s,55s'), FourConcealedPungsYaku::create(), false],

            // test FourQuadsYaku
            [new YakuTestData('11s', '2222s,3333s,4444s,5555s'), FourKongsYaku::create(), true],
            [new YakuTestData('11s', '2222s,3333s,4444s,(5555s)'), FourKongsYaku::create(), true],
            // not 4 quads
            [new YakuTestData('11s', '2222s,3333s,4444s,555s'), FourKongsYaku::create(), false],

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
            [new YakuTestData('EEE,SSS,WW', 'NNN,123s'), LittleFourWindsYaku::create(), true],
            [new YakuTestData('EEE,SSS,WW', 'NNNN,123s'), LittleFourWindsYaku::create(), true],
            // not 3+1 winds
            [new YakuTestData('EEE,SSS,WWW,11s', 'NNN'), LittleFourWindsYaku::create(), false],

            // test ThirteenOrphansYaku
            [new YakuTestData('119m19p19sESWNCPF', null, '1m'), ThirteenOrphansYaku::create(), true],
            [new YakuTestData('119m19p19sESWNCPF', null, '9m'), ThirteenOrphansYaku::create(), true],
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
                [new YakuTestData('111s,222s,333s,444s,55s', null, '5s'), PureFourConcealedPungsYaku::create(), true],
                [new YakuTestData('111s,222s,333s,55s', '(4444s)', '5s'), PureFourConcealedPungsYaku::create(), true],
                // not one pair waiting
                [new YakuTestData('111s,222s,333s,444s,55s', null, '4s'), PureFourConcealedPungsYaku::create(), false],
                [new YakuTestData('111s,222s,333s,55s', '(4444s)', '4s'), PureFourConcealedPungsYaku::create(), false],
                // not 4 isConcealed
                [new YakuTestData('111s,222s,333s,55s', '444s', '5s'), PureFourConcealedPungsYaku::create(), false],
                // not 4
                [new YakuTestData('111s,222s,333s,456s,55s', null, '5s'), PureFourConcealedPungsYaku::create(), false],

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
                [new YakuTestData('119m19p19sESWNCPF', null, '1m'), PureThirteenOrphansYaku::create(), true],
                [new YakuTestData('119m19p19sESWNCPF', null, '9m'), PureThirteenOrphansYaku::create(), false],
            ];
        }
    }

    function testRiichi() {
        $round = $this->getInitRound();
        $round->process(
            // pass first round (to avoid double reach), E reach
            'skip 4; mockHand E 123456789s2355mE; riichi E E; passAll',
            // S discard, E may win
            'mockHand S 1m; discard S 1m'
        );
        $this->assertYakuList('E', [RiichiYaku::create()]);
    }

    function testDoubleRiichi() {
        $round = $this->getInitRound();
        $round->process(
            // E double reach
            'mockHand E 123456789s2355mE; riichi E E; passAll',
            // S discard, E may win
            'mockHand S 1m; discard S 1m'
        );
        $this->assertYakuList('E', [DoubleRiichiYaku::create()], null, [RiichiYaku::create()]);
    }

    function testFirstTurnWin() {
        $round = $this->getInitRound();
        $round->process(
            // S double reach
            'skip 1; mockHand S 123456789s2355mS; riichi S S; passAll; skip 2',
            // S tsumo FirstTurnWin
            'mockHand E E; discard E E; mockNextDraw 1m; passAll'
        );
        $this->assertHand(null, null, '1m', 'S');
        $this->assertYakuList('S', [FirstTurnWinYaku::create()]);
    }

    function testKingSTileWin() {
        $round = $this->getInitRound();
        $round->process(
            'mockNextReplace 5m; mockHand E 123s456s789s7777m5m; concealedKong E 7m7m7m7m'
        );
        $this->assertYakuList('E', [AfterAKongWinYaku::create()]);
    }

    function testRobbingAQuad() {
        $round = $this->getInitRound();
        $round->process(
            'skip 4',
            'mockHand W 23m123456789s11p',
            'mockHand E 1m; discard E 1m',
            'mockHand S 11m; pung S 1m1m; mockHand S 1m; extendKong S 1m 111m'
        );
        $this->assertHand(null, null, '1m', 'W');
        $this->assertYakuList('W', [RobbingAKongYaku::create()]);
    }

    function testBottomOfTheSeaMoon() {
        // todo
    }

    function testBottomOfTheSeaFish() {
        // todo
    }

    function testDora() {
        $round = $this->getInitRound();

        // dora not counted without other yakus
        $round->process('skip 4; mockDeadWall EEEE1919293949s 5 false; mockHand E 222789s789m12345m');
        $this->assertYakuListEmpty('E');

        // dora counted with other yakus
        $round->process('mockHand E 222789s789m12355m');
        $this->assertYakuList('E', [DoraYaku::create()], 1 + 6); // selfDraw + 6 dora
    }

    function testUraDora() {
        $round = $this->getInitRound();

        // uraDora not counted without other yakus
        $round->process('skip 4; mockDeadWall EEEE9191929394s 5 true; mockHand E 222789s789m12345m');
        $this->assertYakuListEmpty('E');

        // uraDora counted with other yakus
        $round->process('mockHand E 222789s789m12355m');
        $this->assertYakuList('E', [UraDoraYaku::create()], 1 + 6); // selfDraw + 6 uraDora
    }

    function testRedDora() {
        $round = $this->getInitRound();

        // redDora not counted without other yakus
        $round->process('skip 4; mockDeadWall EEEE9999999999s 1 false; mockHand E 222789s789m12340m');
        $this->assertYakuListEmpty('E');

        // redDora counted with other yakus
        $round->process('mockHand E 222789s789m12300m');
        $this->assertYakuList('E', [RedDoraYaku::create()], 3); // selfDraw + 2 redDora
    }

    function testBlessingOfHeaven() {
        $round = $this->getInitRound();

        $round->process('mockHand E 123456789m12355s');
        $this->assertYakuList('E', [BlessingOfHeavenYaku::create()]);

        // failed if declared
        $round->process('mockHand E 1111m; concealedKong E 1111m; mockHand E 123456789m55s');
        $this->assertYakuList('E', null, null, [BlessingOfHeavenYaku::create()]);

        // failed if not first turn
        // failed if not dealer
    }

    function testBlessingOfEarth() {
        $round = $this->getInitRound();
        $round->process('skip 1; mockHand S 123456789m12355s');
        $this->assertYakuList('S', [BlessingOfEarthYaku::create()]);

        // failed if declared
        // failed if not first turn
        // failed if not leisure
    }

    function testBlessingOfMan() {
        $round = $this->getInitRound();
        $round->process('mockHand E 5s; discard E 5s; mockHand S 123456789m1235s');
        $this->assertYakuList('S', [BlessingOfManYaku::create()]);

        // failed if discard not empty
        $round->process(
            'passAll; mockHand S C; discard S C; passAll',
            'mockHand S 123456789m1235s; mockHand W 5s; discard W 5s'
        );
        $this->assertYakuList('S', null, null, [BlessingOfManYaku::create()]);

        // failed if declared
        // failed if not first turn
    }
}

/**
 * Convenient adaptor for write yaku test cases.
 * todo remove
 * goal
 * - YakuTestCase: RoundDebugSetData RoundDebugSkipToData targetPlayer yaku isExist getRound
 */
class YakuTestData {
    private static $round;

    static function getInitedRound(PrevailingStatus $rebugResetData = null) {
        self::$round = self::$round ?? new Round();
        self::$round->debugInit($rebugResetData ?? PrevailingStatus::createFirst());
        return self::$round;
    }

    private $handMeldList;
    private $melded;
    private $targetTile;
    private $currentSeatWind;
    private $actorSeatWind;

    function __construct(string $handMeldList, string $melded = null, string $targetTile = null,
                         string $current = null, string $actor = null, string $prevailingWind = null) {
        $this->handMeldList = MeldList::fromString($handMeldList)->toConcealed(true);
        $this->melded = MeldList::fromString($melded !== null ? $melded : "");
        $this->targetTile = $targetTile !== null ? Tile::fromString($targetTile) : $this->handMeldList[0][0];

        $this->currentSeatWind = SeatWind::fromString($current ?? 'E');
        $this->actorSeatWind = $actor !== null ? SeatWind::fromString($actor) : $this->currentSeatWind;

        $this->roundDebugResetData = new PrevailingStatus(PrevailingWind::fromString($prevailingWind ?? 'E'), 1, 0);
    }

    function __toString() {
        return sprintf('handMeldList[%s], declaredMeldList[%s], currentSeatWind[%s], targetSeatWind[%s]'
            , $this->handMeldList, $this->melded, $this->currentSeatWind, $this->actorSeatWind);
    }

    function toWinSubTarget() {
        $round = self::getInitedRound($this->roundDebugResetData);

        // set phase
        $currentSeatWind = $this->currentSeatWind;
        $actorSeatWind = $this->actorSeatWind;
        $isPrivate = $currentSeatWind == $actorSeatWind;

        // set tiles
        $handMeldList = $this->handMeldList;
        $targetTile = $this->targetTile;
        $area = $round->getArea($actorSeatWind);
        $melded = $this->melded;

        while ($round->getCurrentSeatWind() != $currentSeatWind) {
            $round->process('skip 1');
        }
        if (!$isPrivate) {
            $round->process(sprintf('mockHand %s %s; discard %s %s',
                $currentSeatWind, $targetTile, $currentSeatWind, $targetTile));
        }

        if ($isPrivate) { // target tile not set
            $private = $handMeldList->toTileList();
            $targetTile = $targetTile ?? $private->getLast();
            $public = $private->getCopy()->remove($targetTile);
            $hand = $area->getHand()->toHand($public, $melded, $targetTile);
        } else { // targetTile already set by debugSkipTo
            $public = $handMeldList->toTileList()->remove($targetTile);
            $hand = $area->getHand()->toHand($public, $melded, null);
        }
        $area->setHand($hand);

        return new WinSubTarget($handMeldList, $actorSeatWind, $round);
    }
}