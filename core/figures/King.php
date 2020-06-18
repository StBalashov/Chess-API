<?php
require_once 'Figure.php';

class King extends Figure
{
    /**
     * @var bool
     */
    public $hasMoved;

    /**
     * @var string[]
     */
    public $castlingMoves;

    /**
     * King constructor.
     * @param $position
     * @param $color
     */
    public function __construct($position, $color)
    {
        parent::__construct($position, $color);
        $this->allowedDistances = [[0, 1], [1, 1], [1, 0], [-1, 1]];
        $this->hasMoved = false;
        $this->shortname = $color == 'W' ? 'K' : 'k';
        if ($color == 'W') {
            $this->castlingMoves = [1 => 'g1', -1 => 'c1'];
        } else {
            $this->castlingMoves = [1 => 'g8', -1 => 'c8'];
        }
    }

    /**
     * @param $game
     * @return bool
     * checks if this king is checked in a given game state.
     */
    public function isChecked($game)
    {
        $possible_game = clone $game;
        $desk = $possible_game->desk;
        $figures = $desk->figures;
        foreach ($figures as $figure) {
            if ($figure->color == $this->color) {
                continue;
            }
            try {
                $potential_move = new Move($figure->position . ':' . $this->position, $possible_game);
                if ($potential_move->isValid($possible_game, true)) {
                    return true;
                }
            } catch (Exception $e) {
                continue;
            }
        }
        return false;
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