<?php

use Saki\Game\Meld\MeldList;
use Saki\Game\PrevailingStatus;
use Saki\Game\PrevailingWind;
use Saki\Game\SeatWind;
use Saki\Game\Tile\Tile;
use Saki\Win\WinState;
use Saki\Win\WinSubTarget;
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
use Saki\Win\Yaku\Yakuman2\PureNineGatesYaku;
use Saki\Win\Yaku\Yakuman2\PureThirteenOrphansYaku;

class YakuTest extends \SakiTestCase {
    function assertYakuList(string $actor, array $expectedContainYakus = null, int $expectedFan = null,
                            array $expectedNotContainYakus = null) {
        $round = $this->getCurrentRound();
        $winReport = $round->getWinReport(SeatWind::fromString($actor));
        $yakuItemList = $winReport->getYakuItemList();
        $yakus = $yakuItemList->toYakuList()->toArray();

        if ($expectedContainYakus !== null) {
            foreach ($expectedContainYakus as $expectedContainYaku) {
                $this->assertContains($expectedContainYaku, $yakus);
            }
        }

        if ($expectedFan !== null) {
            $this->assertEquals($expectedFan, $yakuItemList->getTotalFan());
        }

        if ($expectedNotContainYakus !== null) {
            foreach ($expectedNotContainYakus as $expectedNotContainYaku) {
                $this->assertNotContains($expectedNotContainYaku, $yakus);
            }
        }
    }

    function assertYakuListEmpty(string $actor) {
        $this->assertYakuList($actor, null, 0);
    }

    /**
     * @dataProvider yakuProvider
     * @param Yaku $yaku
     * @param bool $expectedExist
     * @param string $handMeldListString
     * @param string $meldedString
     * @param string $targetTileString
     * @param string $currentSeatWindString
     * @param string $actorString
     * @param string $prevailingWindString
     */
    function testYaku(Yaku $yaku, bool $expectedExist,
                      string $handMeldListString, string $meldedString = null, string $targetTileString = null,
                      string $currentSeatWindString = null, string $actorString = null,
                      string $prevailingWindString = null
    ) {
        // prepare params
        $handMeldList = MeldList::fromString($handMeldListString)->toConcealed(true);
        $melded = MeldList::fromString($meldedString !== null ? $meldedString : "");
        $targetTile = $targetTileString !== null ? Tile::fromString($targetTileString) : $handMeldList[0][0];

        $currentSeatWind = SeatWind::fromString($currentSeatWindString ?? 'E');
        $actorSeatWind = ($actorString !== null ? SeatWind::fromString($actorString) : $currentSeatWind);
        $isPrivate = ($currentSeatWind == $actorSeatWind);

        $prevailingStatus = new PrevailingStatus(PrevailingWind::fromString($prevailingWindString ?? 'E'), 1, 0);

        // init round, set phase
        $round = $this->getInitRound($prevailingStatus);
        $skipToCommand = sprintf('skipTo %s %s', $currentSeatWind, $isPrivate ? 'true' : 'false');
        $round->process($skipToCommand);

        // set hand
        $area = $round->getArea($actorSeatWind);
        $public = $handMeldList->toTileList()->remove($targetTile);
        $round->getTargetHolder()->replaceTargetTile($targetTile);
        $hand = $area->getHand()->toHand($public, $melded);
        $area->setHand($hand);

        // assert
        $winSubTarget = new WinSubTarget($round, $actorSeatWind, $handMeldList);
        $this->assertBool($expectedExist, $yaku->existIn($winSubTarget));
    }

