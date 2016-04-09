<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SelfWindParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Game\PlayerWind;
use Saki\Game\RoundTurn;
use Saki\RoundPhase\OverPhaseState;
use Saki\RoundResult\OnTheWayDrawRoundResult;
use Saki\RoundResult\RoundResultType;
use Saki\Tile\Tile;

class NineNineDrawCommand extends PrivateCommand {
    static function getParamDeclarations() {
        return [SelfWindParamDeclaration::class];
    }

    function __construct(CommandContext $context, Tile $playerSelfWind) {
        parent::__construct($context, [$playerSelfWind]);
    }

    function matchOtherConditions() {
        $areas = $this->getContext()->getRound()->getTileAreas();
        $currentTurn = $this->getContext()->getRound()->getTurnManager()->getGlobalTurn();

        $isFirstTurn = $currentTurn == 1;
        $noDeclaredActions = !$areas->getDeclareHistory()->hasDeclare(
            new RoundTurn($currentTurn, PlayerWind::createEast())
        );
        $validTileList = $this->getActPlayer()->getTileArea()->getHand()->getPrivate()->isNineKindsOfTerminalOrHonor();
        return $isFirstTurn && $noDeclaredActions && $validTileList;
    }

    function executeImpl() {
        $result = new OnTheWayDrawRoundResult($this->getContext()->getRound()->getPlayerList()->toArray(),
            RoundResultType::create(RoundResultType::NINE_KINDS_OF_TERMINAL_OR_HONOR_DRAW));
        $this->getContext()->getRound()->toNextPhase(new OverPhaseState($result));
    }
}