<?php

namespace Saki\Tile;

use Saki\Util\Pool;
use Saki\Util\Singleton;

class TileFactory extends Singleton {
    private $tilePool;
    private $redDoraInstances;

    protected function __construct() {
        $this->tilePool = Pool::getInstance(__CLASS__);
        $this->redDoraInstances = [];
    }

    /**
     * @return Pool
     */
    protected function getTilePool() {
        return $this->tilePool;
    }

    /**
     * A trick to support redDora while keep == operator ignores redDora(since == compares object fields only).
     *
     * The trick exists because when red dora is considered to be added,
     * Tile comparisons by == operator have been used so much
     * that it's too expensive to add a $redDora member and replace tons of Tile == by Tile.equals(), which is the common way.
     *
     * Note that WeakMap is not required since Tile is a Multiton Class.
     * @var Tile[]
     */
    function isRedDoraTile(Tile $tile) {
        return in_array($tile, $this->redDoraInstances, true);
    }

    protected function registerRedDoraTile(Tile $tile) {
        $this->redDoraInstances[] = $tile;
    }

    /**
     * @param TileType $tileType
     * @param int|null $number
     * @param bool|false $isRedDora
     * @return int
     *
     * ValueID map
     *
     * - m 110-190 r5m 155
     * - p 210-290 r5p 255
     * - s 310-390 r5s 355
     * - E 410 S 420 W 430 N 440
     * - C 510 P 520 F 530
     */
    function toValueID(TileType $tileType, $number = null, $isRedDora = false) {
        $baseID = $tileType->getValue();
        $numberID = $tileType->isSuit() ? ($isRedDora ? 55 : $number * 10) : 0;
        return $baseID + $numberID;
    }

    function getOrGenerateTile(TileType $tileType, $number, bool $isRedDora, callable $tileGenerator) {
        $tilePool = $this->getTilePool();
        $key = $this->toValueID($tileType, $number, $isRedDora);

        if ($tilePool->exist($key)) {
            return $tilePool->get($key);
        } else {
            $newTile = $tileGenerator();
            if ($isRedDora) {
                $this->registerRedDoraTile($newTile);
            }
            return $tilePool->add($key, $newTile);
        }
    }
}