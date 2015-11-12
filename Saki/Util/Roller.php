<?php
namespace Saki\Util;

class Roller {
    // immutable
    private $targets;

    // impl, mutable
    private $targetList; // initialIndex is kept to be 0
    private $currentIndex;
    private $globalTurn; // first turn is 1
    private $localTurns;

    function __construct(array $targets, $initialTarget = null) {
        if (empty($targets)) {
            throw new \InvalidArgumentException();
        }

        $this->targets = $targets;
        $this->targetList = new ArrayLikeObject($targets);

        $initialTarget = $initialTarget !== null ? $initialTarget : $this->targetList[0];
        $this->reset($initialTarget);
    }

    function reset($initialTarget) {
        $initialIndex = $this->targetList->valueToIndex($initialTarget);  // validate

        $this->targetList->leftShift($initialIndex);
        $this->currentIndex = 0;
        $this->globalTurn = 1;
        $this->localTurns = $this->targetList->toArray(function () {
            return 0;
        });
        $this->localTurns[0] = 1; // todo simplify
    }

    function debugSet($currentTarget, $globalTurn) {
        $currentIndex = $this->targetList->valueToIndex($currentTarget);

        $this->currentIndex = $currentIndex;
        $this->globalTurn = $globalTurn;
        $this->localTurns = $this->targetList->toArray(function () {
            return 0;
        });
        $this->localTurns[0] = 1;
    }

    function getTargetsCount() {
        return $this->targetList->count();
    }

    function getInitialTarget() {
        return $this->targetList[0];
    }

    function getCurrentTarget() {
        return $this->targetList[$this->currentIndex];
    }

    function getOffsetTarget($offset, $baseTarget = null) {
        $baseIndex = $baseTarget !== null ? $this->targetList->valueToIndex($baseTarget) // validate
            : $this->currentIndex;

        $targetIndex = Utils::getNormalizedModValue($baseIndex + $offset, $this->getTargetsCount());
        return $this->targetList[$targetIndex];
    }

    function getGlobalTurn() {
        return $this->globalTurn;
    }

    function getTargetLocalTurn($target) {
        $i = $this->targetList->valueToIndex($target); // validate
        return $this->localTurns[$i];
    }

    function toTarget($target) {
        $targetIndex = $this->targetList->valueToIndex($target); // validate

        if ($targetIndex == $this->currentIndex) {
            throw new \InvalidArgumentException('target should not be same with current. Logic maybe confusing.');
        }

        $isNextTurn = $targetIndex < $this->currentIndex;
        $this->currentIndex = $targetIndex;
        if ($isNextTurn) {
            ++$this->globalTurn;
        }
        ++$this->localTurns[$targetIndex];
    }

    // convenient methods
}