<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Tile\Tile;

class ReachCommand extends PrivateCommand {
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, TileParamDeclaration::class];
    }

    function __construct(CommandContext $context, Tile $playerSeatWind, Tile $tile) {
        parent::__construct($context, [$playerSeatWind, $tile]);
    }

    /**
     * @return Tile
     */
    function getTile() {
        return $this->getParam(1);
    }

    function matchOther() {
        // todo not accurate now

        // assert waiting after discard
        $analyzer = $this->getContext()->getRound()->getWinAnalyzer()->getWaitingAnalyzer();
        $handList = $this->getActPlayer()->getArea()->getHand()->getPrivate();
        $futureWaitingList = $analyzer->analyzePrivate($handList, $this->getActPlayer()->getArea()->getHand()->getDeclare());
        $isWaiting = $futureWaitingList->count() > 0;
        if (!$isWaiting) {
            return false;
//            throw new \InvalidArgumentException('Reach condition violated: is waiting.');
        }

        $isValidTile = $futureWaitingList->isForWaitingDiscardedTile($this->getTile());
        if (!$isValidTile) {
            return false;
//            throw new \InvalidArgumentException(
//                sprintf('Reach condition violated: invalid discard tile [%s].', $selfTile)
//            );
        }

        return true;
    }

    function executeImpl() {
        $this->getContext()->getRound()->getAreas()->reach($this->getActPlayer(), $this->getTile());
        $this->getContext()->getRound()->toNextPhase();
    }
}