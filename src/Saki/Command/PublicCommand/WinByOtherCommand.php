<?php
namespace Saki\Command\PublicCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Command\PublicCommand;
use Saki\Game\SeatWind;
use Saki\Phase\OverPhaseState;
use Saki\Result\RoundWinResult;

class WinByOtherCommand extends PublicCommand {
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
        $round = $this->getContext()->getRound();

        $result = RoundWinResult::createWinByOther(
            $round->getPlayerList()->toArray(),
            $this->getActPlayer(),
            $round->getWinResult($this->getActor()),
            $context->tempGetCurrentPlayer(),
            $round->getAreas()->getReachPoints() / 1000,
            $round->getAreas()->getDealerArea()->getSeatWindTurn());
        $round->toNextPhase(
            new OverPhaseState($result)
        );
    }
}