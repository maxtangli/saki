<?php
namespace Saki\Command\PublicCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Command\PublicCommand;
use Saki\Game\SeatWind;
use Saki\Phase\OverPhaseState;
use Saki\Win\Result\WinResult;
use Saki\Win\Result\WinResultInput;

class RonCommand extends PublicCommand {
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
        $actor = $this->getActor();
        $current = $context->getCurrentSeatWind();
        $areas = $context->getAreas();

        $result = new WinResult(WinResultInput::createRon(
            [[$actor, $round->getWinReport($actor)->getFanAndFu()]],
            $current,
            $areas->getOtherSeatWinds([$actor, $current]),
            $areas->getRiichiHolder()->getRiichiPoints(),
            $areas->getDealerArea()->getSeatWindTurn()
        ));
        $round->toNextPhase(
            new OverPhaseState($result)
        );
    }
}