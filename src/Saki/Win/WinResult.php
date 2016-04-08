<?php
namespace Saki\Win;

use Saki\Win\Yaku\YakuItemList;

class WinResult extends WinSubResult {
    static function createNotWin() {
        return new WinResult(
            WinState::create(WinState::NOT_WIN),
            new YakuItemList(),
            0
        );
    }

    function __construct(WinState $winState, YakuItemList $yakuList, int $fuCount) {
        parent::__construct($winState, $yakuList, $fuCount);
    }
}