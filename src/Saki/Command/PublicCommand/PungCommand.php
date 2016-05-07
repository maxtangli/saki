<?php
namespace Saki\Command\PublicCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Command\PublicCommand;
use Saki\Game\SeatWind;
use Saki\Phase\PrivatePhaseState;

class PungCommand extends PublicCommand {
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class];
    }

    function __construct(CommandContext $context, SeatWind $playerSeatWind) {
        parent::__construct($context, [$playerSeatWind]);
    }

    protected function matchOther(CommandContext $context) {
        return true; // todo
    }

    protected function executeImpl(CommandContext $context) {
        $round = $context->getRound();

        $round->getAreas()->pung($this->getActor());
        $round->toNextPhase(
            new PrivatePhaseState($this->getActor(), false)
        );
    }
}