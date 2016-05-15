<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Game\SeatWind;
use Saki\Phase\OverPhaseState;
use Saki\Win\Result\WinResult;
use Saki\Win\Result\WinResultInput;

class TsumoCommand extends PrivateCommand {
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
        $actor = $this->getActor();
        $areas = $round->getAreas();

        $result = new WinResult(WinResultInput::createTsumo(
            [$actor, $round->getWinReport($actor)->getFanAndFu()],
            $areas->getOtherSeatWinds([$actor]),
            $areas->getRiichiHolder()->getRiichiPoints(),
            $areas->getDealerArea()->getSeatWindTurn()
        ));
        $round->toNextPhase(new OverPhaseState($result));
    }
}