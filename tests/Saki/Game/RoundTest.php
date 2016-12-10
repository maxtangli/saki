<?php

use Saki\Game\Phase;
use Saki\Game\SeatWind;
use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Win\Result\ResultType;

class RoundTest extends \SakiTestCase {
    //region Core
    function testGetHand() {
        $round = $this->getInitRound();

        $round->process('mockHand E 123456789m12344p');
        $areaE = $round->getArea(SeatWind::createEast());
        $areaS = $round->getArea(SeatWind::createSouth());

        // E private phase, hand E
        $handE = $areaE->getHand();
        $this->assertEquals('123456789m12344p', $handE->getPrivate()->toSortedString(true));
        $this->assertEquals('4p', $handE->getTarget()->getTile()->__toString());
        $this->assertEquals('123456789m1234p', $handE->getPublic()->toSortedString(true));

        // E private phase, hand S
        $round->process('mockHand S 123456789p1234s');
        $handS = $areaS->getHand();
        // no private
        $this->assertFalse($handS->getTarget()->exist());
        $this->assertEquals('123456789p1234s', $handS->getPublic()->toSortedString(true));

        // E public phase, hand E
        $round->process('discard E 2m');
        $handE = $areaE->getHand();
        // no private
        $this->assertFalse($handE->getTarget()->exist());
        $this->assertEquals('13456789m12344p', $handE->getPublic()->toSortedString(true));

        // E public phase, hand S
        $handS = $areaS->getHand();
        $this->assertEquals('2m123456789p1234s', $handS->getPrivate()->toSortedString(true));
        $this->assertEquals('2m', $handS->getTarget()->getTile()->toFormatString(true));
        $this->assertEquals('123456789p1234s', $handS->getPublic()->toSortedString(true));
    }

    function testNew() {
        // todo
    }

    function testRoll() {
        // todo
    }

    function testGameOver() {
        // to E Round N Dealer
        $round = $this->getInitRound();
        $round->roll(false);
        $round->roll(false);
        $round->roll(false);

        $area = $round->getCurrentArea();
        $pointHolder = $round->getPointHolder();

        // E Player tsumo, but point not over 30000
        $area->setHand(
            $area->getHand()->toHand(TileList::fromString('13m456m789m123s55s'), null, Tile::fromString('2m'))
        );
        $round->process('tsumo E');
        $pointHolder->setPoint(SeatWind::fromString('E'), 25000);
        $this->assertFalse($round->getPhaseState()->isGameOver($round));

        // point over 30000
        $pointHolder->setPoint(SeatWind::fromString('E'), 29999);
        $this->assertFalse($round->getPhaseState()->isGameOver($round));

        $pointHolder->setPoint(SeatWind::fromString('E'), 30000);
        $this->assertTrue($round->getPhaseState()->isGameOver($round));
    }
    //endregion

    //region Command
    function testDiscard() {
        $round = $this->getInitRound();
        $round->process('mockHand E 0p; discard E 0p');
        $this->assertLastOpen('0p');
    }

    function testDiscardWhenReach() {
        $round = $this->getInitRound();
        $round->process(
            'mockHand E 123456789p12347s; riichi E 7s; skip 4;',
            'mockHand E 123456789p12348s'
        );
        $this->assertExecutable('discard E 8s');
        $this->assertNotExecutable('discard E 4s');
    }

    function testDiscardSwapCalling() {
        $round = $this->getInitRound();
        $round->process(
            'mockHand E 2s; discard E 2s',
            'mockHand S 234506s; chow S 34s'
        );
        // swap calling
        $this->assertNotExecutable('discard S 2s');
        $this->assertNotExecutable('discard S 5s');
        $this->assertNotExecutable('discard S 0s');
        // continue
        $this->assertExecutable('discard S 6s');
    }

    function testReach() {
        // todo
    }

    function testChow() {
        $round = $this->getInitRound();

        $claimTurn = $round->getTurn();
        $round->process(
            'mockHand E 3m; discard E 3m',
            'mockHand S 450m12346789p13s; chow S 4m0m'
        );

        $this->assertPrivate('S');
        $this->assertHasClaim($claimTurn);
        $this->assertCurrentTurnChanged('S', $claimTurn);
        $this->assertHand('5m12346789p13s', '340m', '3s');

        $this->assertExecutable('discard S 5m');
    }

