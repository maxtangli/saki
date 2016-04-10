<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Util\Immutable;

/**
 * A wrapper of wind tile for player.
 * @package Saki\Game
 */
class PlayerWind implements Immutable {
    /**
     * @param string $s
     * @return PlayerWind
     */
    static function fromString(string $s) {
        return new self(
            Tile::fromString($s)
        );
    }

    /**
     * @return PlayerWind
     */
    static function createEast() {
        return new PlayerWind(Tile::fromString('E'));
    }

    /**
     * @return PlayerWind
     */
    static function createSouth() {
        return new PlayerWind(Tile::fromString('S'));
    }

    /**
     * @return PlayerWind
     */
    static function createWest() {
        return new PlayerWind(Tile::fromString('W'));
    }

    /**
     * @return PlayerWind
     */
    static function createNorth() {
        return new PlayerWind(Tile::fromString('N'));
    }

    /**
     * @return PlayerWind[]
     */
    static function createAll() {
        return [
            self::createEast(), self::createSouth(), self::createWest(), self::createNorth()
        ];
    }

    private $wind;

    /**
     * @param Tile $wind
     */
    function __construct(Tile $wind) {
        $wind->assertWind();
        $this->wind = $wind;
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->getWindTile()->__toString();
    }

    /**
     * @param int $offset
     * @return PlayerWind
     */
    function toNext(int $offset) {
        return new self($this->getWindTile()->getNextTile($offset));
    }

    /**
     * Return self's next PlayerWind when current PlayerWind $nextDealer will be next dealer.
     * @param PlayerWind $nextDealer
     * @return PlayerWind
     */
    function toNextSelf(PlayerWind $nextDealer) {
        // todo replace offset into self
        $offsetNextDealerToSelf = $nextDealer->getWindTile()->getWindOffsetTo($this->getWindTile());
        return self::createEast()->toNext($offsetNextDealerToSelf);
    }

    /**
     * @return Tile
     */
    function getWindTile() {
        return $this->wind;
    }

    /**
     * @return bool
     */
    function isDealer() {
        return $this->getWindTile() == Tile::fromString('E');
    }

    /**
     * @return bool
     */
    function isLeisureFamily() {
        return !$this->isDealer();
    }
}