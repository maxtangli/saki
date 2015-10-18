<?php

use Saki\Game\MockRound;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Win\WinState;

class WinAnalyzerTest extends \PHPUnit_Framework_TestCase {
    function testPublicPhaseTarget() {
        $roundData = new \Saki\Game\RoundData();
        $roundData->getTileAreas()->setPublicTargetTile(\Saki\Tile\Tile::fromString('5s'));
        $roundData->setRoundPhase(\Saki\Game\RoundPhase::getPublicPhaseInstance());

        $player = $roundData->getPlayerList()[0];
        $playerArea = new \Saki\Game\PlayerArea();
        $playerArea->drawInit(\Saki\Tile\TileList::fromString('123m456m789m123s5s')->toArray());
        $playerArea->setPrivateTargetTile(\Saki\Tile\Tile::fromString('1m'));
        $player->setPlayerArea($playerArea);

        $target = new \Saki\Win\WinTarget($player, $roundData);
        $this->assertEquals(\Saki\Tile\TileSortedList::fromString('123m456m789m123s55s'), $target->getHandTileSortedList(true));
        $this->assertEquals(\Saki\Tile\TileSortedList::fromString('123m456m789m123s5s'), $target->getHandTileSortedList(false));
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

    function testAllRunsYaku() {
        $player = new \Saki\Game\Player(1, 40000, \Saki\Tile\Tile::fromString('E'));

        $playerArea = new \Saki\Game\PlayerArea();
        $playerArea->drawInit(\Saki\Tile\TileList::fromString('123m456m789m123s55s')->toArray());
        $playerArea->setPrivateTargetTile(\Saki\Tile\Tile::fromString('1m'));
        $player->setPlayerArea($playerArea);

        $meldList = \Saki\Meld\MeldList::fromString('123m,456m,789m,123s,55s');
        $roundData = new \Saki\Game\RoundData();
        $roundData->setRoundPhase(\Saki\Game\RoundPhase::getPrivatePhaseInstance());
        $subTarget = new \Saki\Win\WinSubTarget($meldList, $player, $roundData);

        $yaku = \Saki\Win\Yaku\AllRunsYaku::getInstance();
        $this->assertTrue($yaku->existIn($subTarget));

        // testAnalyzer
        $analyzer = new \Saki\Win\WinAnalyzer();
        $result = $analyzer->analyzeSubTarget($subTarget);
        $this->assertCount(1, $result->getYakuList(), $result->getYakuList());
        $cls = get_class(\Saki\Win\Yaku\AllRunsYaku::getInstance());
        $this->assertInstanceOf($cls, $result->getYakuList()[0]);

        // testValueTiles
        $this->assertFalse(\Saki\Win\Yaku\RedValueTilesYaku::getInstance()->existIn($subTarget));
        $this->assertFalse(\Saki\Win\Yaku\WhiteValueTilesYaku::getInstance()->existIn($subTarget));
        $this->assertFalse(\Saki\Win\Yaku\GreenValueTilesYaku::getInstance()->existIn($subTarget));
        $this->assertFalse(\Saki\Win\Yaku\SelfWindValueTilesYaku::getInstance()->existIn($subTarget));
        $playerArea->getDeclaredMeldList()->setInnerArray(
            [\Saki\Meld\Meld::fromString('CCC'),
                \Saki\Meld\Meld::fromString('FFF'),
                \Saki\Meld\Meld::fromString('PPP'),
                \Saki\Meld\Meld::fromString('EEE'),
            ]
        );
        $this->assertTrue(\Saki\Win\Yaku\RedValueTilesYaku::getInstance()->existIn($subTarget));
        $this->assertTrue(\Saki\Win\Yaku\WhiteValueTilesYaku::getInstance()->existIn($subTarget));
        $this->assertTrue(\Saki\Win\Yaku\GreenValueTilesYaku::getInstance()->existIn($subTarget));
        $this->assertTrue(\Saki\Win\Yaku\SelfWindValueTilesYaku::getInstance()->existIn($subTarget));
    }
}
