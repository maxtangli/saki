<?php
namespace Saki\Command\PublicCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Command\PublicCommand;
use Saki\Phase\PrivatePhaseState;
use Saki\Tile\Tile;

class PongCommand extends PublicCommand {
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class];
    }

    function __construct(CommandContext $context, Tile $playerSeatWind) {
        parent::__construct($context, [$playerSeatWind]);
    }

    function matchOther() {
        return true; // todo
    }

    function executeImpl() {
        $round = $this->getContext()->getRound();

        $round->getAreas()->pong(
            $this->getActPlayer(), $this->getCurrentPlayer()
        );
        $round->toNextPhase(
            new PrivatePhaseState($this->getActPlayer(), false)
        );
    }
}