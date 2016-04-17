<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Game\SeatWind;
use Saki\Phase\OverPhaseState;
use Saki\Win\Result\WinResult;

class WinBySelfCommand extends PrivateCommand {
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class];
    }

    function __construct(CommandContext $context, SeatWind $playerSeatWind) {
        parent::__construct($context, [$playerSeatWind]);
    }

    protected function matchOther(CommandContext $context) {
        // todo
        return true;
    }

    protected function executeImpl(CommandContext $context) {
        $round = $context->getRound();

        $result = WinResult::createWinBySelf(
            $round->getPlayerList()->toArray(),
            $this->getActPlayer(),
            $round->getWinReport($this->getActor()),
            $round->getAreas()->getReachPoints() / 1000,
            $round->getAreas()->getDealerArea()->getSeatWindTurn()
        );
        $round->toNextPhase(new OverPhaseState($result));
    }
}