    function yakuProvider() {
        return [
            // AfterAKongWinYaku is tested alone

            // test AllSimplesYaku
            [AllSimplesYaku::create(), true, '234m,456m,888s,55s', '678m'],
            // not without term
            [AllSimplesYaku::create(), false, '234m,456m,888s,55s', '789m'],
            // not without honour
            [AllSimplesYaku::create(), false, '234m,456m,888s,EE', '789m'],

            // BottomOfTheSeaFishYaku is tested alone
            // BottomOfTheSeaMoonYaku is tested alone
            // DoraYaku is tested alone

            // test DragonPungRedYaku
            [DragonPungRedYaku::create(), true, '123m,44m,55m,66m,55s', 'CCC', '5s'],
            [DragonPungRedYaku::create(), false, '123m,44m,55m,66m,55s', '111s', '5s'],

            // test DragonPungWhiteYaku
            [DragonPungWhiteYaku::create(), true, '123m,44m,55m,66m,55s', 'PPP', '5s'],
            [DragonPungWhiteYaku::create(), false, '123m,44m,55m,66m,55s', '111s', '5s'],

            // test DragonPungGreenYaku
            [DragonPungGreenYaku::create(), true, '123m,44m,55m,66m,55s', 'FFF', '5s'],
            [DragonPungGreenYaku::create(), false, '123m,44m,55m,66m,55s', '111s', '5s'],

            // FirstTurnWinYaku is tested alone

            // test FullyConcealedHandYaku
            [FullyConcealedHandYaku::create(), true, '123m,456m,77m,88m,11s,55s', null, '1s'],
            // not isConcealed
            [FullyConcealedHandYaku::create(), false, '123m,77m,88m,11s,55s', '333m', '1s'],
            // not selfDraw
            [FullyConcealedHandYaku::create(), false, '123m,456m,77m,88m,11s,55s', null, '1s', 'E', 'W'],

            // test PinfuYaku
            [PinfuYaku::create(), true, '123m,456m,789m,123s,55s', null, '1s'],
            // not isConcealed
            [PinfuYaku::create(), false, '123m,456m,123s,55s', '789m', '1s'],
            // not 4 chow
            [PinfuYaku::create(), false, '123m,456m,999m,123s,55s', null, '1s'],
            // not suit pair
            [PinfuYaku::create(), false, '123m,456m,789m,123s,EE', null, '1s'],
            // not two-pair waiting
            [PinfuYaku::create(), false, '123m,456m,789m,123s,55s', null, '2s'],

            // test PrevailingWindYaku
            [PrevailingWindYaku::create(), true, '123m,44m,55m,66m,55s', 'EEE', '5s'],
            // not prevailingWind
            [PrevailingWindYaku::create(), false, '123m,44m,55m,66m,55s', 'EEE', '5s', null, null, PrevailingWind::fromString('S')],

            // test PureDoubleChowYaku
            [PureDoubleChowYaku::create(), true, '123m,123m,77m,88m,11s,EE', null, 'E'],
            // not isConcealed
            [PureDoubleChowYaku::create(), false, '123m,123m,EE', '123s,123s', 'E'],

            // test TwicePureDoubleChowYaku
            [TwicePureDoubleChowYaku::create(), true, '123m,123m,123s,123s,EE', null, 'E'],
            // not non-duplicate
            [TwicePureDoubleChowYaku::create(), false, '123m,123m,123m,123m,EE', null, 'E'],
            // not isConcealed
            [TwicePureDoubleChowYaku::create(), false, '123m,123m,EE', '123s,123s', 'E'],

            // RedDoraYaku is tested alone
            // RiichiYaku is tested alone
            // RobbingAKongYaku is tested alone

            // test SeatWindYaku
            [SeatWindYaku::create(), true, '123m,44m,55m,66m,55s', 'SSS', '5s', 'E', 'S'],
            // not seatWind
            [SeatWindYaku::create(), false, '123m,44m,55m,66m,55s', 'SSS', '5s', 'E', 'E'],

            // UraDoraYaku is tested alone

            // test AllPungsYaku
            [AllPungsYaku::create(), true, '111m,999m,111s,EE', "999s"],
            [AllPungsYaku::create(), true, '111m,999m,111s,EE', "9999s"],
            // not 4+1
            [AllPungsYaku::create(), false, '111m,99m,11s,22s,EE', "999s"],
            // not all triples
            [AllPungsYaku::create(), false, '123m,999m,111s,EE', "999s"],

            // test AllTerminalsAndHonoursYaku
            [AllTerminalsAndHonoursYaku::create(), true, '111m,999m,111s,EE', "999s"],
            [AllTerminalsAndHonoursYaku::create(), true, '111m,999m,111s,11p', "999s"],
            // not all terminals
            [AllTerminalsAndHonoursYaku::create(), false, '123m,999m,111s,EE', "999s"],
            // not 4+1
            [AllTerminalsAndHonoursYaku::create(), false, '11m,99m,11p,99p,11s,99s,EE'],

            // DoubleRiichi is tested alone

            // test LittleThreeDragonsYaku
            [LittleThreeDragonsYaku::create(), true, 'CCC,PPP,FF,11m,22m,33m'],
            [LittleThreeDragonsYaku::create(), true, 'CCC,FF,11m,22m,33m', 'PPPP'],
            // not 2 pung + 1 pair
            [LittleThreeDragonsYaku::create(), false, 'CCC,PPP,FFF,11m', '789m'],

            // test MixedTripleChowYaku
            [MixedTripleChowYaku::create(), true, '234m,234p,EEE,NN', '234s'],
            // not all three color
            [MixedTripleChowYaku::create(), false, '234m,234m,EEE,NN', '234s'],

            // test OutsideHandYaku
            [OutsideHandYaku::create(), true, '123m,789m,123s,EE', '789s'],
            [OutsideHandYaku::create(), true, '123m,789m,123s,11s', '789s'],
            // not any chow
            [OutsideHandYaku::create(), false, '111m,999m,111p,11s', '9999p'],
            // not all outside
            [OutsideHandYaku::create(), false, '123m,789m,123s,11s', '678s'],
            // not 4+1
            [OutsideHandYaku::create(), false, '11m,99m,11p,99p,11s,99s,EE'],

            // test TerminalsInAllSetsYaku
            [TerminalsInAllSetsYaku::create(), true, '123m,789m,123s,11s', '789s'],
            // not any chow
            [TerminalsInAllSetsYaku::create(), false, '111m,999m,111p,11s', '999p'],
            // not all outside
            [TerminalsInAllSetsYaku::create(), false, '123m,789m,123s,11s', '678s'],
            // not pure outside
            [TerminalsInAllSetsYaku::create(), false, '123m,789m,123s,EE', '789s'],
            // not 4+1
            [TerminalsInAllSetsYaku::create(), false, '11m,99m,11p,99p,11s,99s,11s'],

            // test PureStraightYaku
            [PureStraightYaku::create(), true, '123m,456m,111s,EE', '789m'],
            // not full straight
            [PureStraightYaku::create(), false, '123m,456m,111s,EE', '789s'],

            // test SevenPairsYaku
            [SevenPairsYaku::create(), true, '11m,22m,33s,44m,55p,EE,CC'],
            // not different pairs
            [SevenPairsYaku::create(), false, '11m,22m,33s,44m,55p,EE,EE'],
            // not all pairs
            [SevenPairsYaku::create(), false, '11m,222m,333s,55p,EE,CC'],

            // test ThreeConcealedPungsYaku
            [ThreeConcealedPungsYaku::create(), true, '111m,222m,444m,123s,EE'],
            [ThreeConcealedPungsYaku::create(), true, '111m,222m,123s,EE', '(4444m)'],
            // not isConcealed
            [ThreeConcealedPungsYaku::create(), false, '111m,222m,123s,EE', '444m'],
            // not three
            [ThreeConcealedPungsYaku::create(), false, '111m,234m,456m,444m,EE'],
            [ThreeConcealedPungsYaku::create(), false, '111m,222m,333m,444m,EE'],

            // test ThreeKongsYaku
            [ThreeKongsYaku::create(), true, '123s,44s', '1111m,2222m,3333m'],
            [ThreeKongsYaku::create(), true, '123s,44s', '1111m,2222m,(3333m)'],
            // not three
            [ThreeKongsYaku::create(), false, '123s,44s', '1111m,2222m,333m'],
            [ThreeKongsYaku::create(), false, '44s', '1111m,2222m,3333m,4444m'],

            // test TriplePungYaku
            [TriplePungYaku::create(), true, '333m,333s,NN', '333p,234s'],
            [TriplePungYaku::create(), true, '333m,333s,NN', '3333p,234s'],
            [TriplePungYaku::create(), true, '333m,333s,NN', '(3333p),234s'],
            // not same number
            [TriplePungYaku::create(), false, '333m,444s,NN', '3333p,234s'],
            // not same number, with honour
            [TriplePungYaku::create(), false, '333m,444s,NN', '3333p,CCCC'],
            // not different color(though not practical)
            [TriplePungYaku::create(), false, '333m,333m,NN', '3333p,234s'],

            // test HalfFlushYaku
            [HalfFlushYaku::create(), true, '123m,33m,44m,55m,EEE,SS'],
            // no honour
            [HalfFlushYaku::create(), false, '123m,33m,44m,55m,123m,11m'],
            // no suit types
            [HalfFlushYaku::create(), false, 'EEE,SSS,WWW,NNN,CC'],
            // not same Suit types
            [HalfFlushYaku::create(), false, '123m,33m,44m,55m,123m,11s'],

            // test FullFlushYaku
            [FullFlushYaku::create(), true, '123m,33m,44m,55m,123m,11m'],
            // not all suit
            [FullFlushYaku::create(), false, '123m,33m,44m,55m,EEE,SS'],
            // no suit types
            [FullFlushYaku::create(), false, 'EEE,SSS,WWW,NNN,CC'],
            // not same Suit types
            [FullFlushYaku::create(), false, '123m,33m,44m,55m,123m,11s'],

            // TerminalsInAllSetsYaku is tested following OutsideHandYaku
            // TwicePureDoubleChowYaku is tested following PureDoubleChowYaku

            // FullFlushYaku is tested following HalfFlushYaku

            // test AllGreenYaku
            [AllGreenYaku::create(), true, '234s,234s,FF', '666s,888s'],
            [AllGreenYaku::create(), true, '222s,333s,444s,666s,88s'],
            // not 41 series
            [AllGreenYaku::create(), false, '22s,22s,33s,44s,66s,88s,FF'],
            // not all green tiles
            [AllGreenYaku::create(), false, '222s,333s,FF', '666s,111s'],

            // test AllHonoursYaku
            [AllHonoursYaku::create(), true, 'CC', 'EEE,SSS,WWW,NNN'],
            [AllHonoursYaku::create(), true, 'EEE,SSS,WWW,NNN,CC'],
            [AllHonoursYaku::create(), true, 'EE,SS,WW,NN,CC,PP,FF'],
            // not all honours
            [AllHonoursYaku::create(), false, 'EEE,SSS,WWW,NNN,11s'],

            // test AllTerminalsYaku
            [AllTerminalsYaku::create(), true, '111m,999m,11s', '111p,999p'],
            // not 41 series
            [AllTerminalsYaku::create(), false, '11m,11m,99m,11p,99p,11s,99s'],
            // not all terminals
            [AllTerminalsYaku::create(), false, 'EEE,999m,11s', '111p,999p'],

            // test BigFourWindsYaku
            [BigFourWindsYaku::create(), true, 'EEE,SSS,WWW,11s', 'NNN'],
            [BigFourWindsYaku::create(), true, 'EEE,SSS,WWW,11s', 'NNNN'],
            // not 4 winds
            [BigFourWindsYaku::create(), false, 'EEE,SSS,WW', 'NNN,123s'],

            // test BigThreeDragonsYaku
            [BigThreeDragonsYaku::create(), true, 'CCC,PPP,123s,11s', 'FFF'],
            [BigThreeDragonsYaku::create(), true, 'CCC,PPP,123s,11s', 'FFFF'],
            [BigThreeDragonsYaku::create(), true, 'CCC,PPP,123s,11s', '(FFFF)'],
            // not 3 tripleOrKongs
            [BigThreeDragonsYaku::create(), false, 'CC,PPP,123s,EEE', 'FFF'],

            // BlessingOfEarthYaku is tested alone
            // BlessingOfHeavenYaku is tested alone
            // BlessingOfManYaku is tested alone

            // test FourConcealedPungsYaku
            [FourConcealedPungsYaku::create(), true, '111s,222s,333s,444s,55s'],
            [FourConcealedPungsYaku::create(), true, '111s,222s,333s,55s', '(4444s)'],
            // not 4 isConcealed
            [FourConcealedPungsYaku::create(), false, '111s,222s,333s,55s', '444s'],
            // not 4
            [FourConcealedPungsYaku::create(), false, '111s,222s,333s,456s,55s'],

            // test FourKongsYaku
            [FourKongsYaku::create(), true, '11s', '2222s,3333s,4444s,5555s'],
            [FourKongsYaku::create(), true, '11s', '2222s,3333s,4444s,(5555s)'],
            // not 4 kongs
            [FourKongsYaku::create(), false, '11s', '2222s,3333s,4444s,555s'],

            // test PureFourConcealedPungsYaku
            [PureFourConcealedPungsYaku::create(), true, '111s,222s,333s,444s,55s', null, '5s'],
            [PureFourConcealedPungsYaku::create(), true, '111s,222s,333s,55s', '(4444s)', '5s'],
            // not one pair waiting
            [PureFourConcealedPungsYaku::create(), false, '111s,222s,333s,444s,55s', null, '1s'],
            [PureFourConcealedPungsYaku::create(), false, '111s,222s,333s,55s', '(4444s)', '1s'],
            // not 4 isConcealed
            [PureFourConcealedPungsYaku::create(), false, '111s,222s,333s,55s', '444s', '5s'],
            // not 4
            [PureFourConcealedPungsYaku::create(), false, '111s,222s,333s,456s,55s', null, '5s'],

            // test LittleFourWindsYaku
            [LittleFourWindsYaku::create(), true, 'EEE,SSS,WW', 'NNN,123s'],
            [LittleFourWindsYaku::create(), true, 'EEE,SSS,WW', 'NNNN,123s'],
            // not 3+1 winds
            [LittleFourWindsYaku::create(), false, 'EEE,SSS,WWW,11s', 'NNN'],

            // test NineGatesYaku
            [NineGatesYaku::create(), true, '111s,123s,456s,789s,99s', null, '1s'],
            [NineGatesYaku::create(), true, '111s,22s,345s,678s,999s', null, '2s'],
            [NineGatesYaku::create(), true, '111s,22s,345s,678s,999s', null, '3s'],
            // not concealed
            [NineGatesYaku::create(), false, '111s,22s,345s,678s', '999s'],
            // not all suit
            [NineGatesYaku::create(), false, '111s,22s,345s,678s,EEE'],
            // not same color suit
            [NineGatesYaku::create(), false, '111s,22s,345s,678s,999m'],

            // test PureNineGatesYaku
            [PureNineGatesYaku::create(), true, '111s,123s,456s,789s,99s', null, '1s'],
            [PureNineGatesYaku::create(), true, '111s,22s,345s,678s,999s', null, '2s'],
            // not pure
            [PureNineGatesYaku::create(), false, '111s,22s,345s,678s,999s', null, '3s'],
            // not concealed
            [PureNineGatesYaku::create(), false, '111s,22s,345s,678s', '999s'],
            // not all suit
            [PureNineGatesYaku::create(), false, '111s,22s,345s,678s,EEE'],
            // not same color suit
            [PureNineGatesYaku::create(), false, '111s,22s,345s,678s,999m'],

            // test ThirteenOrphansYaku
            [ThirteenOrphansYaku::create(), true, '119m19p19sESWNCPF', null, '1m'],
            [ThirteenOrphansYaku::create(), true, '119m19p19sESWNCPF', null, '9m'],

            // test PureThirteenOrphansYaku
            [PureThirteenOrphansYaku::create(), true, '119m19p19sESWNCPF', null, '1m'],
            [PureThirteenOrphansYaku::create(), false, '119m19p19sESWNCPF', null, '9m'],
        ];
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

    function testRobbingAKong() {
        $round = $this->getInitRound();
        $round->process(
            'skip 4; mockHand W 23m123456789s11p',
            'mockHand E 1m; discard E 1m',
            'mockHand S 11m; pung S 1m1m; mockHand S 1m; extendKong S 1m 111m'
        );
        $this->assertHand(null, null, '1m', 'W');
        $this->assertYakuList('W', [RobbingAKongYaku::create()]);
    }

    function testBottomOfTheSeaMoon() {
        $round = $this->getInitRound();
        $round->process(
            'skip 4; mockWallRemain 0; mockHand E 123456789m12355s'
        );
        $this->assertYakuList('E', [BottomOfTheSeaMoonYaku::create()]);
    }

    function testBottomOfTheSeaFish() {
        $round = $this->getInitRound();
        $round->process(
            'skip 4; mockWallRemain 0; mockHand E 5s; discard E 5s; mockHand S 123456789m1235s'
        );
        $this->assertYakuList('S', [BottomOfTheSeaFishYaku::create()]);
    }

    function testDora() {
        $round = $this->getInitRound();

        // dora not counted without other yakus
        $round->process('skip 4; mockIndicatorWall 1919293949s 5 false; mockHand E 222789s789m12345m');
        $this->assertYakuListEmpty('E');

        // dora counted with other yakus
        $round->process('mockHand E 222789s789m12355m');
        $this->assertYakuList('E', [DoraYaku::create()], 1 + 6); // selfDraw + 6 dora
    }

    function testUraDora() {
        $round = $this->getInitRound();

        // uraDora not counted without other yakus
        $round->process('skip 4; mockIndicatorWall 9191929394s 5 true; mockHand E 222789s789m12345m');
        $this->assertYakuListEmpty('E');

        // uraDora counted with other yakus
        $round->process('mockHand E 222789s789m12355m');
        $this->assertYakuList('E', [UraDoraYaku::create()], 1 + 6); // selfDraw + 6 uraDora
    }

    function testRedDora() {
        $round = $this->getInitRound();

        // redDora not counted without other yakus
        $round->process('skip 4; mockIndicatorWall 9999999999s 1 false; mockHand E 222789s789m12340m');
        $this->assertYakuListEmpty('E');

        // redDora counted with other yakus
        $round->process('mockHand E 222789s789m12300m');
        $this->assertYakuList('E', [RedDoraYaku::create()], 3); // selfDraw + 2 redDora
    }

    function testBlessingOfHeaven() {
        // passed
        $round = $this->getInitRound();
        $round->process('mockHand E 123456789m12355s');
        $this->assertYakuList('E', [BlessingOfHeavenYaku::create()]);

        // failed if declared
        $round = $this->getInitRound();
        $round->process('mockHand E 1111m; concealedKong E 1111m; mockHand E 123456789m55s');
        $this->assertYakuList('E', null, null, [BlessingOfHeavenYaku::create()]);

        // failed if not first turn
        $round = $this->getInitRound();
        $round->process('skip 4; mockHand E 123456789m12355s');
        $this->assertYakuList('E', null, null, [BlessingOfHeavenYaku::create()]);

        // failed if not dealer
        $round = $this->getInitRound();
        $round->process('skip 1; mockHand S 123456789m12355s');
        $this->assertYakuList('S', null, null, [BlessingOfHeavenYaku::create()]);
    }

    function testBlessingOfEarth() {
        // passed
        $round = $this->getInitRound();
        $round->process('skip 1; mockHand S 123456789m12355s');
        $this->assertYakuList('S', [BlessingOfEarthYaku::create()]);

        // failed if declared
        $round = $this->getInitRound();
        $round->process('mockHand E 1111m; concealedKong E 1111m; skip 1; mockHand S 123456789m12355s');
        $this->assertYakuList('S', null, null, [BlessingOfEarthYaku::create()]);

        // failed if not first turn
        $round = $this->getInitRound();
        $round->process('skip 1; skip 4; mockHand S 123456789m12355s');
        $this->assertYakuList('S', null, null, [BlessingOfEarthYaku::create()]);

        // failed if not leisure
        $round = $this->getInitRound();
        $round->process('mockHand E 123456789m12355s');
        $this->assertYakuList('E', null, null, [BlessingOfEarthYaku::create()]);
    }

    function testBlessingOfMan() {
        // passed
        $round = $this->getInitRound();
        $round->process('mockHand E 5s; discard E 5s; mockHand S 123456789m1235s');
        $this->assertYakuList('S', [BlessingOfManYaku::create()]);

        // failed if declared
        $round = $this->getInitRound();
        $round->process('mockHand E 11115s; concealedKong E 1111s; discard E 5s; mockHand S 123456789m1235s');
        $this->assertYakuList('S', null, null, [BlessingOfManYaku::create()]);

        // failed if not first turn
        $round = $this->getInitRound();
        $round->process('skip 4; mockHand E 5s; discard E 5s; mockHand S 123456789m1235s');
        $this->assertYakuList('S', null, null, [BlessingOfManYaku::create()]);

        // failed if not before self turn
        $round = $this->getInitRound();
        $round->process('skip 2; mockHand W 5s; discard W 5s; mockHand S 123456789m1235s');
        $this->assertYakuList('S', null, null, [BlessingOfManYaku::create()]);
    }

    function testThirteenOrphanTsumo() {
        $round = $this->getInitRound();
        $round->process('mockHand E 119m19p19sESWNCPF');

        $winReport = $round->getWinReport(SeatWind::createEast());
        $this->assertEquals(WinState::create(WinState::WIN_BY_SELF), $winReport->getWinState());
    }
}