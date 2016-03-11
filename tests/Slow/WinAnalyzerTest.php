<?php

use Saki\Game\Round;
use Saki\Win\WinState;
use Saki\Win\WinTarget;

class WinAnalyzerTest extends \PHPUnit_Framework_TestCase {
    function testPublicPhaseTarget() {
        $r = new Round();
        $pro = $r->getProcessor();
        $pro->process('discard E E:s-5s:5s; mockHand E 123m456m789m123s5s'); // mock 13 tiles

        $target = new WinTarget($r->getTurnManager()->getCurrentPlayer(), $r);

        $dataProvider = [
            ['123456789m12355s', $target->getPrivateHand()->__toString()],
            ['123456789m1235s', $target->getPublicHand()->__toString()],
        ];
        foreach ($dataProvider as list($expected, $actual)) {
            $this->assertEquals($expected, $actual, sprintf('expected[%s] but actual[%s]', $expected, $actual));
        }
    }

    function testFuritenSelfDiscardedCase() {
        // self discarded furiten
        $r = new Round();
        $pro = $r->getProcessor();

        $p1 = $r->getTurnManager()->getCurrentPlayer();
        $pro->process('discard E E:s-123m456m789m123s55s:1s');

        $pro->process('passAll; discard S S:s-1s:1s');
        $this->assertEquals(WinState::getInstance(WinState::FURITEN_FALSE_WIN), $r->getWinResult($p1)->getWinState());

        $pro->process('passAll; discard W W:s-4s:4s');
        $this->assertEquals(WinState::getInstance(WinState::FURITEN_FALSE_WIN), $r->getWinResult($p1)->getWinState());
    }

    function testFuritenReachCase() {
        // other discarded after self reach furiten
        $r = new Round();
        $pro = $r->getProcessor();

        $p1 = $r->getTurnManager()->getCurrentPlayer();
        $pro->process('reach E E:s-123m456m789m23s55sE:E');

        $pro->process('passAll; discard S S:s-1s:1s');
        $this->assertEquals(WinState::getInstance(WinState::WIN_BY_OTHER), $r->getWinResult($p1)->getWinState());

        $pro->process('passAll; discard W W:s-1s:1s');
        $this->assertEquals(WinState::getInstance(WinState::FURITEN_FALSE_WIN), $r->getWinResult($p1)->getWinState());

        $pro->process('passAll; discard N N:s-4s:4s');
        $this->assertEquals(WinState::getInstance(WinState::FURITEN_FALSE_WIN), $r->getWinResult($p1)->getWinState());

        // furiten even after 1 turn
        $pro->process('mockWall E; passAll; discard E E');

        $pro->process('passAll; discard S S:s-1s:1s');
        $this->assertEquals(WinState::getInstance(WinState::FURITEN_FALSE_WIN), $r->getWinResult($p1)->getWinState());

        $pro->process('passAll; discard W W:s-4s:4s');
        $this->assertEquals(WinState::getInstance(WinState::FURITEN_FALSE_WIN), $r->getWinResult($p1)->getWinState());
    }

    function testFuritenOtherDiscardedCase() {
        // other discarded in one turn
        // other discarded after self reach furiten
        $r = new Round();
        $pro = $r->getProcessor();
        $pSouth = $r->getPlayerList()[1];
        $pro->process(
            'discard E E:s-E:E'
            , 'passAll; discard S S:s-123m456m789m23s55sE:E'
            , 'passAll; discard W W:s-1s:1s'
        );
        $this->assertEquals(WinState::getInstance(WinState::WIN_BY_OTHER), $r->getWinResult($pSouth)->getWinState());

        $pro->process('passAll; discard N N:s-1s:1s');
        $this->assertEquals(WinState::getInstance(WinState::FURITEN_FALSE_WIN), $r->getWinResult($pSouth)->getWinState());

        $pro->process('passAll; discard E E:s-4s:4s');
        $this->assertEquals(WinState::getInstance(WinState::FURITEN_FALSE_WIN), $r->getWinResult($pSouth)->getWinState());

        // not furiten after 1 turn
        $pro->process(
            'mockWall E; passAll; discard S E'
            , 'passAll; discard W W:s-1s:1s'
        );
        $this->assertEquals(WinState::getInstance(WinState::WIN_BY_OTHER), $r->getWinResult($pSouth)->getWinState()); // passed
    }
}
