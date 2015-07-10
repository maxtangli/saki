<?php

class WinAnalyzerTest extends \PHPUnit_Framework_TestCase {
    function testAllRunsYaku() {

        $player = new \Saki\Game\Player(1, 40000, \Saki\Tile\Tile::fromString('E'));

        $playerArea = new \Saki\Game\PlayerArea();
        $playerArea->drawInit(\Saki\Tile\TileList::fromString('123m456m789m123s55s')->toArray());
        $playerArea->setCandidateTile(\Saki\Tile\Tile::fromString('1m'));
        $player->setPlayerArea($playerArea);

        $meldList = \Saki\Meld\MeldList::fromString('123m,456m,789m,123s,55s');
        $subTarget = new \Saki\Win\WinAnalyzerSubTarget($meldList, $player);

        $yaku = \Saki\Yaku\AllRunsYaku::getInstance();
        $this->assertTrue($yaku->existIn($subTarget));

        // testAnalyzer
        $analyzer = new \Saki\Win\WinAnalyzer();
        $result = $analyzer->analyzeSubTarget($subTarget);
        $this->assertCount(1, $result->getYakuList(), $result->getYakuList());
        $this->assertInstanceOf('Saki\Yaku\AllRunsYaku', $result->getYakuList()[0]);

        // testReach
        $this->assertFalse(\Saki\Yaku\ReachYaku::getInstance()->existIn($subTarget));
        $playerArea->setIsReach(true);
        $this->assertTrue($subTarget->isReach());
        $this->assertTrue(\Saki\Yaku\ReachYaku::getInstance()->existIn($subTarget));

        // testValueTiles
        $this->assertFalse(\Saki\Yaku\RedValueTilesYaku::getInstance()->existIn($subTarget));
        $this->assertFalse(\Saki\Yaku\WhiteValueTilesYaku::getInstance()->existIn($subTarget));
        $this->assertFalse(\Saki\Yaku\GreenValueTilesYaku::getInstance()->existIn($subTarget));
        $this->assertFalse(\Saki\Yaku\SelfWindValueTilesYaku::getInstance()->existIn($subTarget));
        $playerArea->getDeclaredMeldList()->setInnerArray(
            [\Saki\Meld\Meld::fromString('CCC'),
                \Saki\Meld\Meld::fromString('FFF'),
                \Saki\Meld\Meld::fromString('PPP'),
                \Saki\Meld\Meld::fromString('EEE'),
            ]
        );
        $this->assertTrue(\Saki\Yaku\RedValueTilesYaku::getInstance()->existIn($subTarget));
        $this->assertTrue(\Saki\Yaku\WhiteValueTilesYaku::getInstance()->existIn($subTarget));
        $this->assertTrue(\Saki\Yaku\GreenValueTilesYaku::getInstance()->existIn($subTarget));
        $this->assertTrue(\Saki\Yaku\SelfWindValueTilesYaku::getInstance()->existIn($subTarget));
    }
}
