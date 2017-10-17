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
     * @return bool
     */
    function isGameOver() {
        return $this->getRound()->getPhaseState()->isGameOver();
    }

    /**
     * @return ArrayList
     */
    function getSeatList() {
        return $this->seatList;
    }

    /**
     * @param UserProxy $userProxy
     * @return Seat
     */
    function getSeat(UserProxy $userProxy) {
        $match = function (Seat $seat) use ($userProxy) {
            return $seat->matchUserProxy($userProxy);
        };
        return $this->getSeatList()->getSingle($match);
    }

    /**
     * @param callable $callable
     */
    function callAll(callable $callable) {
        $init = function (Seat $seat) use ($callable) {
            $seat->call($callable);
        };
        $this->getSeatList()->walk($init);
    }

    function notifyAll() {
        $this->getSeatList()->walk(Utils::getMethodCallback('notify'));
    }
}