<?php
namespace Saki\Command\PublicCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SelfWindParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Command\PublicCommand;
use Saki\RoundPhase\PrivatePhaseState;
use Saki\Tile\Tile;

class PongCommand extends PublicCommand {
    static function getParamDeclarations() {
        return [SelfWindParamDeclaration::class];
    }

    function __construct(CommandContext $context, Tile $playerSelfWind) {
        parent::__construct($context, [$playerSelfWind]);
    }

    function matchOtherConditions() {
        $tileAreas = $this->getContext()->getRoundData()->getTileAreas();
        return $this->getActPlayer()->getTileArea()->canPong(
            $tileAreas->getTargetTile()->getTile()
        );
    }

    function executeImpl() {
        $roundData = $this->getContext()->getRoundData();

        $roundData->getTileAreas()->pong(
            $this->getActPlayer(), $this->getCurrentPlayer()
        );
        $roundData->toNextPhase(
            new PrivatePhaseState($this->getActPlayer(), false)
        );
    }
}