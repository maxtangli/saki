<?php
namespace Saki\Hand;

use Saki\Meld\MeldList;
use Saki\Tile\Tile;

/**
 * A hand for a player plus with a specified ownMeldList as ownTileList's meld combination.
 * @package Saki\Hand
 */
class SubHand extends Hand {
    private $ownMeldList;

    private $fastAllMeldList;

    function __construct(MeldList $ownMeldList, MeldList $declare, Tile $target) {
        parent::__construct($ownMeldList->toTileList(), $declare, $target);
        $this->ownMeldList = $ownMeldList;
    }

    /**
     * @return MeldList
     */
    function getOwnMeldList() {
        return $this->ownMeldList;
    }

    /**
     * @return MeldList
     */
    function getAllMeldList() {
        if ($this->fastAllMeldList === null) {
            $a = array_merge($this->getOwnMeldList()->toArray(), $this->getDeclare()->toArray());
            $this->fastAllMeldList = new MeldList($a, false);
        }
        return $this->fastAllMeldList;
    }
}