<?php

class WinAnalyzerTest extends \PHPUnit_Framework_TestCase {
    function testPublicPhaseTarget() {
        $roundData = new \Saki\Game\RoundData();
        $roundData->getTileAreas()->setPublicTargetTile(\Saki\Tile\Tile::fromString('5s'));
        $roundData->setRoundPhase(\Saki\Game\RoundPhase::getPublicPhaseInstance());

        $player = $roundData->getPlayerList()[0];
        $playerArea = new \Saki\Game\PlayerArea();
        $playerArea->drawInit(\Saki\Tile\TileList::fromString('123m456m789m123s5s')->toArray());
        $playerArea->setCandidateTile(\Saki\Tile\Tile::fromString('1m'));
        $player->setPlayerArea($playerArea);

        $target = new \Saki\Win\WinTarget($player, $roundData);
        $this->assertEquals(\Saki\Tile\TileSortedList::fromString('123m456m789m123s55s'), $target->getHandTileSortedList());
        $this->assertEquals(\Saki\Tile\TileSortedList::fromString('123m456m789m123s5s'), $target->getHandTileSortedList(false));
    }

    function testAllRunsYaku() {

        $player = new \Saki\Game\Player(1, 40000, \Saki\Tile\Tile::fromString('E'));

        $playerArea = new \Saki\Game\PlayerArea();
        $playerArea->drawInit(\Saki\Tile\TileList::fromString('123m456m789m123s55s')->toArray());
        $playerArea->setCandidateTile(\Saki\Tile\Tile::fromString('1m'));
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

        // testReach
        $this->assertFalse(\Saki\Win\Yaku\ReachYaku::getInstance()->existIn($subTarget));
        $playerArea->setIsReach(true);
        $this->assertTrue($subTarget->isReach());
        $this->assertTrue(\Saki\Win\Yaku\ReachYaku::getInstance()->existIn($subTarget));

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
