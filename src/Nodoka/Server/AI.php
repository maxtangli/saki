<?php

namespace Nodoka\Server;

use Saki\Command\Command;
use Saki\Command\PrivateCommand\DiscardCommand;
use Saki\Command\PublicCommand\PassCommand;
use Saki\Play\Participant;
use Saki\Play\Play;
use Saki\Util\ArrayList;
use Saki\Util\Singleton;
use Saki\Util\Utils;

class AI extends Singleton {
    function tryAI(Play $play) {
        $playerParticipantList = $play->getParticipantList(null, true, true);
        if ($this->isAllAI($playerParticipantList)) {
            return false;
        }

        $round = $play->getRound();
        if (!$round->getPhase()->isPrivateOrPublic()) {
            return false;
        }

        $currentActor = $round->getCurrentSeatWind();
        $commandProvided = $round->getProcessor()->getProvider()->provideAll();
        // if private actor is AI, return random DiscardCommand
        if ($round->getPhase()->isPrivate()) {
            /** @var Participant $currentParticipant */
            $currentParticipant = $play->getParticipantList($currentActor, true, true)
                ->getSingle();
            if (!$this->isAI($currentParticipant)) {
                return false;
            }

            /** @var Command $discard */
            $randomDiscard = $commandProvided
                ->getActorProvided($currentActor, DiscardCommand::class)
                ->getRandom();
            return [$currentParticipant->getUserKey(), $randomDiscard];
        }

        // if any public actor is AI, return its PassCommand
        if ($round->getPhase()->isPublic()) {
            $publicParticipantList = $play->getParticipantList($currentActor, false, true);
            /** @var Participant $publicParticipant */
            foreach ($publicParticipantList as $publicParticipant) {
                if ($this->isAI($publicParticipant)) {
                    $actor = $publicParticipant->getRole()->getViewer();
                    $commandList = $commandProvided->getActorProvided($actor);
                    $isPass = Utils::toClassPredicate(PassCommand::class);
                    $passCommand = $commandList->getSingleOrDefault($isPass, false);
                    if ($passCommand !== false) {
                        return [$publicParticipant->getUserKey(), $passCommand];
                    }
                }
            }

            return false;
        }

        throw new \LogicException();
    }

    function isAllAI(ArrayList $participantList) {
        return $participantList->all([$this, 'isAI']);
    }

    function isAI(Participant $participant) {
        /** @var User $user */
        $user = $participant->getUserKey();
        return $user->getConnection() instanceof NullClient;
    }
}