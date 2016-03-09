<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SelfWindParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\RoundPhase\OverPhaseState;
use Saki\RoundResult\WinRoundResult;
use Saki\Tile\Tile;

class WinBySelfCommand extends PrivateCommand {
    static function getParamDeclarations() {
        return [SelfWindParamDeclaration::class];
    }

    function __construct(CommandContext $context, Tile $playerSelfWind) {
        parent::__construct($context, [$playerSelfWind]);
    }

    function matchOtherConditions() {
        // todo
        return true;
    }

    function executeImpl() {
        $roundData = $this->getContext()->getRoundData();

        $result = WinRoundResult::createWinBySelf(
            $roundData->getPlayerList()->toArray(),
            $this->getActPlayer(),
            $roundData->getWinResult($this->getActPlayer()),
            $roundData->getTileAreas()->getAccumulatedReachCount(),
            $roundData->getRoundWindData()->getSelfWindTurn()
        );
        $roundData->toNextPhase(new OverPhaseState($result));
    }
}