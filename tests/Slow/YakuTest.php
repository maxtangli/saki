<?php

use Saki\Game\Round;
use Saki\Game\RoundDebugResetData;
use Saki\Game\RoundPhase;
use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Win\WinSubTarget;
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
use Saki\Win\Yaku\Yaku;
use Saki\Win\Yaku\Yakuman\AllGreenYaku;
use Saki\Win\Yaku\Yakuman\AllHonorsYaku;
use Saki\Win\Yaku\Yakuman\AllTerminalsYaku;
use Saki\Win\Yaku\Yakuman\BigFourWindsYaku;
use Saki\Win\Yaku\Yakuman\BigThreeDragonsYaku;
use Saki\Win\Yaku\Yakuman\FourConcealedTriplesYaku;
use Saki\Win\Yaku\Yakuman\FourQuadsYaku;
use Saki\Win\Yaku\Yakuman\SmallFourWindsYaku;
use Saki\Win\Yaku\Yakuman2\FourConcealedTriplesOnePairWaitingYaku;

class YakuTest extends \PHPUnit_Framework_TestCase {
    static function assertYakuExist($expected, YakuTestData $yakuTestData, Yaku $yaku) {
        $subTarget = $yakuTestData->toWinSubTarget();
        self::assertEquals($expected, $yaku->existIn($subTarget),
            sprintf("yaku        : %s\nyakuTestData: %s\nSubTarget   : currentPlayerWind[%s], targetPlayerWind[%s],%s\n",
                $yakuTestData, $yaku, $subTarget->getCurrentPlayer()->getSelfWind(), $subTarget->getSelfWind(), 'isPrivatePhase:' . var_export($subTarget->isPrivatePhase(), true)));
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
            [new YakuTestData('123m,456m,789m,123s,55s', null, '1s'), AllRunsYaku::getInstance(), true],
            // not isConcealed
            [new YakuTestData('123m,456m,123s,55s', '789m', '1s'), AllRunsYaku::getInstance(), false],
            // not 4 run
            [new YakuTestData('123m,456m,999m,123s,55s', null, '1s'), AllRunsYaku::getInstance(), false],
            // not suit pair
            [new YakuTestData('123m,456m,789m,123s,EE', null, '1s'), AllRunsYaku::getInstance(), false],
            // not two-pair waiting
            [new YakuTestData('123m,456m,789m,123s,55s', null, '2s'), AllRunsYaku::getInstance(), false],

            // test AllSimplesYaku
            [new YakuTestData('234m,456m,888s,55s', '678m'), AllSimplesYaku::getInstance(), true],
            // not without terminal
            [new YakuTestData('234m,456m,888s,55s', '789m'), AllSimplesYaku::getInstance(), false],
            // not without honor
            [new YakuTestData('234m,456m,888s,EE', '789m'), AllSimplesYaku::getInstance(), false],

            // test ConcealedSelfDrawYaku
            [new YakuTestData('123m,456m,77m,88m,11s,55s', null, '1s'), ConcealedSelfDrawYaku::getInstance(), true],
            // not isConcealed
            [new YakuTestData('123m,77m,88m,11s,55s', '333m', '1s'), ConcealedSelfDrawYaku::getInstance(), false],
            // not selfDraw
            [new YakuTestData('123m,456m,77m,88m,11s,55s', null, '1s', 'E', 'W'), ConcealedSelfDrawYaku::getInstance(), false],

            // test DoubleRunYaku
            [new YakuTestData('123m,123m,77m,88m,11s,EE', null, 'E'), DoubleRunYaku::getInstance(), true],
            // not isConcealed
            [new YakuTestData('123m,123m,EE', '123s,123s', 'E'), DoubleRunYaku::getInstance(), false],

            // test TwoDoubleRunYaku
            [new YakuTestData('123m,123m,123s,123s,EE', null, 'E'), TwoDoubleRunYaku::getInstance(), true],
            // not non-duplicate
            [new YakuTestData('123m,123m,123m,123m,EE', null, 'E'), TwoDoubleRunYaku::getInstance(), false],
            // not isConcealed
            [new YakuTestData('123m,123m,EE', '123s,123s', 'E'), TwoDoubleRunYaku::getInstance(), false],

            // reach and finalTileWin yakus is tested in separate functions

            // not reach
            [new YakuTestData('123m,456m,789m,123s,55s', null, '1s'), ReachYaku::getInstance(), false],

            // test RedValueTilesYaku
            [new YakuTestData('123m,44m,55m,66m,55s', 'CCC', '5s'), RedValueTilesYaku::getInstance(), true],
            [new YakuTestData('123m,44m,55m,66m,55s', '111s', '5s'), RedValueTilesYaku::getInstance(), false],

            // test WhiteValueTilesYaku
            [new YakuTestData('123m,44m,55m,66m,55s', 'PPP', '5s'), WhiteValueTilesYaku::getInstance(), true],
            [new YakuTestData('123m,44m,55m,66m,55s', '111s', '5s'), WhiteValueTilesYaku::getInstance(), false],

            // test GreenValueTilesYaku
            [new YakuTestData('123m,44m,55m,66m,55s', 'FFF', '5s'), GreenValueTilesYaku::getInstance(), true],
            [new YakuTestData('123m,44m,55m,66m,55s', '111s', '5s'), GreenValueTilesYaku::getInstance(), false],

            // test RoundWindValueTilesYaku
            [new YakuTestData('123m,44m,55m,66m,55s', 'EEE', '5s'), RoundWindValueTilesYaku::getInstance(), true],
            // not roundWind
            [(new YakuTestData('123m,44m,55m,66m,55s', 'EEE', '5s', null, null, (new RoundDebugResetData())->setRoundWind(Tile::fromString('S'))))
                , RoundWindValueTilesYaku::getInstance(), false],

            // test SelfWindValueTilesYaku
            [new YakuTestData('123m,44m,55m,66m,55s', 'SSS', '5s', 'E', 'S'), SelfWindValueTilesYaku::getInstance(), true],
            // not selfWind
            [new YakuTestData('123m,44m,55m,66m,55s', 'SSS', '5s', 'E', 'E'), SelfWindValueTilesYaku::getInstance(), false],
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
            [new YakuTestData('111m,999m,111s,EE', "999s"), AllTerminalsAndHonorsYaku::getInstance(), true],
            [new YakuTestData('111m,999m,111s,11p', "999s"), AllTerminalsAndHonorsYaku::getInstance(), true],
            // not all terminals
            [new YakuTestData('123m,999m,111s,EE', "999s"), AllTerminalsAndHonorsYaku::getInstance(), false],
            // not 4+1
            [new YakuTestData('11m,99m,11p,99p,11s,99s,EE'), AllTerminalsAndHonorsYaku::getInstance(), false],

            // test AllTriplesYaku
            [new YakuTestData('111m,999m,111s,EE', "999s"), AllTriplesYaku::getInstance(), true],
            [new YakuTestData('111m,999m,111s,EE', "9999s"), AllTriplesYaku::getInstance(), true],
            // not 4+1
            [new YakuTestData('111m,99m,11s,22s,EE', "999s"), AllTriplesYaku::getInstance(), false],
            // not all triples
            [new YakuTestData('123m,999m,111s,EE', "999s"), AllTriplesYaku::getInstance(), false],

            // DoubleReach is tested in fan1 with Reach

            // test FullStraightYaku
            [new YakuTestData('123m,456m,111s,EE', '789m'), FullStraightYaku::getInstance(), true],
            // not full straight
            [new YakuTestData('123m,456m,111s,EE', '789s'), FullStraightYaku::getInstance(), false],

            // test LittleThreeDragonsYaku
            [new YakuTestData('CCC,PPP,FF,11m,22m,33m'), LittleThreeDragonsYaku::getInstance(), true],
            [new YakuTestData('CCC,FF,11m,22m,33m', 'PPPP'), LittleThreeDragonsYaku::getInstance(), true],
            // not 2 pong + 1 pair
            [new YakuTestData('CCC,PPP,FFF,11m', '789m'), LittleThreeDragonsYaku::getInstance(), false],

            // test MixedOutsideHandYaku
            [new YakuTestData('123m,789m,123s,EE', '789s'), MixedOutsideHandYaku::getInstance(), true],
            [new YakuTestData('123m,789m,123s,11s', '789s'), MixedOutsideHandYaku::getInstance(), true],
            // not any run
            [new YakuTestData('111m,999m,111p,11s', '9999p'), MixedOutsideHandYaku::getInstance(), false],
            // not all outside
            [new YakuTestData('123m,789m,123s,11s', '678s'), MixedOutsideHandYaku::getInstance(), false],
            // not 4+1
            [new YakuTestData('11m,99m,11p,99p,11s,99s,EE'), MixedOutsideHandYaku::getInstance(), false],

            // test PureOutsideHandYaku
            [new YakuTestData('123m,789m,123s,11s', '789s'), PureOutsideHandYaku::getInstance(), true],
            // not any run
            [new YakuTestData('111m,999m,111p,11s', '999p'), PureOutsideHandYaku::getInstance(), false],
            // not all outside
            [new YakuTestData('123m,789m,123s,11s', '678s'), PureOutsideHandYaku::getInstance(), false],
            // not pure outside
            [new YakuTestData('123m,789m,123s,EE', '789s'), PureOutsideHandYaku::getInstance(), false],
            // not 4+1
            [new YakuTestData('11m,99m,11p,99p,11s,99s,11s'), PureOutsideHandYaku::getInstance(), false],

            // test SevenPairsYaku
            [new YakuTestData('11m,22m,33s,44m,55p,EE,CC'), SevenPairsYaku::getInstance(), true],
            // not different pairs
            [new YakuTestData('11m,22m,33s,44m,55p,EE,EE'), SevenPairsYaku::getInstance(), false],
            // not all pairs
            [new YakuTestData('11m,222m,333s,55p,EE,CC'), SevenPairsYaku::getInstance(), false],

            // test ThreeColorRunsYaku
            [new YakuTestData('234m,234p,EEE,NN', '234s'), ThreeColorRunsYaku::getInstance(), true],
            // not all three color
            [new YakuTestData('234m,234m,EEE,NN', '234s'), ThreeColorRunsYaku::getInstance(), false],

            // test ThreeColorTriplesYaku
            [new YakuTestData('333m,333s,NN', '333p,234s'), ThreeColorTriplesYaku::getInstance(), true],
            [new YakuTestData('333m,333s,NN', '3333p,234s'), ThreeColorTriplesYaku::getInstance(), true],
            [new YakuTestData('333m,333s,NN', '(333p),234s'), ThreeColorTriplesYaku::getInstance(), true],
            // not same number
            [new YakuTestData('333m,444s,NN', '3333p,234s'), ThreeColorTriplesYaku::getInstance(), false],
            // not different color(though not practical)
            [new YakuTestData('333m,333m,NN', '3333p,234s'), ThreeColorTriplesYaku::getInstance(), false],

            // test ThreeConcealedTriplesYaku
            [new YakuTestData('111m,222m,123s,EE', '(444m)'), ThreeConcealedTriplesYaku::getInstance(), true],
            [new YakuTestData('111m,222m,123s,EE', '(4444m)'), ThreeConcealedTriplesYaku::getInstance(), true],
            // not isConcealed
            [new YakuTestData('111m,222m,123s,EE', '444m'), ThreeConcealedTriplesYaku::getInstance(), false],
            // not three
            [new YakuTestData('111m,234m,456m,EE', '(444m)'), ThreeConcealedTriplesYaku::getInstance(), false],
            [new YakuTestData('111m,222m,333m,EE', '(444m)'), ThreeConcealedTriplesYaku::getInstance(), false],

            // test ThreeQuadsYaku
            [new YakuTestData('123s,44s', '1111m,2222m,3333m'), ThreeQuadsYaku::getInstance(), true],
            [new YakuTestData('123s,44s', '1111m,2222m,(3333m)'), ThreeQuadsYaku::getInstance(), true],
            // not three
            [new YakuTestData('123s,44s', '1111m,2222m,333m'), ThreeQuadsYaku::getInstance(), false],
            [new YakuTestData('44s', '1111m,2222m,3333m,4444m'), ThreeQuadsYaku::getInstance(), false],
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
            [new YakuTestData('123m,33m,44m,55m,EEE,SS'), HalfFlushYaku::getInstance(), true],
            // no honor
            [new YakuTestData('123m,33m,44m,55m,123m,11m'), HalfFlushYaku::getInstance(), false],
            // no suit types
            [new YakuTestData('EEE,SSS,WWW,NNN,CC'), HalfFlushYaku::getInstance(), false],
            // not same Suit types
            [new YakuTestData('123m,33m,44m,55m,123m,11s'), HalfFlushYaku::getInstance(), false],

            [new YakuTestData('123m,33m,44m,55m,123m,11m'), FullFlushYaku::getInstance(), true],
            // not all suit
            [new YakuTestData('123m,33m,44m,55m,EEE,SS'), FullFlushYaku::getInstance(), false],
            // no suit types
            [new YakuTestData('EEE,SSS,WWW,NNN,CC'), FullFlushYaku::getInstance(), false],
            // not same Suit types
            [new YakuTestData('123m,33m,44m,55m,123m,11s'), FullFlushYaku::getInstance(), false],

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
            [new YakuTestData('234s,234s,FF', '666s,888s'), AllGreenYaku::getInstance(), true],
            [new YakuTestData('222s,333s,444s,666s,88s'), AllGreenYaku::getInstance(), true],
            // not 41 tileSeries
            [new YakuTestData('22s,22s,33s,44s,66s,88s,FF'), AllGreenYaku::getInstance(), false],
            // not all green tiles
            [new YakuTestData('222s,333s,FF', '666s,111s'), AllGreenYaku::getInstance(), false],

            // test AllHonorsYaku
            [new YakuTestData('CC', 'EEE,SSS,WWW,NNN'), AllHonorsYaku::getInstance(), true],
            [new YakuTestData('EEE,SSS,WWW,NNN,CC'), AllHonorsYaku::getInstance(), true],
            [new YakuTestData('EE,SS,WW,NN,CC,PP,FF'), AllHonorsYaku::getInstance(), true],
            // not all honors
            [new YakuTestData('EEE,SSS,WWW,NNN,11s'), AllHonorsYaku::getInstance(), false],

            // test AllTerminalsYaku
            [new YakuTestData('111m,999m,11s', '111p,999p'), AllTerminalsYaku::getInstance(), true],
            // not 41 tileSeries
            [new YakuTestData('11m,11m,99m,11p,99p,11s,99s'), AllTerminalsYaku::getInstance(), false],
            // not all terminals
            [new YakuTestData('EEE,999m,11s', '111p,999p'), AllTerminalsYaku::getInstance(), false],

            // test BigFourWindsYaku
            [new YakuTestData('EEE,SSS,WWW,11s', 'NNN'), BigFourWindsYaku::getInstance(), true],
            [new YakuTestData('EEE,SSS,WWW,11s', 'NNNN'), BigFourWindsYaku::getInstance(), true],
            // not 4 winds
            [new YakuTestData('EEE,SSS,WW', 'NNN,123s'), BigFourWindsYaku::getInstance(), false],

            // test BigThreeDragonsYaku
            [new YakuTestData('CCC,PPP,123s,11s', 'FFF'), BigThreeDragonsYaku::getInstance(), true],
            [new YakuTestData('CCC,PPP,123s,11s', 'FFFF'), BigThreeDragonsYaku::getInstance(), true],
            [new YakuTestData('CCC,PPP,123s,11s', '(FFFF)'), BigThreeDragonsYaku::getInstance(), true],
            // not 3 tripleOrQuads
            [new YakuTestData('CC,PPP,123s,EEE', 'FFF'), BigThreeDragonsYaku::getInstance(), false],

            // test FourConcealedTriplesYaku
            [new YakuTestData('111s,222s,333s,444s,55s'), FourConcealedTriplesYaku::getInstance(), true],
            [new YakuTestData('111s,222s,333s,55s', '(444s)'), FourConcealedTriplesYaku::getInstance(), true],
            [new YakuTestData('111s,222s,333s,55s', '(4444s)'), FourConcealedTriplesYaku::getInstance(), true],
            // not 4 isConcealed
            [new YakuTestData('111s,222s,333s,55s', '444s'), FourConcealedTriplesYaku::getInstance(), false],
            // not 4
            [new YakuTestData('111s,222s,333s,456s,55s'), FourConcealedTriplesYaku::getInstance(), false],

            // test FourQuadsYaku
            [new YakuTestData('11s', '2222s,3333s,4444s,5555s'), FourQuadsYaku::getInstance(), true],
            [new YakuTestData('11s', '2222s,3333s,4444s,(5555s)'), FourQuadsYaku::getInstance(), true],
            // not 4 quads
            [new YakuTestData('11s', '2222s,3333s,4444s,555s'), FourQuadsYaku::getInstance(), false],

            // test SmallFourWindsYaku
            [new YakuTestData('EEE,SSS,WW', 'NNN,123s'), SmallFourWindsYaku::getInstance(), true],
            [new YakuTestData('EEE,SSS,WW', 'NNNN,123s'), SmallFourWindsYaku::getInstance(), true],
            // not 3+1 winds
            [new YakuTestData('EEE,SSS,WWW,11s', 'NNN'), SmallFourWindsYaku::getInstance(), false],
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
                // test FourConcealedTriplesYaku
                [new YakuTestData('111s,222s,333s,444s,55s', null, '5s'), FourConcealedTriplesOnePairWaitingYaku::getInstance(), true],
                [new YakuTestData('111s,222s,333s,55s', '(444s)', '5s'), FourConcealedTriplesOnePairWaitingYaku::getInstance(), true],
                [new YakuTestData('111s,222s,333s,55s', '(4444s)', '5s'), FourConcealedTriplesOnePairWaitingYaku::getInstance(), true],
                // not one pair waiting
                [new YakuTestData('111s,222s,333s,444s,55s', null, '4s'), FourConcealedTriplesOnePairWaitingYaku::getInstance(), false],
                [new YakuTestData('111s,222s,333s,55s', '(444s)', '4s'), FourConcealedTriplesOnePairWaitingYaku::getInstance(), false],
                [new YakuTestData('111s,222s,333s,55s', '(4444s)', '4s'), FourConcealedTriplesOnePairWaitingYaku::getInstance(), false],

                // not 4 isConcealed
                [new YakuTestData('111s,222s,333s,55s', '444s', '5s'), FourConcealedTriplesOnePairWaitingYaku::getInstance(), false],
                // not 4
                [new YakuTestData('111s,222s,333s,456s,55s', null, '5s'), FourConcealedTriplesOnePairWaitingYaku::getInstance(), false],
            ];
        }
    }

    // todo simplify testReach

    function testReach() {
        $r = new Round();

        // pass first round
        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('E'));
        $r->passPublicPhase();

        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('E'));
        $r->passPublicPhase();

        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('E'));
        $r->passPublicPhase();
        $this->assertEquals(RoundPhase::PRIVATE_PHASE, $r->getRoundPhase()->getValue());

        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('N'));
        $r->passPublicPhase();
        $this->assertEquals(RoundPhase::PRIVATE_PHASE, $r->getRoundPhase()->getValue(), $r->getRoundData()->getTurnManager()->getGlobalTurn());

        // E reach
        $r->debugReachByReplace($r->getCurrentPlayer(), Tile::fromString('E'), TileList::fromString('123456789s2355mE'));
        $r->passPublicPhase();

        // W discard, E may win
        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('1m'));

        $yakuList = $r->getWinResult($r->getPlayerList()[0])->getYakuList();

        $this->assertGreaterThan(0, $yakuList->count());
        $this->assertContains(ReachYaku::getInstance(), $yakuList, $yakuList);
    }

    function testDoubleReach() {
        $r = new Round();
        // E double reach
        $r->debugReachByReplace($r->getCurrentPlayer(), Tile::fromString('E'), TileList::fromString('123456789s2355mE'));
        $r->passPublicPhase();

        // W discard, E may win
        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('1m'));

        $yakuList = $r->getWinResult($r->getPlayerList()[0])->getYakuList();

        $this->assertGreaterThan(0, $yakuList->count());
        $this->assertNotContains(ReachYaku::getInstance(), $yakuList, $yakuList);
        $this->assertContains(DoubleReachYaku::getInstance(), $yakuList, $yakuList);
    }

    function testFirstTurnWin() {
        $r = new Round();

        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('E'));
        $r->passPublicPhase();

        // S double Reach
        $r->debugReachByReplace($r->getCurrentPlayer(), Tile::fromString('S'), TileList::fromString('123456789s2355mS'));
        $r->passPublicPhase();

        // pass
        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('E'));
        $r->passPublicPhase();

        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('E'));
        $r->passPublicPhase();

        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('E'));
        $r->getRoundData()->getTileAreas()->getWall()->debugSetNextDrawTile(Tile::fromString('1m'));
        $r->passPublicPhase();
        $this->assertEquals(Tile::fromString('1m'), $r->getRoundData()->getTileAreas()->getTargetTile());

        // S winBySelf FirstTurnWin
        $yakuList = $r->getWinResult($r->getCurrentPlayer())->getYakuList();
        $this->assertGreaterThan(0, $yakuList->count());
        $this->assertContains(FirstTurnWinYaku::getInstance(), $yakuList, $yakuList);
    }

    function testFinalTileWinMoon() {

    }

    function testFinalTileWinFish() {

    }
}

