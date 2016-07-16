<?php

use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Win\WinState;

class FuritenTest extends \SakiTestCase {
    function assertFuriten(Round $round, string $seatWind, ...$scripts) {
        $this->assertFuritenImpl(true, $round, $seatWind, ...$scripts);
    }

    function assertRon(Round $round, string $seatWind, ...$scripts) {
        $this->assertFuritenImpl(false, $round, $seatWind, ...$scripts);
    }

    protected function assertFuritenImpl(bool $isFuriten, Round $round, string $seatWind, ...$scripts) {
        $round->process(...$scripts);

        $winState = $round->getWinReport(SeatWind::fromString($seatWind))->getWinState();
        $expected = WinState::create($isFuriten ? WinState::FURITEN_FALSE_WIN : WinState::WIN_BY_OTHER);
        $this->assertEquals($expected, $winState);
    }

    function testSelf() {
        $round = $this->getInitRound();
        // furiten by self discard
        $this->assertFuriten(
            $round, 'E',
            'mockHand E 1m; discard E 1m; passAll',
            'mockHand E 23456789m44sPPP; mockHand S 1m; discard S 1m'
        );
        // furiten by self discard, two-side waiting case
        $this->assertFuriten($round, 'E', 'passAll; mockHand W 4m; discard W 4m');
    }

    function testSelfNextTurn() {
        $round = $this->getInitRound();
        // furiten by self discard, next turn
        $this->assertFuriten(
            $round, 'E',
            'mockHand E 1m; discard E 1m; passAll',
            'skip 4; mockHand E 23456789m44sPPP; mockHand S 1m; discard S 1m'
        );
        // furiten by self discard, two-side waiting case, next turn
        $this->assertFuriten($round, 'E', 'passAll; mockHand W 4m; discard W 4m');
    }

    function testSelfNextNextTurn() {
        $round = $this->getInitRound();
        // furiten by self discard, next next turn
        $this->assertFuriten(
            $round, 'E',
            'mockHand E 1m; discard E 1m; passAll',
            'skip 8; mockHand E 23456789m44sPPP; mockHand S 1m; discard S 1m'
        );
        // furiten by self discard, two-side wait case, next next turn
        $this->assertFuriten($round, 'E', 'passAll; mockHand W 4m; discard W 4m');
    }

    function testSelfExtendKongCase() {
        $round = $this->getInitRound();
        // furiten by self extendKong, next turn
        $this->assertFuriten(
            $round, 'E',
            'mockHand E C; discard E C; passAll',
            'mockHand S 1m; discard S 1m',
            'mockHand E 111m; pung E 1m1m; extendKong E 1m 111m; passAll',
            'skip 5; mockHand E 23789m44sPPP; mockHand S 4m; discard S 4m'
        );
    }

    function testRiichi() {
        $round = $this->getInitRound();

        // setup reach
        $this->assertRon(
            $round, 'E',
            'mockHand E 123m456m789m23s55sE; riichi E E',
            'passAll; mockHand S 1s; discard S 1s'
        );
        // furiten by other discard after reach
        $this->assertFuriten(
            $round, 'E',
            'passAll; mockHand W 1s; discard W 1s'
        );
        // furiten by other discard after reach, two-side-wait case
        $this->assertFuriten(
            $round, 'E',
            'passAll; mockHand N 4s; discard N 4s'
        );

        // furiten by other discard after reach, next turn case
        $this->assertFuriten(
            $round, 'E',
            'mockNextDraw E; passAll; discard E E',
            'passAll; mockHand S 1s; discard S 1s'
        );
        // furiten by other discard after reach, next turn + two-side-wait case
        $this->assertFuriten(
            $round, 'E',
            'passAll; mockHand W 4s; discard W 4s'
        );
    }

    function testTurn() {
        // other discarded in one turn
        $round = $this->getInitRound();
        // setup
        $this->assertRon(
            $round, 'S',
            'mockHand E E; discard E E'
            , 'passAll; mockHand S 123m456m789m23s55sE; discard S E'
            , 'passAll; mockHand W 1s; discard W 1s'
        );
        // furiten by other discard in one turn
        $this->assertFuriten(
            $round, 'S',
            'passAll; mockHand N 1s; discard N 1s'
        );
        // furiten by other discard in one turn, two-side-wait case
        $this->assertFuriten(
            $round, 'S',
            'passAll; mockHand E 4s; discard E 4s'
        );
        // not furiten after self's discard
        $this->assertRon(
            $round, 'S',
            'mockNextDraw E; passAll; discard S E'
            , 'passAll; mockHand W 1s; discard W 1s'
        );
    }

    function testTurnSpecial() {
        $round = $this->getInitRound();
        // furiten since last self's discard, even multiple turn passed
        $this->assertFuriten(
            $round, 'S',
            'mockHand E E; discard E E',
            'passAll; mockHand S 123m456m789m23s55sE; discard S E', // wait 14s
            'passAll; mockHand W 1s; discard W 1s',
            'passAll; mockHand N 1s; discard N 1s'
        );
        $this->assertEquals(1, $round->getTurn()->getCircleCount());
        $this->assertFuriten(
            $round, 'S',
            'mockHand E 11sC; pung E 1s1s; discard E C',
            'mockHand W CCC; pung W CC; discard W C',
            'mockHand E CCC; pung E CC; discard E C',
            'mockHand W CC1s; pung W CC; discard W 1s'
        );
        $this->assertEquals(3, $round->getTurn()->getCircleCount());
    }
}