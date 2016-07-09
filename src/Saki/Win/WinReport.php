<?php
namespace Saki\Win;

use Saki\Game\SeatWind;
use Saki\Win\Yaku\YakuItemList;

class WinReport extends WinSubReport {
    static function createNotWin(SeatWind $actor) {
        return new WinReport(
            $actor,
            WinState::create(WinState::NOT_WIN),
            new YakuItemList(),
            0
        );
    }
}