    function testChowRequireNext() {
        $round = $this->getInitRound();
        $round->process(
            'skip 3; mockHand N 3m; discard N 3m',
            'mockHand E 450m12346789p13s; mockHand S 450m12346789p13s; mockHand W 450m12346789p13s'
        );
        $this->assertExecutable('chow E 45m');
        $this->assertNotExecutable('chow S 45m');
        $this->assertNotExecutable('chow W 45m');
    }

    function testChowSwapCalling() {
        $round = $this->getInitRound();
        $round->process(
            'mockHand E 1111s; concealedKong E 1111s',
            'mockHand E 1111s; concealedKong E 1111s',
            'mockHand E 1111s; concealedKong E 1111s',
            'mockHand E 1234sC; discard E C; skip 3',
            'mockHand N 1s; discard N 1s'
        );
        $this->assertNotExecutable('chow E 23s');
    }

    function testPung() {
        $round = $this->getInitRound();

        $claimTurn = $round->getTurn();
        $round->process(
            'mockHand E 5m; discard E 5m',
            'mockHand W 550m12346789p13s; pung W 5m0m'
        );

        $this->assertPrivate('W');
        $this->assertHasClaim($claimTurn);
        $this->assertCurrentTurnChanged('W', $claimTurn);
        $this->assertHand('12346789p13s5m', '550m', '5m'); // todo should not be 5m but 3s

        $this->assertExecutable('discard W 5m');
    }

    function testKong() {
        $round = $this->getInitRound();

        $claimTurn = $round->getTurn();
        $round->process(
            'mockHand E 5m; discard E 5m',
            'mockNextReplace 1p; mockHand W 550m23456789p13s; kong W 5m5m0m'
        );

        $this->assertPrivate('W');
        $this->assertHasClaim($claimTurn);
        $this->assertCurrentTurnChanged('W', $claimTurn);
        $this->assertHand('23456789p13s1p', '5550m', '1p');

        $this->assertExecutable('discard W 5p');
    }

    function testConcealedKong() {
        $round = $this->getInitRound();

        $claimTurn = $round->getTurn();
        $round->process('mockNextReplace 1p; mockHand E 5550m23456789p13s; concealedKong E 5m5m5m0m');

        $this->assertPrivate('E');
        $this->assertHasClaim($claimTurn);
        $this->assertCurrentTurnNotChanged($claimTurn);
        $this->assertHand('23456789p13s1p', '(5550m)', '1p');

        $this->assertExecutable('discard E 5p');
    }

    function testExtendKong() {
        $round = $this->getInitRound();

        $round->process(
            'mockHand E 5m; discard E 5m',
            'mockHand S 005m23456789p13s; mockNextReplace 1p; pung S 0m5m'
        );

        // enter robQuad phase
        $claimTurn = $round->getTurn();
        $round->process('extendKong S 0m 055m');
        $this->assertPublic();
        $this->assertFalse($round->getPhaseState()->allowClaim());
        $this->assertHasNotClaim($claimTurn);
        $this->assertCurrentTurnNotChanged($claimTurn);
        $this->assertHand(null, null, '0m', 'E');

        // leave robQuad phase and enter private phase
        $round->process('passAll');
        $this->assertPrivate('S');
        $this->assertHasClaim($claimTurn);
        $this->assertCurrentTurnNotChanged($claimTurn);
        $this->assertHand('23456789p13s1p', '5500m', '1p'); // todo right order of Meld?

        $this->assertExecutable('discard S 5p');
    }

    // todo test not kong able

    function testTsumo() {
        $round = $this->getInitRound();

        // test over phase
        $round->process('mockHand E 123m456m789m123s55s; tsumo E');
        $this->assertOver();
        $this->assertCount(1, $round->getWall()->getIndicatorWall()->getUraIndicatorList());

        // test toNextRound
        $round->toNextRound();
        $this->assertPrivate();
        $this->assertEquals(SeatWind::createEast(), $round->getDealerArea()->getInitialSeatWind());
    }

    function testRon() {
        $this->getInitRound()->process(
            'mockHand E 4s; discard E 4s',
            'mockHand S 123m456m789m23s55s; ron S'
        );
        $this->assertOver(ResultType::WIN_BY_OTHER);
    }

    function testMultiRon() {
        // todo
    }
    //endregion