/**
 * Convenient adaptor for write yaku test cases.
 * tobe removed
 * goal
 * - YakuTestCase: RoundDebugSetData RoundDebugSkipToData targetPlayer yaku isExist getRound
 */
class YakuTestData {
    private static $round;

    private $handMeldList;
    private $declareMeldList;
    private $targetTile;

    private $currentPlayerWind;
    private $targetPlayerWind;

    private $roundDebugResetData;

    function __construct(string $handMeldListString, string $declareMeldListString = null, string $targetTileString = null,
                         string $currentPlayerWindString = null, string $targetPlayerWindString = null, RoundDebugResetData $roundDebugResetData = null) {
        $this->handMeldList = MeldList::fromString($handMeldListString)->toConcealed(true);
        $this->declareMeldList = MeldList::fromString($declareMeldListString !== null ? $declareMeldListString : "");
        $this->targetTile = $targetTileString !== null ? Tile::fromString($targetTileString) : $this->handMeldList[0][0];

        $this->currentPlayerWind = Tile::fromString($currentPlayerWindString ?? 'E');
        $this->targetPlayerWind = $targetPlayerWindString !== null ? Tile::fromString($targetPlayerWindString) : $this->currentPlayerWind;

        $this->roundDebugResetData = $roundDebugResetData ?? new RoundDebugResetData();
    }

