<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Game\SeatWind;
use Saki\Tile\Tile;

class RiichiCommand extends PrivateCommand {
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, TileParamDeclaration::class];
    }

    function __construct(CommandContext $context, SeatWind $playerSeatWind, Tile $tile) {
        parent::__construct($context, [$playerSeatWind, $tile]);
    }

    /**
     * @return Tile
     */
    function getTile() {
        return $this->getParam(1);
    }

    protected function matchOther(CommandContext $context) {
        // todo not accurate now

        // assert waiting after discard
        $winAnalyzer = $context->getRound()->getGameData()->getWinAnalyzer();
        $analyzer = $winAnalyzer->getWaitingAnalyzer();
        $actorHand = $context->getActorHand();
        $futureWaitingList = $analyzer->analyzePrivate($actorHand->getPrivate(), $actorHand->getDeclare());
        $isWaiting = $futureWaitingList->count() > 0;
        if (!$isWaiting) {
            return false;
        }

        $isValidTile = $futureWaitingList->discardExist($this->getTile());
        if (!$isValidTile) {
            return false;
        }

        return true;
    }

    protected function executeImpl(CommandContext $context) {
        $context->getActorArea()->riichi($this->getTile());
        $context->getRound()->toNextPhase();
    }
}