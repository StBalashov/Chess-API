<?php
require_once 'Figure.php';


class Knight extends Figure
{
    /**
     * Knight constructor.
     * @param $position
     * @param $color
     */
    public function __construct($position, $color)
    {
        parent::__construct($position, $color);
        $this->allowedDistances = [[1, 2], [-1, 2], [2, 1], [-2, 1]];
        $this->shortname = $color == 'W' ? 'N' : 'n';
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