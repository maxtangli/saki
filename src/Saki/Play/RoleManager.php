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
        foreach ($this->playerWindCounts as $seatWindString => $count) {
            if ($count == 0) {
                $seatWind = SeatWind::fromString($seatWindString);
                $this->registPlayerWind($seatWind);
                return Role::createPlayer($seatWind);
            }
        }
        return Role::createViewer(SeatWind::createEast());
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