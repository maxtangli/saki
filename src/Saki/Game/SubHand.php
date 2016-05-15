<?php
namespace Saki\Game;
use Saki\Meld\MeldList;

/**
 * A Hand specified by a private MeldList.
 * @package Saki\Game
 */
class SubHand extends Hand {
    /**
     * @param Hand $hand
     * @param MeldList $privateMeldList
     * @return SubHand
     */
    static function fromHand(Hand $hand, MeldList $privateMeldList) {
        return new self($privateMeldList, $hand->getMelded(), $hand->getTarget());
    }
    
    private $privateMeldList;

    /**
     * @param MeldList $privateMeldList
     * @param MeldList $melded
     * @param Target $target
     */
    function __construct(MeldList $privateMeldList, MeldList $melded, Target $target) {
        $public = $privateMeldList->toTileList()->remove($target->getTile());
        parent::__construct($public, $melded, $target);

        $this->privateMeldList = $privateMeldList;
    }

    /**
     * @return MeldList
     */
    function getPrivateMeldList() {
        return $this->privateMeldList;
    }

    /**
     * @return MeldList
     */
    function getAllMeldList() {
        return $this->getPrivateMeldList()->getCopy()
            ->concat($this->getMelded());
    }
}