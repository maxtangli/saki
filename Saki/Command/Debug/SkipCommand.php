<?php
namespace Saki\Command\Debug;

use Saki\Command\Command;
use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\IntParamDeclaration;

class SkipCommand extends Command {
    static function getParamDeclarations() {
        return [IntParamDeclaration::class];
    }

    function __construct(CommandContext $context, int $skipCount) {
        parent::__construct($context, [$skipCount]);
    }

    function getSkipCount() {
        return $this->getParam(0);
    }

    function executable() {
        $r = $this->getContext()->getRound();
        return $r->getPhaseState()->getRoundPhase()->isPrivate();
    }

    function executeImpl() {
        $r = $this->getContext()->getRound();
        $pro = $r->getProcessor();
        $nTodo = $this->getSkipCount();
        while($nTodo-- > 0) {
            // todo handle game over
            $pro->process('discard I I:s-C:C; passAll');
        }
    }
}