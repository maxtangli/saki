<?php

namespace Nodoka\Server;

use Saki\Play\Play;

/**
 * @package Nodoka\Server
 */
class Room {
    private $matchingUserList;
    private $playList;
    private $shuffleMatching;

    function __construct() {
        $this->matchingUserList = [];
        $this->playList = [];
        $this->shuffleMatching = true;
    }

    /**
     * @return bool
     */
    function isShuffleMatching() {
        return $this->shuffleMatching;
    }

    /**
     * @param bool $shuffleMatching
     */
    function setShuffleMatching(bool $shuffleMatching) {
        $this->shuffleMatching = $shuffleMatching;
    }

    /**
     * @param User $user
     */
    function joinMatching(User $user) {
        if ($this->isPlaying($user)) {
            throw new \InvalidArgumentException('is playing.');
        }

        $this->matchingUserList[$user->getId()] = $user;
    }

    /**
     * @param User $user
     */
    function leaveMatching(User $user) {
        if ($this->isPlaying($user)) {
            throw new \InvalidArgumentException('is playing.');
        }

        // nothing happens if $user not exist
        unset($this->matchingUserList[$user->getId()]);
    }

    /**
     * @return Play|false
     */
    function doMatching() {
        if (count($this->matchingUserList) >= 4) {
            /** @var User[] $users */
            $users = array_splice($this->matchingUserList, 0, 4);
            if ($this->isShuffleMatching()) {
                shuffle($users);
            }
            $play = new Play($users);

            foreach ($users as $user) {
                $this->playList[$user->getId()] = $play;
            }

            return $play;
        }

        return false;
    }

    /**
     * @param User $user
     * @return bool
     */
    function isIdle(User $user) {
        return !$this->isPlaying($user) && !$this->isMatching($user);
    }

    /**
     * @param User $user
     * @return bool
     */
    function isMatching(User $user) {
        return isset($this->matchingUserList[$user->getId()]);
    }

    /**
     * @param User $user
     * @return bool
     */
    function isPlaying(User $user) {
        return isset($this->playList[$user->getId()]);
    }

    /**
     * @param User $user
     * @return Play
     */
    function getPlay(User $user) {
        if (!$this->isPlaying($user)) {
            throw new \InvalidArgumentException('not playing.');
        }
        return $this->playList[$user->getId()];
    }

    /**
     * @param $userId
     * @return User
     */
    function getPlayingUser($userId) {
        if (!isset($this->playList[$userId])) {
            throw new \InvalidArgumentException();
        }

        /** @var Play $play */
        $play = $this->playList[$userId];
        /** @var User $user */
        foreach ($play->getUserKeys() as $user) {
            if ($user->getId() == $userId) {
                return $user;
            }
        }

        throw new \InvalidArgumentException();
    }

    /**
     * @param Play $play
     */
    function finishPlay(Play $play) {
        /** @var User $user */
        foreach ($play->getUserKeys() as $user) {
            unset($this->playList[$user->getId()]);
        }
    }
}