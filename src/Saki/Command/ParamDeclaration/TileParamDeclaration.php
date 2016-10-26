<?php
namespace Saki\Command\ParamDeclaration;

use Saki\Game\Tile\Tile;

/**
 * @package Saki\Command\ParamDeclaration
 */
class TileParamDeclaration extends ParamDeclaration {
    //region ParamDeclaration impl
    function toObject() {
        $paramString = $this->getParamString();
        return Tile::fromString($paramString);
    }
    //endregion
}