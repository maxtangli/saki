<?php

namespace Saki\Play;
use Saki\Util\Utils;

/**
 * @package Saki\Play
 */
class Roomer implements UserProxy {
    private $userProxy;
    private $room;
    private $roomState;
    private $seat;

    /**
     * @param Room $room
     * @param UserProxy $userProxy
     */
    function __construct(UserProxy $userProxy, Room $room) {
        $this->userProxy = $userProxy;
        $this->room = $room;
        $this->roomState = RoomState::create(RoomState::NULL);
        $this->seat = null;
    }

    /**
     * @return string
     */
    function __toString() {
        return sprintf('roomer[%s]', $this->getUserProxy()->getId());
    }

    /**
     * @return Room
     */
    function getRoom() {
        return $this->room;
    }

    /**
     * @return RoomState
     */
    function getRoomState() {
        return $this->roomState;
    }

    /**
     * @return UserProxy
     */
    function getUserProxy() {
        return $this->userProxy;
    }

    /**
     * @param UserProxy $userProxy
     */
    function setUserProxy(UserProxy $userProxy) {
        if (!$this->isUserProxy($userProxy)) {
            throw new \InvalidArgumentException();
        }
        $this->userProxy = $userProxy;
    }

    /**
     * @param UserProxy $userProxy
     * @return bool
     */
    function isUserProxy(UserProxy $userProxy) {
        return $this->getUserProxy()->getId() == $userProxy->getId();
    }

    //region UserProxy impl
    function getId() {
        return $this->getUserProxy()->getId();
    }

    function send(Response $response) {
        return $this->getUserProxy()->send($response);
    }

    //endregion

    /**
     * @return Seat
     */
    function getSeat() {
        if ($this->getRoomState()->isPlaying()) {
            if (!isset($this->seat)) {
                throw new \LogicException();
            }
            return $this->seat;
        }
        throw new \InvalidArgumentException();
    }

    /**
     * @return $this
     */
    function join() {
        if (!$this->getRoomState()->isNull()) {
            throw new \InvalidArgumentException();
        }

        $this->getRoom()->getRoomerList()->insertLast($this);
        $this->roomState = RoomState::create(RoomState::UNAUTHORIZED);
        $this->getUserProxy()->send(Response::createOk());
        return $this;
    }

    /**
     * @return $this
     */
    function leave() {
        $roomState = $this->getRoomState();
        if (!$roomState->isInRoom()) {
            throw new \InvalidArgumentException();
        }

        if ($roomState->isPlaying()) {
            $disconnectedUser = new DisconnectedUser($this->getId());
            $this->setUserProxy($disconnectedUser);
            return $this;
        }

        if ($roomState->isMatching()) {
            $tableMatcher = $this->getRoom()->getTableMatcher();
            $tableMatcher->matchOff($this);
        }
        $this->getRoom()->getRoomerList()->remove($this);
        $this->roomState = RoomState::create(RoomState::NULL);
        $this->getUserProxy()->send(Response::createOk());
        return $this;
    }

    /**
     * @return $this
     */
    function authorize() {
        if (!$this->getRoomState()->isUnauthorized()) {
            throw new \InvalidArgumentException();
        }

        $this->roomState = RoomState::create(RoomState::IDLE);
        $this->getUserProxy()->send(Response::createOk());
        return $this;
    }

    /**
     * @return $this
     */
    function matchingOn() {
        if (!$this->getRoomState()->isIdle()) {
            throw new \InvalidArgumentException();
        }

        $tableMatcher = $this->getRoom()->getTableMatcher();

        $tableMatcher->matchOn($this);
        $this->roomState = RoomState::create(RoomState::MATCHING);
        $this->getUserProxy()->send(Response::createOk());

        $tableOrFalse = $tableMatcher->tryMatching();
        if ($tableOrFalse instanceof Table) {
            $table = $tableOrFalse;

            $playOn = function (Roomer $roomer) use ($table) {
                $roomer->playOn($table);
            };
            $table->callAll($playOn);

            $table->notifyAll();
        }
        return $this;
    }

    /**
     * @return $this
     */
    function matchingOff() {
        if (!$this->getRoomState()->isMatching()) {
            throw new \InvalidArgumentException();
        }

        $tableMatcher = $this->getRoom()->getTableMatcher();

        $tableMatcher->matchOff($this);
        $this->roomState = RoomState::create(RoomState::IDLE);
        $this->getUserProxy()->send(Response::createOk());
        return $this;
    }

    /**
     * @param Table $table
     * @return $this
     */
    function playOn(Table $table) {
        if (!$this->getRoomState()->isMatching()) {
            throw new \InvalidArgumentException();
        }

        $seat = $table->getSeat($this);
        $this->seat = $seat;
        $this->roomState = RoomState::create(RoomState::PLAYING);
        return $this;
    }

    /**
     * @param string $command
     * @return $this
     */
    function play(string $command) {
        if (!$this->getRoomState()->isPlaying()) {
            throw new \InvalidArgumentException();
        }

        $seat = $this->getSeat();
        $seat->tryExecute($command);

        if ($seat->getTable()->isGameOver()) {
            $seat->getTable()->callAll(Utils::getMethodCallback('gameOver'));
        }

        return $this;
    }

    /**
     * @return $this
     */
    function gameOver() {
        if (!$this->getRoomState()->isPlaying()) {
            throw new \InvalidArgumentException();
        }

        if (!$this->getSeat()->getTable()->isGameOver()) {
            throw new \InvalidArgumentException();
        }

        $this->roomState = RoomState::create(RoomState::IDLE);
        $this->seat = null;
        if ($this->getUserProxy() instanceof DisconnectedUser) {
            $this->leave();
        }

        return $this;
    }
}