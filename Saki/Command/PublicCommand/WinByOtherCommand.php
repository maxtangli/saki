<?php
namespace Saki\Command\PublicCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SelfWindParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Command\PublicCommand;
use Saki\RoundPhase\OverPhaseState;
use Saki\RoundResult\WinRoundResult;
use Saki\Tile\Tile;

class WinByOtherCommand extends PublicCommand {
    static function getParamDeclarations() {
        return [SelfWindParamDeclaration::class];
    }

    function __construct(CommandContext $context, Tile $playerSelfWind) {
        parent::__construct($context, [$playerSelfWind]);
    }

    function matchOtherConditions() {
        return true; // todo
    }

    function executeImpl() {
        $roundData = $this->getContext()->getRoundData();

        $result = WinRoundResult::createWinByOther(
            $roundData->getPlayerList()->toArray(),
            $this->getActPlayer(),
            $roundData->getWinResult($this->getActPlayer()),
            $this->getCurrentPlayer(),
            $roundData->getTileAreas()->getAccumulatedReachCount(),
            $roundData->getRoundWindData()->getSelfWindTurn());
        $roundData->toNextPhase(
            new OverPhaseState($result)
        );
    }
}