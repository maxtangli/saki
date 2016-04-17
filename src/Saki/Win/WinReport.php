<?php
namespace Saki\Win;

use Saki\Win\Yaku\YakuItemList;

class WinReport extends WinSubReport {
    static function createNotWin() {
        return new WinReport(
            WinState::create(WinState::NOT_WIN),
            new YakuItemList(),
            0
        );
    }

    function __construct(WinState $winState, YakuItemList $yakuList, int $fu) {
        parent::__construct($winState, $yakuList, $fu);
    }
}