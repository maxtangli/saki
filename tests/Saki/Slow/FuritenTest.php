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

        $winState = $r->getAreas()->getWinReport(SeatWind::fromString($seatWind))->getWinState();
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
            'mockHand E 1m; discard E 1m; passAll',
            'mockHand E 23456789m44sPPP; mockHand S 1m; discard S 1m'
        );
        // furiten by self discard, two-side waiting case
        $this->assertFuriten($r, 'E', 'passAll; mockHand W 4m; discard W 4m');
    }

    function testSelfNextTurn() {
        $r = new Round();
        // furiten by self discard, next turn
        $this->assertFuriten(
            $r, 'E',
            'mockHand E 1m; discard E 1m; passAll',
            'skip 4; mockHand E 23456789m44sPPP; mockHand S 1m; discard S 1m'
        );
        // furiten by self discard, two-side waiting case, next turn
        $this->assertFuriten($r, 'E', 'passAll; mockHand W 4m; discard W 4m');
    }

    function testSelfNextNextTurn() {
        $r = new Round();
        // furiten by self discard, next next turn
        $this->assertFuriten(
            $r, 'E',
            'mockHand E 1m; discard E 1m; passAll',
            'skip 8; mockHand E 23456789m44sPPP; mockHand S 1m; discard S 1m'
        );
        // furiten by self discard, two-side wait case, next next turn
        $this->assertFuriten($r, 'E', 'passAll; mockHand W 4m; discard W 4m');
    }

    function testSelfExtendKongCase() {
        $r = new Round();
        // furiten by self extendKong, next turn
        $this->assertFuriten(
            $r, 'E',
            'mockHand E C; discard E C; passAll',
            'mockHand S 1m; discard S 1m',
            'mockHand E 111m; pung E 1m 1m; extendKong E 1m 111m; passAll',
            'skip 5; mockHand E 23789m44sPPP; mockHand S 4m; discard S 4m'
        );
    }

    function testRiichi() {
        $r = new Round();

        // setup reach
        $this->assertRon(
            $r, 'E',
            'mockHand E 123m456m789m23s55sE; riichi E E',
            'passAll; mockHand S 1s; discard S 1s'
        );
        // furiten by other discard after reach
        $this->assertFuriten(
            $r, 'E',
            'passAll; mockHand W 1s; discard W 1s'
        );
        // furiten by other discard after reach, two-side-wait case
        $this->assertFuriten(
            $r, 'E',
            'passAll; mockHand N 4s; discard N 4s'
        );

        // furiten by other discard after reach, next turn case
        $this->assertFuriten(
            $r, 'E',
            'mockNextDraw E; passAll; discard E E',
            'passAll; mockHand S 1s; discard S 1s'
        );
        // furiten by other discard after reach, next turn + two-side-wait case
        $this->assertFuriten(
            $r, 'E',
            'passAll; mockHand W 4s; discard W 4s'
        );
    }

    function testTurn() {
        // other discarded in one turn
        $r = new Round();
        // setup
        $this->assertRon(
            $r, 'S',
            'mockHand E E; discard E E'
            , 'passAll; mockHand S 123m456m789m23s55sE; discard S E'
            , 'passAll; mockHand W 1s; discard W 1s'
        );
        // furiten by other discard in one turn
        $this->assertFuriten(
            $r, 'S',
            'passAll; mockHand N 1s; discard N 1s'
        );
        // furiten by other discard in one turn, two-side-wait case
        $this->assertFuriten(
            $r, 'S',
            'passAll; mockHand E 4s; discard E 4s'
        );
        // not furiten after self's discard
        $this->assertRon(
            $r, 'S',
            'mockNextDraw E; passAll; discard S E'
            , 'passAll; mockHand W 1s; discard W 1s'
        );
    }

    function testTurnSpecial() {
        $r = new Round();
        // furiten since last self's discard, even multiple turn passed
        $this->assertFuriten(
            $r, 'S',
            'mockHand E E; discard E E',
            'passAll; mockHand S 123m456m789m23s55sE; discard S E', // wait 14s
            'passAll; mockHand W 1s; discard W 1s',
            'passAll; mockHand N 1s; discard N 1s'
        );
        $this->assertEquals(1, $r->getAreas()->getTurn()->getCircleCount());
        $this->assertFuriten(
            $r, 'S',
            'mockHand E 11sC; pung E 1s 1s; discard E C',
            'mockHand W CCC; pung W C C; discard W C',
            'mockHand E CCC; pung E C C; discard E C',
            'mockHand W CC1s; pung W C C; discard W 1s'
        );
        $this->assertEquals(3, $r->getAreas()->getTurn()->getCircleCount());
    }
}