<?php
namespace Saki\Win;

use Saki\Game\Meld\MeldList;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Game\SubHand;
use Saki\Util\ArrayList;
use Saki\Win\Series\Series;

/**
 * @package Saki\Win
 */
class WinSubTarget extends WinTarget {
    private $handMeldList;

    /**
     * @param Round $round
     * @param SeatWind $actor
     * @param MeldList $handMeldList
     */
    function __construct(Round $round, SeatWind $actor, MeldList $handMeldList) {
        parent::__construct($round, $actor);
        $this->handMeldList = $handMeldList;
    }

    /**
     * @return MeldList
     */
    function getHandMeldList() {
        return $this->handMeldList;
    }

    /**
     * @return SubHand
     */
    function getSubHand() {
        return SubHand::fromHand($this->getHand(), $this->getHandMeldList());
    }

    /**
     * Sugar method.
     * @return MeldList
     */
    function getAllMeldList() {
        return $this->getSubHand()->getAllMeldList();
    }
}