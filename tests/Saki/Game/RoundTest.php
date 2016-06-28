<?php

use Saki\Game\SeatWind;
use Saki\Win\Result\ResultType;

class RoundTest extends \SakiTestCase {
    function testNew() {
        // todo
    }

    function testRoll() {
        // todo
    }

    function testOver() {
        $round = $this->getInitRound();
        $round->roll(false);
        $round->roll(false);
        $round->roll(false);

        $round->process('mockHand E 123456789m12355s; tsumo E');
        $this->assertTrue($round->getPhaseState()->isGameOver($round));
        $f = $round->getPhaseState()->getFinalScore($round);
        // todo
    }

    function testDiscard() {
        $round = $this->getInitRound();
        $round->process('mockHand E 0p; discard E 0p');
        $this->assertLastOpen('0p');
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

        $this->assertHasClaim($claimTurn);
        $this->assertCurrentTurnChanged('S', $claimTurn);
        $this->assertHand('5m12346789p13s', '340m', '3s');
    }

    function testPung() {
        $round = $this->getInitRound();

        $claimTurn = $round->getTurn();
        $round->process(
            'mockHand E 5m; discard E 5m',
            'mockHand W 550m12346789p13s; pung W 5m0m'
        );

        $this->assertHasClaim($claimTurn);
        $this->assertCurrentTurnChanged('W', $claimTurn);
        $this->assertHand('12346789p13s5m', '550m', '5m'); // todo should not be 5m but 3s
    }

    function testKong() {
        $round = $this->getInitRound();

        $claimTurn = $round->getTurn();
        $round->process(
            'mockHand E 5m; discard E 5m',
            'mockNextReplace 1p; mockHand W 550m23456789p13s; kong W 5m5m0m'
        );

        $this->assertHasClaim($claimTurn);
        $this->assertCurrentTurnChanged('W', $claimTurn);
        $this->assertHand('23456789p13s1p', '5550m', '1p');
    }

    function testConcealedKong() {
        $round = $this->getInitRound();

        $claimTurn = $round->getTurn();
        $round->process('mockNextReplace 1p; mockHand E 5550m23456789p13s; concealedKong E 5m5m5m0m');

        $this->assertHasClaim($claimTurn);
        $this->assertCurrentTurnNotChanged($claimTurn);
        $this->assertHand('23456789p13s1p', '(5550m)', '1p');
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
        $this->assertTrue($round->getPhaseState()->isRonOnly());
        $this->assertHasNotClaim($claimTurn);
        $this->assertCurrentTurnNotChanged($claimTurn);
        $this->assertHand(null, null, '0m', 'E');

        // leave robQuad phase and enter private phase
        $round->process('passAll');
        $this->assertPrivate('E');
        $this->assertHasClaim($claimTurn);
        $this->assertCurrentTurnNotChanged($claimTurn);
        $this->assertHand('23456789p13s1p', '5500m', '1p'); // todo right order of Meld?
    }

    // todo test kong not able for four kongs case

    function testNotFourKongDrawBySamePlayer() {
        $round = $this->getInitRound();

        $round->process(
            'mockHand E 1111s; concealedKong E 1s1s1s1s',
            'mockHand E 1111s; concealedKong E 1s1s1s1s',
            'mockHand E 1111s; concealedKong E 1s1s1s1s',
            'mockHand E 1111s; concealedKong E 1s1s1s1s',
            'mockHand E 1s; discard E 1s; passAll'
        );
        $this->assertPrivate();
    }

    function testFourKongDrawByConcealedKong() {
        $round = $this->getInitRound();

        $round->process(
            'mockHand E 1111s1m; concealedKong E 1s1s1s1s; discard E 1m; passAll',
            'mockHand S 1111s1m; concealedKong S 1s1s1s1s; discard S 1m; passAll',
            'mockHand W 1111s1m; concealedKong W 1s1s1s1s; discard W 1m; passAll',
            'mockHand N 1111s1m; concealedKong N 1s1s1s1s; discard N 1m; passAll'
        );
        $this->assertResultType(ResultType::FOUR_KONG_DRAW);
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
        $this->assertResultType(ResultType::FOUR_KONG_DRAW);
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
        $this->assertResultType(ResultType::FOUR_KONG_DRAW);
    }

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

    function testPointList() {
        $round = $this->getInitRound();

        $facade = $round->getPointHolder()->getPointList();

        $this->assertFalse($facade->hasMinus());
        $this->assertTrue($facade->hasTiledTop());
        $this->assertEquals(25000, $facade->getFirst()->getPoint());
    }
}
