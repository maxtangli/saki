<?php
namespace Saki\Command\ParamDeclaration;

use Saki\Meld\Meld;

/**
 * @package Saki\Command\ParamDeclaration
 */
class MeldParamDeclaration extends ParamDeclaration {
    //region ParamDeclaration impl
    function toObject() {
        return Meld::fromString($this->getParamString());
    }
    //endregion
}