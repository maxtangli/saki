<?php
namespace Saki\Command\ParamDeclaration;

use Saki\Tile\Tile;

class TileParamDeclaration extends ParamDeclaration {
    function toObject() {
        return Tile::fromString($this->getParamString());
    }
}