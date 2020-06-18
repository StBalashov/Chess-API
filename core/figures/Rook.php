<?php
require_once 'Figure.php';

class Rook extends Figure
{
    /**
     * @var bool
     */
    public $hasMoved;

    /**
     * @var string
     * possible castling move for a rook
     */
    public $castlingMove;

    /**
     * Rook constructor.
     * @param $position
     * @param $color
     * @param $castlingMove
     */
    public function __construct($position, $color, $castlingMove)
    {
        parent::__construct($position, $color);
        $moves = [];
        foreach (range(1, 7) as $item) {
            array_push($moves, [$item, 0]);
            array_push($moves, [0, $item]);
        }
        $this->allowedDistances = $moves;
        $this->hasMoved = false;
        $this->shortname = $color == 'W' ? 'R' : 'r';
        $this->castlingMove = $castlingMove;
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