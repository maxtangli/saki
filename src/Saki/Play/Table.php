<?php

namespace Saki\Play;

use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Util\ArrayList;
use Saki\Util\Utils;

/**
 * @package Saki\Play
 */
class Table {
    private $round;
    private $seatList;

    /**
     * @param array $userProxies
     */
    function __construct(array $userProxies) {
        $round = new Round();

        $toRole = function (SeatWind $seatWind) use ($round) {
            return Role::createPlayer($round, $seatWind);
        };
        $roleList = $round->getRule()->getPlayerType()->getSeatWindList($toRole);
        $toParticipant = function (UserProxy $userProxy, Role $role) {
            return new Seat($this, $userProxy, $role);
        };
        $seatList = (new ArrayList())
            ->fromMapping(new ArrayList($userProxies), $roleList, $toParticipant);

        $this->round = $round;
        $this->seatList = $seatList;
    }

    /**
     * @return Round
     */
    function getRound() {
        return $this->round;
    }

    /**
     * @return ArrayList
     */
    function getSeatList() {
        return $this->seatList;
    }

    function notifyAll() {
        $this->getSeatList()->walk(Utils::getMethodCallback('notify'));
    }
}