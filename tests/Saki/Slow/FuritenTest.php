<?php

use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Win\WinState;

class FuritenTest extends SakiTestCase {
    function assertFuriten(Round $r, string $seatWind, ...$scripts) {
        $this->assertFuritenImpl(true, $r, $seatWind, ...$scripts);
    }

    function assertRon(Round $r, string $seatWind, ...$scripts) {
        $this->assertFuritenImpl(false, $r, $seatWind, ...$scripts);
    }

    protected function assertFuritenImpl(bool $isFuriten, Round $r, string $seatWind, ...$scripts) {
        $r->getProcessor()->process(...$scripts);

        $winState = $r->getWinReport(SeatWind::fromString($seatWind))->getWinState();
        if ($isFuriten) {
            $this->assertEquals(WinState::create(WinState::FURITEN_FALSE_WIN), $winState);
        } else {
            $this->assertEquals(WinState::create(WinState::WIN_BY_OTHER), $winState);
        }
    }

    function testSelf() {
        $r = new Round();
        // furiten by self discard
        $this->assertFuriten(
            $r, 'E',
            'discard E E:s-1m:1m; passAll',
            'mockHand E 23456789m44sPPP; discard S S:s-1m:1m'
        );
        // furiten by self discard, two-side waiting case
        $this->assertFuriten($r, 'E', 'passAll; discard W W:s-4m:4m');
    }

    function testSelfNextTurn() {
        $r = new Round();
        // furiten by self discard, next turn
        $this->assertFuriten(
            $r, 'E',
            'discard E E:s-1m:1m; passAll',
            'skip 4; mockHand E 23456789m44sPPP; discard S S:s-1m:1m'
        );
        // furiten by self discard, two-side waiting case, next turn
        $this->assertFuriten($r, 'E', 'passAll; discard W W:s-4m:4m');
    }

    function testSelfNextNextTurn() {
        $r = new Round();
        // furiten by self discard, next next turn
        $this->assertFuriten(
            $r, 'E',
            'discard E E:s-1m:1m; passAll',
            'skip 8; mockHand E 23456789m44sPPP; discard S S:s-1m:1m'
        );
        // furiten by self discard, two-side wait case, next next turn
        $this->assertFuriten($r, 'E', 'passAll; discard W W:s-4m:4m');
    }

    function testSelfPlusKongCase() {
        $r = new Round();
        // furiten by self plusKong, next turn
        $this->assertFuriten(
            $r, 'E',
            'discard E E:s-C:C; passAll',
            'discard S S:s-1m:1m', 'mockHand E 111m; pong E; plusKong E 1m; passAll',
            'skip 5; mockHand E 23789m44sPPP; discard S S:s-4m:4m'
        );
    }

    function testReach() {
        $r = new Round();

        // setup reach
        $this->assertRon(
            $r, 'E',
            'reach E E:s-123m456m789m23s55sE:E',
            'passAll; discard S S:s-1s:1s'
        );
        // furiten by other discard after reach
        $this->assertFuriten(
            $r, 'E',
            'passAll; discard W W:s-1s:1s'
        );
        // furiten by other discard after reach, two-side-wait case
        $this->assertFuriten(
            $r, 'E',
            'passAll; discard N N:s-4s:4s'
        );

        // furiten by other discard after reach, next turn case
        $this->assertFuriten(
            $r, 'E',
            'mockNextDraw E; passAll; discard E E',
            'passAll; discard S S:s-1s:1s'
        );
        // furiten by other discard after reach, next turn + two-side-wait case
        $this->assertFuriten(
            $r, 'E',
            'passAll; discard W W:s-4s:4s'
        );
    }

    function testTurn() {
        // other discarded in one turn
        $r = new Round();
        // setup
        $this->assertRon(
            $r, 'S',
            'discard E E:s-E:E'
            , 'passAll; discard S S:s-123m456m789m23s55sE:E'
            , 'passAll; discard W W:s-1s:1s'
        );
        // furiten by other discard in one turn
        $this->assertFuriten(
            $r, 'S',
            'passAll; discard N N:s-1s:1s'
        );
        // furiten by other discard in one turn, two-side-wait case
        $this->assertFuriten(
            $r, 'S',
            'passAll; discard E E:s-4s:4s'
        );
        // not furiten after self's discard
        $this->assertRon(
            $r, 'S',
            'mockNextDraw E; passAll; discard S E'
            , 'passAll; discard W W:s-1s:1s'
        );
    }

    function testTurnSpecial() {
        $r = new Round();
        // furiten since last self's discard, even multiple turn passed
        $this->assertFuriten(
            $r, 'S',
            'discard E E:s-E:E',
            'passAll; discard S S:s-123m456m789m23s55sE:E', // wait 14s
            'passAll; discard W W:s-1s:1s',
            'passAll; discard N N:s-1s:1s'
        );
        $this->assertEquals(1, $r->getAreas()->getTurn()->getCircleCount());
        $this->assertFuriten(
            $r, 'S',
            'mockHand E 11sC; pong E; discard E C',
            'mockHand W CCC; pong W; discard W C',
            'mockHand E CCC; pong E; discard E C',
            'mockHand W CC1s; pong W; discard W 1s'
        );
        $this->assertEquals(3, $r->getAreas()->getTurn()->getCircleCount());
    }
}