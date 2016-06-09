<?php

namespace Saki\Command\ParamDeclaration;

use Saki\Game\SeatWind;

/**
 * @package Saki\Command\ParamDeclaration
 */
class SeatWindParamDeclaration extends ParamDeclaration {
    //region ParamDeclaration impl
    function toObject() {
        $paramString = $this->getParamString();
        return SeatWind::fromString($paramString);
    }
    //endregion
}