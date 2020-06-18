<?php
require_once 'Figure.php';


class Queen extends Figure
{
    /**
     * Queen constructor.
     * @param $position
     * @param $color
     */
    public function __construct($position, $color)
    {
        parent::__construct($position, $color);
        $moves = [];
        foreach (range(1, 7) as $item) {
            array_push($moves, [$item, $item]);
            array_push($moves, [-1 * $item, $item]);
            array_push($moves, [$item, 0]);
            array_push($moves, [0, $item]);
        }
        $this->allowedDistances = $moves;
        $this->shortname = $color == 'W' ? 'Q' : 'q';
    }

    /**
     * @param $distances
     * @return bool
     */
    public function isValidDistance($distances)
    {
        return parent::isValidDistance($distances);
    }

}