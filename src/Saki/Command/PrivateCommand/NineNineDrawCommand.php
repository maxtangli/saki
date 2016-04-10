<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Game\SeatWind;
use Saki\Game\Turn;
use Saki\Phase\OverPhaseState;
use Saki\RoundResult\OnTheWayDrawRoundResult;
use Saki\RoundResult\RoundResultType;
use Saki\Tile\Tile;

class NineNineDrawCommand extends PrivateCommand {
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class];
    }

    function __construct(CommandContext $context, Tile $playerSeatWind) {
        parent::__construct($context, [$playerSeatWind]);
    }

    function matchOther() {
        $areas = $this->getContext()->getRound()->getAreas();
        $currentCircleCount = $this->getContext()->getRound()->getTurnManager()->getCurrentTurn()->getCircleCount();

        $isFirstTurn = $currentCircleCount == 1;
        $noDeclaredActions = !$areas->getDeclareHistory()->hasDeclare(
            new Turn($currentCircleCount, SeatWind::createEast())
        );
        $validTileList = $this->getActPlayer()->getArea()->getHand()->getPrivate()->isNineKindsOfTerminalOrHonor();
        return $isFirstTurn && $noDeclaredActions && $validTileList;
    }

    function executeImpl() {
        $result = new OnTheWayDrawRoundResult($this->getContext()->getRound()->getPlayerList()->toArray(),
            RoundResultType::create(RoundResultType::NINE_KINDS_OF_TERMINAL_OR_HONOR_DRAW));
        $this->getContext()->getRound()->toNextPhase(new OverPhaseState($result));
    }
}