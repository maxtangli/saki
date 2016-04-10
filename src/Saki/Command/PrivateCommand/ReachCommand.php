<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SelfWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Tile\Tile;

class ReachCommand extends PrivateCommand {
    static function getParamDeclarations() {
        return [SelfWindParamDeclaration::class, TileParamDeclaration::class];
    }

    function __construct(CommandContext $context, Tile $playerSelfWind, Tile $tile) {
        parent::__construct($context, [$playerSelfWind, $tile]);
    }

    /**
     * @return Tile
     */
    function getTile() {
        return $this->getParam(1);
    }

    function matchOtherConditions() {
        // todo not accurate now

        // assert waiting after discard
        $analyzer = $this->getContext()->getRound()->getWinAnalyzer()->getWaitingAnalyzer();
        $handList = $this->getActPlayer()->getTileArea()->getHand()->getPrivate();
        $futureWaitingList = $analyzer->analyzePrivate($handList, $this->getActPlayer()->getTileArea()->getHand()->getDeclare());
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