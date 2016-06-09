<?php

use Saki\Win\Result\ResultType;

class RoundTest extends SakiTestCase {
    function testNew() {
        // todo
    }

    function testRoll() {
        // todo
    }

    function testOver() {
        $r = $this->getInitRound();
        $r->roll(false);
        $r->roll(false);
        $r->roll(false);

        $r->process('mockHand E 123456789m12355s; tsumo E');
        $this->assertTrue($r->getAreas()->getPhaseState()->isGameOver($r));
        $f = $r->getAreas()->getPhaseState()->getFinalScore($r);
        // todo
    }

    function testDiscard() {
        $r = $this->getInitRound();
        $r->process('mockHand E 0p; discard E 0p');
        $this->assertLastOpen('0p');
    }

    function testReach() {
        // todo
    }

    function testChow() {
        $r = $this->getInitRound();

        $claimTurn = $r->getAreas()->getTurn();
        $r->process(
            'mockHand E 3m; discard E 3m',
            'mockHand S 450m12346789p13s; chow S 4m 0m'
        );

        $this->assertHasClaim($claimTurn);
        $this->assertCurrentTurnChanged('S', $claimTurn);
        $this->assertHand('5m12346789p13s', '340m', '3s');
    }

    function testPung() {
        $r = $this->getInitRound();

        $claimTurn = $r->getAreas()->getTurn();
        $r->process(
            'mockHand E 5m; discard E 5m',
            'mockHand W 550m12346789p13s; pung W 5m 0m'
        );

        $this->assertHasClaim($claimTurn);
        $this->assertCurrentTurnChanged('W', $claimTurn);
        $this->assertHand('12346789p13s5m', '550m', '5m'); // todo should not be 5m but 3s
    }

    function testKong() {
        $r = $this->getInitRound();

        $claimTurn = $r->getAreas()->getTurn();
        $r->process(
            'mockHand E 5m; discard E 5m',
            'mockNextReplace 1p; mockHand W 550m23456789p13s; kong W 5m 5m 0m'
        );

        $this->assertHasClaim($claimTurn);
        $this->assertCurrentTurnChanged('W', $claimTurn);
        $this->assertHand('23456789p13s1p', '5550m', '1p');
    }

    function testConcealedKong() {
        $r = $this->getInitRound();

        $claimTurn = $r->getAreas()->getTurn();
        $r->process('mockNextReplace 1p; mockHand E 5550m23456789p13s; concealedKong E 5m 5m 5m 0m');

        $this->assertHasClaim($claimTurn);
        $this->assertCurrentTurnNotChanged($claimTurn);
        $this->assertHand('23456789p13s1p', '(5550m)', '1p');
    }

    function testExtendKong() {
        $r = $this->getInitRound();

        $r->process(
            'mockHand E 5m; discard E 5m',
            'mockHand S 005m23456789p13s; mockNextReplace 1p; pung S 0m 5m'
        );

        // enter robQuad phase
        $claimTurn = $r->getAreas()->getTurn();
        $r->process('extendKong S 0m 055m');
        $this->assertPublic();
        $this->assertTrue($r->getAreas()->getPhaseState()->isRonOnly());
        $this->assertHasNotClaim($claimTurn);
        $this->assertCurrentTurnNotChanged($claimTurn);
        $this->assertHand(null, null, '0m', 'E');

        // leave robQuad phase and enter private phase
        $r->process('passAll');
        $this->assertPrivate('E');
        $this->assertHasClaim($claimTurn);
        $this->assertCurrentTurnNotChanged($claimTurn);
        $this->assertHand('23456789p13s1p', '5500m', '1p'); // todo right order of Meld?
    }

    function testNotFourKongDrawBySamePlayer() {
        $r = $this->getInitRound();

        $r->process(
            'mockHand E 1111s; concealedKong E 1s 1s 1s 1s',
            'mockHand E 1111s; concealedKong E 1s 1s 1s 1s',
            'mockHand E 1111s; concealedKong E 1s 1s 1s 1s',
            'mockHand E 1111s; concealedKong E 1s 1s 1s 1s',
            'mockHand E 1s; discard E 1s; passAll'
        );
        $this->assertPrivate();
    }

    function testFourKongDrawByConcealedKong() {
        $r = $this->getInitRound();

        $r->process(
            'mockHand E 1111s1m; concealedKong E 1s 1s 1s 1s; discard E 1m; passAll',
            'mockHand S 1111s1m; concealedKong S 1s 1s 1s 1s; discard S 1m; passAll',
            'mockHand W 1111s1m; concealedKong W 1s 1s 1s 1s; discard W 1m; passAll',
            'mockHand N 1111s1m; concealedKong N 1s 1s 1s 1s; discard N 1m; passAll'
        );
        $this->assertResultType(ResultType::FOUR_KONG_DRAW);
    }

    function testFourKongDrawByExtendKong() {
        $r = $this->getInitRound();

        $r->process(
            'mockHand E 1m; discard E 1m; passAll',
            'mockHand S 1111s1m; concealedKong S 1s 1s 1s 1s; discard S 1m; passAll',
            'mockHand W 1111s1m; concealedKong W 1s 1s 1s 1s; discard W 1m; passAll',
            'mockHand N 1111s1m; concealedKong N 1s 1s 1s 1s; discard N 1m',
            'mockHand E 111m1p; pung E 1m 1m; extendKong E 1m 111m; passAll; discard E 1p; passAll'
        );
        $this->assertResultType(ResultType::FOUR_KONG_DRAW);
    }

    function testFourKongDrawByKong() {
        $r = $this->getInitRound();

        $r->process(
            'mockHand E 1111s1m; concealedKong E 1s 1s 1s 1s; discard E 1m; passAll',
            'mockHand S 1111s1m; concealedKong S 1s 1s 1s 1s; discard S 1m; passAll',
            'mockHand W 1111s1m; concealedKong W 1s 1s 1s 1s; discard W 1m',
            'mockHand E 1111m; kong E 1m 1m 1m'
        );
        $this->assertPrivate();

        $r->process('discard E 1m; passAll');
        $this->assertResultType(ResultType::FOUR_KONG_DRAW);
    }
}
