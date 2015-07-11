<?php


class GameResultTest extends PHPUnit_Framework_TestCase {
    function testOverall() {
        /**
         * 25000 -> 31100 +1100 +1.1 +2 +20 = +22 + 20 = +42
         * 25000 -> 24400 -5600 -5.6 -6     = -6  + 10 = +4
         * 25000 -> 22300 -7700 -7.7 -8     = -8  - 10 = -18
         * 25000 -> 22200 -7800 -7.8 -8     = -8  - 20 = -28
         */
        $players = [
            new \Saki\Game\Player(1, 31100, \Saki\Tile\Tile::fromString('E')),
            new \Saki\Game\Player(2, 24400, \Saki\Tile\Tile::fromString('E')),
            new \Saki\Game\Player(3, 22300, \Saki\Tile\Tile::fromString('E')),
            new \Saki\Game\Player(4, 22200, \Saki\Tile\Tile::fromString('E')),
        ];
        $expected = [
            [1, 42],
            [2, 4],
            [3, -18],
            [4, -28],
        ];
        $r = new \Saki\Game\GameResult($players);
        foreach ($players as $k => $player) {
            $item = $r->getResultItem($player);
            list($expectedRank, $expectedPoint) = [$expected[$k][0], $expected[$k][1]];
            $this->assertEquals($expectedRank, $item->getRank());
            $this->assertEquals($expectedPoint, $item->getScorePoint());
        }
    }
}
