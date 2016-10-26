<?php
namespace Saki\Play;

use Saki\Game\PlayerType;
use Saki\Game\SeatWind;

/**
 * @package Saki\Play
 */
class RoleManager {
    private $playerType;
    private $playerWindCounts;

    /**
     * @param PlayerType $playerType
     */
    function __construct(PlayerType $playerType) {
        $this->playerType = $playerType;
        $this->playerWindCounts = $playerType->getSeatWindMap(0);
    }

    /**
     * @return PlayerType
     */
    function getPlayerType() {
        return $this->playerType;
    }

    /**
     * @return SeatWind|false
     */
    function getRemainPlayerWind() {
        foreach ($this->playerWindCounts as $key => $count) {
            if ($count == 0) {
                return SeatWind::fromString($key);
            }
        }
        return false;
    }

    /**
     * @param SeatWind $seatWind
     */
    private function registPlayerWind(SeatWind $seatWind) {
        $key = $seatWind->__toString();
        $this->playerWindCounts[$key]++;
    }

    /**
     * @param SeatWind $seatWind
     */
    private function unregistPlayerWind(SeatWind $seatWind) {
        $key = $seatWind->__toString();
        $this->playerWindCounts[$key]--;
    }

    /**
     * @param Role $forceRole
     * @return Role
     */
    function assign(Role $forceRole = null) {
        if (isset($forceRole)) {
            return $this->assignSpecified($forceRole);
        }
        return $this->assignRemain();
    }

    /**
     * @param Role $role
     * @return Role
     */
    function assignSpecified(Role $role) {
        if ($role->isPlayer()) {
            $this->registPlayerWind($role->getViewer());
        } else {
            // if viewer, do nothing
        }
        return $role;
    }

    /**
     * @return Role
     */
    function assignRemain() {
        $seatWind = $this->getRemainPlayerWind();

        if ($seatWind === false) {
            return Role::createViewer(SeatWind::createEast());
        }

        $this->registPlayerWind($seatWind);
        return Role::createPlayer($seatWind);
    }

    /**
     * @param Role $role
     */
    function recycle(Role $role) {
        if ($role->isPlayer()) {
            $this->unregistPlayerWind($role->getViewer());
        } else {
            // if viewer, do nothing
        }
    }
}