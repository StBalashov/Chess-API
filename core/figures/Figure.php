<?php


class Figure
{
    /**
     * @var array
     * keeps an array of allowed distances for a figure in [x_dist, y_dist] format
     */
    public $allowedDistances;

    /**
     * @var string
     * position of the figure on desk
     */
    public $position;

    /**
     * @var string
     */
    public $color;

    /**
     * @var string
     * shortname in FEN
     */
    public $shortname;

    /**
     * Figure constructor.
     * @param $position
     * @param $color
     */
    public function __construct($position, $color)
    {
        $this->position = $position;
        $this->color = $color;
    }

    /**
     * @param $distances
     * @return bool
     * checks if given distance is valid for a figure
     */
    public function isValidDistance($distances)
    {
        if (in_array($distances, $this->allowedDistances)
            or in_array([-1 * $distances[0], -1 * $distances[1]], $this->allowedDistances)) {
            return true;
        }
        return false;
    }

}