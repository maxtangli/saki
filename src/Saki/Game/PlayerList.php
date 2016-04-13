<?php
namespace Saki\Game;

use Saki\Util\ArrayList;
use Saki\Util\ReadonlyArrayList;
use Saki\Util\Utils;

class PlayerList extends ArrayList {
    use ReadonlyArrayList;

    static function createStandard() {
        return new PlayerList(4, 25000);
    }

    /**
     * @param int $n
     * @param int $initialPoint
     */
    function __construct(int $n, int $initialPoint) {
        if (!Utils::inRange($n, 1, 4)) {
            throw new \InvalidArgumentException();
        }

        $data = [
            [1, $initialPoint, SeatWind::fromString('E')],
            [2, $initialPoint, SeatWind::fromString('S')],
            [3, $initialPoint, SeatWind::fromString('W')],
            [4, $initialPoint, SeatWind::fromString('N')],
        ];
        $players = array_map(function ($v) {
            return new Player($v[0], $v[1], $v[2]);
        }, array_slice($data, 0, $n));

        parent::__construct($players);
    }
}