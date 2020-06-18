<?php
require_once 'Figure.php';


class Pawn extends Figure
{
    /**
     * @var string
     * capture, initial or ordinar + color of pawn
     */
    public $moveType;

    /**
     * Pawn constructor.
     * @param $position
     * @param $color
     */
    public function __construct($position, $color)
    {
        parent::__construct($position, $color);
        $this->allowedDistances = [
            'initial_W' => [[0, 1], [0, 2]],
            'initial_b' => [[0, -1], [0, -2]],
            'ordinar_W' => [[0, 1]],
            'ordinar_b' => [[0, -1]],
            'capture_W' => [[-1, 1], [1, 1]],
            'capture_b' => [[-1, -1], [1, -1]]];
        $this->shortname = $color == 'W' ? 'P' : 'p';
        $this->moveType = 'initial';
    }

    /**
     * @param $distances
     * @return bool
     * modifies parent function and adapts it for the pawn
     */
    public function isValidDistance($distances)
    {
        if (in_array($distances, $this->allowedDistances[$this->moveType . '_' . $this->color])) {
            return true;
        }
        return false;
    }


}