    //region Draw
    function testNotFourKongDrawBySamePlayer() {
        $round = $this->getInitRound();
        $round->process(
            'mockHand E 1111s; concealedKong E 1s1s1s1s',
            'mockHand E 1111s; concealedKong E 1s1s1s1s',
            'mockHand E 1111s; concealedKong E 1s1s1s1s',
            'mockHand E 1111s; concealedKong E 1s1s1s1s',
            'mockHand E 1s; discard E 1s; passAll'
        );
        $this->assertPrivate('S');
    }

    function testFourKongDrawByConcealedKong() {
        $round = $this->getInitRound();
        $round->process(
            'mockHand E 1111s1m; concealedKong E 1s1s1s1s; discard E 1m; passAll',
            'mockHand S 1111s1m; concealedKong S 1s1s1s1s; discard S 1m; passAll',
            'mockHand W 1111s1m; concealedKong W 1s1s1s1s; discard W 1m; passAll',
            'mockHand N 1111s1m; concealedKong N 1s1s1s1s; discard N 1m; passAll'
        );
        $this->assertOver(ResultType::FOUR_KONG_DRAW);
    }

    function testFourKongDrawByExtendKong() {
        $round = $this->getInitRound();
        $round->process(
            'mockHand E 1m; discard E 1m; passAll',
            'mockHand S 1111s1m; concealedKong S 1s1s1s1s; discard S 1m; passAll',
            'mockHand W 1111s1m; concealedKong W 1s1s1s1s; discard W 1m; passAll',
            'mockHand N 1111s1m; concealedKong N 1s1s1s1s; discard N 1m',
            'mockHand E 111m1p; pung E 1m1m; extendKong E 1m 111m; passAll; discard E 1p; passAll'
        );
        $this->assertOver(ResultType::FOUR_KONG_DRAW);
    }

    function testFourKongDrawByKong() {
        $round = $this->getInitRound();
        $round->process(
            'mockHand E 1111s1m; concealedKong E 1s1s1s1s; discard E 1m; passAll',
            'mockHand S 1111s1m; concealedKong S 1s1s1s1s; discard S 1m; passAll',
            'mockHand W 1111s1m; concealedKong W 1s1s1s1s; discard W 1m',
            'mockHand E 1111m; kong E 1m1m1m'
        );
        $this->assertPrivate();

        $round->process('discard E 1m; passAll');
        $this->assertOver(ResultType::FOUR_KONG_DRAW);
    }

    function testNineNineDraw() {
        $round = $this->getInitRound();

        $validTileList = TileList::fromString('19m19p15559sESWNC');
        $this->assertTrue($validTileList->isNineKindsOfTermOrHonour());

        $round->process('mockHand E 19m19p15559sESWNC; nineNineDraw E');
        $this->assertOver(ResultType::NINE_NINE_DRAW);

        $round->process('toNextRound');
        $this->assertPrivate();
    }

    function testExhaustiveDraw() {
        $round = $this->getInitRound();
        $round->process('mockHand E 123456789m12355p; riichi E 5p');
        for ($phase = $round->getPhaseState()->getPhase(); $phase != Phase::createOver(); $phase = $round->getPhaseState()->getPhase()) {
            $round->process('skip 1');
        }
        $this->assertOver(ResultType::EXHAUSTIVE_DRAW);
        $this->assertPoints([25000 - 1000 + 3000, 25000 - 1000, 25000 - 1000, 25000 - 1000]);
    }

    function testFourRiichiDraw() {
        $round = $this->getInitRound();
        $round->process(
            'mockHand E 123456789m12357s; riichi E 7s; passAll',
            'mockHand S 123456789m12357s; riichi S 7s; passAll',
            'mockHand W 123456789m12357s; riichi W 7s; passAll',
            'mockHand N 123456789m12357s; riichi N 7s; passAll'
        );
        $this->assertOver(ResultType::FOUR_REACH_DRAW);
    }

    function testFourWindDraw() {
        $round = $this->getInitRound();
        $round->process(
            'mockHand E E; discard E E; passAll',
            'mockHand S E; discard S E; passAll',
            'mockHand W E; discard W E; passAll',
            'mockHand N E; discard N E; passAll'
        );
        $this->assertOver(ResultType::FOUR_WIND_DRAW);
    }
    //endregion
}
