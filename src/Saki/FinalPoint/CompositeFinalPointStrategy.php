<?php
namespace Saki\FinalPoint;

class CompositeFinalPointStrategy extends FinalPointStrategy {
    private $strategies;

    /**
     * CompositeFinalPointStrategy constructor.
     * @param FinalPointStrategy[] $strategies
     */
    function __construct(array $strategies) {
        $this->strategies = $strategies;
    }

    function __toString() {
        $tokens = array_map(function ($s) {
            return "[%s]";
        }, $this->getStrategies());
        return sprintf('composite[%s]', implode(',', $tokens));
    }

    function getStrategies() {
        return $this->strategies;
    }

    function getPointDelta(FinalPointStrategyTarget $target, $player) {
        $sum = 0;
        foreach ($this->getStrategies() as $strategy) {
            $sum += $strategy->getPointDelta($target, $player);
        }
        return $sum;
    }
}