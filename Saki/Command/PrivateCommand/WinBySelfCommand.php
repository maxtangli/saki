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
        $round = $this->getContext()->getRoundData();

        $result = WinRoundResult::createWinBySelf(
            $round->getPlayerList()->toArray(),
            $this->getActPlayer(),
            $round->getWinResult($this->getActPlayer()),
            $round->getTileAreas()->getAccumulatedReachCount(),
            $round->getRoundWindData()->getSelfWindTurn()
        );
        $round->toNextPhase(new OverPhaseState($result));
    }
}