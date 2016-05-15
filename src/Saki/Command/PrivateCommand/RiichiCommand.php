<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Game\Riichi;
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
        // assert waiting after discard
        $winAnalyzer = $context->getRound()->getGameData()->getWinAnalyzer();
        $analyzer = $winAnalyzer->getWaitingAnalyzer();
        $actorHand = $context->getActorHand();
        $futureWaitingList = $analyzer->analyzePrivate($actorHand->getPrivate(), $actorHand->getMelded());
        $isWaiting = $futureWaitingList->count() > 0;
        if (!$isWaiting) {
            return false;
        }

        $isValidTile = $futureWaitingList->discardExist($this->getTile());
        if (!$isValidTile) {
            return false;
        }

        $riichi = new Riichi($this->getActor(), $this->getTile());
        return $riichi->valid($context->getActorArea());
    }

    protected function executeImpl(CommandContext $context) {
        $riichi = new Riichi($this->getActor(), $this->getTile());
        $riichi->apply($context->getActorArea());
        
        $context->getRound()->toNextPhase();
    }
}