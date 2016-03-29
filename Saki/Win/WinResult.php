<?php
namespace Saki\Win;

use Saki\Win\Yaku\YakuItemList;

class WinResult extends WinSubResult {
    function __construct(WinState $winState, YakuItemList $yakuList, int $fuCount) {
        parent::__construct($winState, $yakuList, $fuCount);
    }
}