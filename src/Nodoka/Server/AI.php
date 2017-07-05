<?php

namespace Nodoka\Server;

use Saki\Command\PublicCommand\PassCommand;
use Saki\Play\Participant;
use Saki\Play\Play;
use Saki\Util\ArrayList;
use Saki\Util\Singleton;
use Saki\Util\Utils;

class AI extends Singleton {
    function tryAI(Play $play) {
        $playerParticipantList = $play->getParticipantList();
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
            $currentParticipant = $play->getCurrentParticipant();
            if (!$this->isAI($currentParticipant)) {
                return false;
            }

            $targetTile = $round->getArea($currentActor)->getHand()->getTarget()->getTile();
            $commandLine = "discard {$currentActor} {$targetTile}";
            $discardTarget = $round->getProcessor()->getParser()->parseLine($commandLine);

            if (!$commandProvided->getActorProvided($currentActor)->valueExist($discardTarget)) {
                throw new \LogicException();
            }

            return [$currentParticipant->getUserKey(), $discardTarget];
        }

        // if any public actor is AI, return its PassCommand
        if ($round->getPhase()->isPublic()) {
            $publicParticipantList = $play->getNotCurrentParticipantList();
            /** @var Participant $publicParticipant */
            foreach ($publicParticipantList as $publicParticipant) {
                if ($this->isAI($publicParticipant)) {
                    $actor = $publicParticipant->getRole()->getActor();
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