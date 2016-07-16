<?php
namespace Saki\Game;

use Saki\Tile\Tile;

/**
 * Wind indicator for player.
 * @package Saki\Game
 */
class SeatWind extends IndicatorWind {
    /**
     * Return self's next SeatWind when current SeatWind $nextDealer will be next dealer.
     * @param SeatWind $nextDealer
     * @return SeatWind
     */
    function toNextSelf(SeatWind $nextDealer) { 
        // note: too complex, better to find a simpler way
        // diff keeps when dealer rolls
        $dealerToThis = $nextDealer->getOffsetTo($this);
        return self::createEast()->toNext($dealerToThis);
    }

    /**
     * @param bool $keepDealer
     * @return SeatWind
     */
    function toRolled(bool $keepDealer) {
        return $keepDealer ? $this : $this->toNext(-1);
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