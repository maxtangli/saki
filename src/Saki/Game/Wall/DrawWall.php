<?php
namespace Saki\Game\Wall;

use Saki\Game\PlayerType;
use Saki\Game\Tile\Tile;

/**
 * @package Saki\Game\Wall
 */
class DrawWall extends LiveWall {
    function __construct() {
        parent::__construct(true);
    }

    /**
     * @return bool
     */
    function isBottomOfTheSea() {
        return $this->getRemainTileCount() == 0;
    }

    /**
     * @return Tile
     */
    function draw() {
        return $this->outNext();
    }

    /**
     * @param PlayerType $playerType
     * @return Tile[][] e.x. [E => [1s,2s...] ...]
     */
    function deal(PlayerType $playerType) {
        $result = $playerType->getSeatWindMap([]);
        foreach ([4, 4, 4, 1] as $drawTileCount) {
            foreach ($result as $k => $notUsed) {
                $nTodo = $drawTileCount;
                while ($nTodo-- > 0) {
                    $result[$k][] = $this->draw();
                }
            }
        }
        return $result;
    }
}