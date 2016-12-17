<?php
namespace Saki\Win;

use Saki\Game\SeatWind;
use Saki\Util\Utils;
use Saki\Win\Yaku\YakuItemList;

/**
 * @package Saki\Win
 */
class WinReport extends WinSubReport {
    /**
     * @param SeatWind $actor
     * @return WinReport
     */
    static function createNotWin(SeatWind $actor) {
        return new WinReport(
            $actor,
            WinState::create(WinState::NOT_WIN),
            new YakuItemList(),
            0
        );
    }

    /**
     * @return array
     */
    function toJson() {
        $a = [
            'actor' => $this->getActor()->__toString(),
            'fan' => $this->getFan(),
            'fu' => $this->getFu(),
            'yakuItems' => $this->getYakuItemList()->toArray(Utils::getToStringCallback()),
        ];
        return $a;
    }
}