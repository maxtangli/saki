<?php
namespace Saki\Command\Debug;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SelfWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileListParamDeclaration;
use Saki\Command\PlayerCommand;
use Saki\Tile\Tile;
use Saki\Tile\TileList;

class MockHandCommand extends PlayerCommand {
    static function getParamDeclarations() {
        return [SelfWindParamDeclaration::class, TileListParamDeclaration::class];
    }

    function __construct(CommandContext $context, Tile $selfWind, TileList $mockTileList) {
        parent::__construct($context, [$selfWind, $mockTileList]);
    }

    /**
     * @return TileList
     */
    function getMockTileList() {
        return $this->getParam(1);
    }

    function matchRequiredPhases() {
        return $this->getRoundPhase()->isPrivateOrPublic();
    }

    function matchRequiredPlayer() {
        return true;
    }

    function matchOtherConditions() {
        $hand = $this->getActPlayer()->getTileArea()->getHandReference();
        $mockTileList = $this->getMockTileList();
        return $mockTileList->count() <= $hand->count();
    }

    function executeImpl() {
        $tileAreas = $this->getContext()->getRoundData()->getTileAreas();
        $tileAreas->debugReplaceHand($this->getActPlayer(), $this->getMockTileList());
    }
}