<?php
namespace Saki\FinalScore;

class CompositeFinalScoreStrategy extends FinalScoreStrategy {
    private $strategies;

    /**
     * CompositeFinalScoreStrategy constructor.
     * @param FinalScoreStrategy[] $strategies
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

    function getScoreDelta(FinalScoreStrategyTarget $target, $player) {
        $sum = 0;
        foreach ($this->getStrategies() as $strategy) {
            $sum += $strategy->getScoreDelta($target, $player);
        }
        return $sum;
    }
}