<?php
namespace Saki\Win;

use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Game\SubHand;
use Saki\Meld\MeldList;

class WinSubTarget extends WinTarget {
    private $handMeldList;

    function __construct(MeldList $handMeldList, SeatWind $actor, Round $round) {
        parent::__construct($actor, $round);
        $this->handMeldList = $handMeldList;
    }

    function getHandMeldList() {
        return $this->handMeldList;
    }

    /**
     * @return MeldList
     */
    function getAllMeldList() {
        return $this->getHandMeldList()->getCopy()->concat($this->getDeclaredMeldList());
    }
    
    function getSubHand() {
        return SubHand::fromHand($this->getHand(), $this->getHandMeldList());
    }
}