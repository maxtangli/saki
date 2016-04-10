<?php
namespace Saki\Game;

use Saki\Tile\Tile;

/**
 * Wind indicator for player.
 * @package Saki\Game
 */
class PlayerWind extends IndicatorWind {
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