    function __toString() {
        return sprintf('handMeldList[%s], declaredMeldList[%s], currentPlayerWind[%s], targetPlayerWind[%s]'
            , $this->handMeldList, $this->declareMeldList, $this->currentPlayerWind, $this->targetPlayerWind);
    }

    function toWinSubTarget() {
        if (!self::$round) {
            self::$round = new Round(); // for 10 test cases, 1.2s => 0.2s which is 6x faster
        }
        $round = self::$round;
        $round->getRoundData()->debugReset($this->roundDebugResetData);
        $round->init();

        // set phase
        $currentPlayer = $round->getPlayerList()->getSelfWindPlayer($this->currentPlayerWind);
        $targetPlayer = $round->getPlayerList()->getSelfWindPlayer($this->targetPlayerWind);
        $isPrivatePhase = ($currentPlayer === $targetPlayer);

        // set tiles
        $handMeldList = $this->handMeldList;
        $targetTile = $this->targetTile;
        $tileAreas = $round->getRoundData()->getTileAreas();

        $round->debugSkipTo($currentPlayer, RoundPhase::getPrivateOrPublicInstance($isPrivatePhase), null, null, $targetTile);
        if ($isPrivatePhase) {
            $handTileList = $handMeldList->toTileList();
            $tileAreas->debugSetPrivate($targetPlayer, $handTileList, $this->declareMeldList, $targetTile);
        } else {
            $handTileList = $handMeldList->toTileList()->removeByValue($targetTile);
            $tileAreas->debugSetPublic($targetPlayer, $handTileList, $this->declareMeldList);
        }

        return new WinSubTarget($this->handMeldList, $targetPlayer, $round->getRoundData());
    }
}