<?php

class YakuAnalyzerTest extends \PHPUnit_Framework_TestCase {
    function testAllRunsYaku() {
        $playerArea = new \Saki\Game\PlayerArea();
        $playerArea->drawInit(\Saki\TileList::fromString('123m456m789m123s55s')->toArray());
        $playerArea->setCandidateTile(\Saki\Tile::fromString('1m'));
        //$target = new \Saki\Yaku\YakuAnalyzerTarget($playerArea);

        $meldList = \Saki\Meld\MeldList::fromString('123m,456m,789m,123s,55s');
        $subTarget = new \Saki\Yaku\YakuAnalyzerSubTarget($meldList, $playerArea);

        $this->assertTrue($subTarget->isConcealed());
        $this->assertTrue($subTarget->is4WinSetAnd1Pair());
        $this->assertTrue($subTarget->isAllSuit());

        $yaku = \Saki\Yaku\AllRunsYaku::getInstance();
        $this->assertTrue($yaku->existIn($subTarget));

        // testAnalyzer
        $analyzer = new \Saki\Yaku\YakuAnalyzer();
        $result = $analyzer->analyzeSubTarget($subTarget);
        $this->assertCount(1, $result->getYakuList());
        $this->assertInstanceOf('Saki\Yaku\AllRunsYaku', $result->getYakuList()[0]);
    }
}
