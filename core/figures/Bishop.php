<?php
require_once 'Figure.php';

class Bishop extends Figure
{
    /**
     * Bishop constructor.
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
        }
        $this->allowedDistances = $moves;
        $this->shortname = $color == 'W' ? 'B' : 'b';
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