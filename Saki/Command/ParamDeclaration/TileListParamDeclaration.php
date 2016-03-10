<?php
namespace Saki\Command\ParamDeclaration;

use Saki\Tile\TileList;

class TileListParamDeclaration extends ParamDeclaration {
    function toObject() {
        return TileList::fromString($this->getParamString());
    }
}