<?php
namespace Saki\Command;

abstract class PlayerCommand extends Command {
    // todo constructor validate?
    function getActor() {
//        return new SeatWind($this->getParam(0));
        return $this->getParam(0);
    }

    function getActPlayer() { // todo remove
        return $this->getContext()->getActorArea()->getPlayer();
    }

    //region override Command
    function getContext() {
        $context = parent::getContext();
        $context->bindActor($this->getActor());
        return $context;
    }

    protected function executableImpl(CommandContext $context) {
        return $this->matchPhase($context)
        && $this->matchActor($context)
        && $this->matchOther($context);
    }

    function execute() {
        parent::execute();
        $this->getContext()->unbindActor();
    }
    //endregion

    //region subclass hooks
    abstract protected function matchPhase(CommandContext $context);

    abstract protected function matchActor(CommandContext $context);

    abstract protected function matchOther(CommandContext $context);
    //endregion
}