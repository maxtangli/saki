<?php
namespace Saki\Command\Debug;

use Saki\Command\Command;
use Saki\Command\CommandContext;

class PassAllCommand extends Command {
    static function getParamDeclarations() {
        return [];
    }

    function __construct(CommandContext $context) {
        parent::__construct($context, []);
    }

    function executable() {
        return $this->getContext()->getRoundData()->getPhaseState()->getRoundPhase()->isPublic();
    }

    function executeImpl() {
        $this->getContext()->getRoundData()->toNextPhase();
    }
}