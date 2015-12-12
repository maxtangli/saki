<?php

use Saki\Game\MockRound;
use Saki\Game\RoundData;
use Saki\Game\RoundPhase;
use Saki\Game\TileArea;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Tile\TileSortedList;
use Saki\Win\WinState;
use Saki\Game\Player;
use Saki\Win\WinTarget;

class WinAnalyzerTest extends \PHPUnit_Framework_TestCase {
    function testPublicPhaseTarget() {
        $r = new MockRound();
        $targetTile = Tile::fromString('5s');
        $replaceHand = TileList::fromString('123m456m789m123s5s');
        $r->debugDiscardByReplace($r->getCurrentPlayer(), $targetTile);
        $r->debugSetHand($r->getCurrentPlayer(), $replaceHand);

        $target = new WinTarget($r->getCurrentPlayer(), $r->getRoundData());

        $dataProvider = [
            ['123456789m12355s', $target->getPrivateHand()->__toString()],
            ['123456789m1235s', $target->getPublicHand()->__toString()],
        ];
        foreach($dataProvider as list($expected, $actual)) {
            $this->assertEquals($expected, $actual, sprintf('expected[%s] but actual[%s]', $expected, $actual));
        }
    }

    function testFuritenSelfDiscardedCase() {
        // self discarded furiten
        $r = new MockRound();
        $p1 = $r->getCurrentPlayer();
        $r->debugDiscardByReplace($p1, Tile::fromString('1s'), TileList::fromString('123m456m789m123s55s'));

        $r->passPublicPhase();
        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('1s'));
        $this->assertEquals(WinState::getInstance(WinState::FURITEN_FALSE_WIN), $r->getWinResult($p1)->getWinState());

        $r->passPublicPhase();
        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('4s'));
        $this->assertEquals(WinState::getInstance(WinState::FURITEN_FALSE_WIN), $r->getWinResult($p1)->getWinState());
    }

    function testFuritenReachCase() {
        // other discarded after self reach furiten
        $r = new MockRound();
        $p1 = $r->getCurrentPlayer();
        $r->debugReachByReplace($p1, Tile::fromString('E'), TileList::fromString('123m456m789m23s55sE'));

        $r->passPublicPhase();
        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('1s'));
        $this->assertEquals(WinState::getInstance(WinState::WIN_BY_OTHER), $r->getWinResult($p1)->getWinState());

        $r->passPublicPhase();
        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('1s'));
        $this->assertEquals(WinState::getInstance(WinState::FURITEN_FALSE_WIN), $r->getWinResult($p1)->getWinState());

        $r->passPublicPhase();
        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('4s'));
        $this->assertEquals(WinState::getInstance(WinState::FURITEN_FALSE_WIN), $r->getWinResult($p1)->getWinState());

        // furiten even after 1 turn
        $r->debugSetWallPopTile(Tile::fromString('E'));
        $r->passPublicPhase();
        $r->discard($p1, Tile::fromString('E'));

        $r->passPublicPhase();
        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('1s'));
        $this->assertEquals(WinState::getInstance(WinState::FURITEN_FALSE_WIN), $r->getWinResult($p1)->getWinState());

        $r->passPublicPhase();
        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('4s'));
        $this->assertEquals(WinState::getInstance(WinState::FURITEN_FALSE_WIN), $r->getWinResult($p1)->getWinState());
    }

    function testFuritenOtherDiscardedCase() {
        // other discarded in one turn
        // other discarded after self reach furiten
        $r = new MockRound();
        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('E'));

        $r->passPublicPhase();
        $p2 = $r->getCurrentPlayer();
        $r->debugDiscardByReplace($p2, Tile::fromString('E'), TileList::fromString('123m456m789m23s55sE'));

        $r->passPublicPhase();
        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('1s'));
        $this->assertEquals(WinState::getInstance(WinState::WIN_BY_OTHER), $r->getWinResult($p2)->getWinState());

        $r->passPublicPhase();
        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('1s'));
        $this->assertEquals(WinState::getInstance(WinState::FURITEN_FALSE_WIN), $r->getWinResult($p2)->getWinState());

        $r->passPublicPhase();
        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('4s'));
        $this->assertEquals(WinState::getInstance(WinState::FURITEN_FALSE_WIN), $r->getWinResult($p2)->getWinState());

        // not furiten after 1 turn
        $r->debugSetWallPopTile(Tile::fromString('E'));
        $r->passPublicPhase();
        $r->discard($p2, Tile::fromString('E'));

        $r->passPublicPhase();
        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('1s'));
        $this->assertEquals(WinState::getInstance(WinState::WIN_BY_OTHER), $r->getWinResult($p2)->getWinState()); // passed
    }
}
