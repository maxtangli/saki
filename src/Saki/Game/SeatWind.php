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
        // todo replace offset into self
        $offsetNextDealerToSelf = $nextDealer->getWindTile()->getWindOffsetTo($this->getWindTile());
        return self::createEast()->toNext($offsetNextDealerToSelf);
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