<?php
require_once 'Desk.php';


class Game
{

    /** @var Desk */
    public $desk;

    /** @var string  */
    public $turn;

    /** @var string
     * keeps the winner
     */
    public $isOver;

    /** @var string
     * castling posibilities (FEN)
     */
    public $castling;

    /** @var string
     * en passant possible move (FEN)
     */
    public $en_passant;

    /** @var string
     * A shortname of the figure that user chooses to replace with
     */
    public $promotionFigure;

    /** @var Pawn */
    public $promotionPawn;

    /** @var int */
    public $moveCount;

    /** @var array
     * An array with two King objects
     */
    public $kings;


    /**
     * Game constructor.
     */
    public function __construct()
    {
        $this->desk = new Desk();
        $this->turn = 'W';
        $this->isOver = '';
        $this->castling = 'KQkq';
        $this->en_passant = '-';
        $this->promotionFigure = '';
        $this->promotionPawn = null;
        $this->moveCount = 0;
        $this->kings = ['W' => $this->desk->fields['e1'], 'b' => $this->desk->fields['e8']];
    }

    /**
     * Clone constructor
     */
    public function __clone()
    {
        $this->desk = clone $this->desk;
        $this->kings['W'] = clone $this->kings['W'];
        $this->kings['b'] = clone $this->kings['b'];
    }

    /**
     * @param $color
     * @return string
     */
    public function swapColor($color)
    {
        return $color == 'W' ? 'b' : 'W';
    }

    /**
     * @return string
     * produces a FEN representation of the game
     */
    public function getFen()
    {
        $fen = $this->desk->generateFen();
        $fen .= ' ' . strtolower($this->turn) . ' ' . $this->castling . ' ' . $this->en_passant;
        return $fen;
    }

    /**
     * @param $move
     */
    public function updateCastling(&$move)
    {
        if ($move->figureType == 'k') {
            $move->figure->hasMoved = true;
            if ($move->figure->color == 'W') {
                $this->castling[0] = '-';
                $this->castling[1] = '-';
            } else {
                $this->castling[2] = '-';
                $this->castling[3] = '-';
            }
        }
        if ($move->figureType == 'r') {
            $move->figure->hasMoved = true;
            if ($move->figure->color == 'W') {
                if ($move->from[0] == 'a') {
                    $this->castling[1] = '-';
                } else {
                    $this->castling[0] = '-';
                }
            } else {
                if ($move->from[0] == 'a') {
                    $this->castling[3] = '-';
                } else {
                    $this->castling[2] = '-';
                }
            }
        }
    }

    /**
     * @param $move
     */
    public function updateEn_passant($move)
    {
        if ($move->figureType != 'p') {
            $this->en_passant = '-';
        } elseif (abs($move->y_distance) != 2) {
            $this->en_passant = '-';
        } else {
            $this->en_passant = $move->from[0] . (string)((int)$move->from[1] + $move->y_distance / abs($move->y_distance));
        }
    }

    /**
     * @param $color
     * @return bool
     * @throws FieldFormatException checks if the game is over
     * @throws MoveException
     * checks if the game is over
     */
    public function isCheckMated($color)
    {
        if ($this->kings[$color]->isChecked($this)) {
            $possible_game = clone $this;
            $possible_game->turn = $this->swapColor($this->turn);
            $desk = $possible_game->desk;
            $figures = $desk->figures;
            foreach ($figures as $figure) {
                if ($color != $figure->color) {
                    continue;
                }
                foreach (array_keys($desk->fields) as $field_to) {
                    try {
                        $potential_move = new Move($figure->position . ':' . $field_to, $possible_game);
                    } catch (Exception $e) {
                        continue;
                    }
                    if ($potential_move->isValid($possible_game, false)) {
                        return false;
                    }
                }
            }
            return true;
        }
        return false;
    }

    /**
     produces pawn promotion on desk
     */
    public function promotePawn()
    {
        $this->turn = $this->swapColor($this->turn);
        $pawn = $this->promotionPawn;
        $newFigure = null;
        switch ($this->promotionFigure) {
            case 'q':
                $newFigure = new Queen($pawn->position, $pawn->color);
                break;
            case 'b':
                $newFigure = new Bishop($pawn->position, $pawn->color);
                break;
            case 'r':
                $newFigure = new Rook($pawn->position, $pawn->color, null);
                $newFigure->hasMoved = true;
                break;
            case 'n':
                $newFigure = new Knight($pawn->position, $pawn->color);
                break;
        }
        $this->desk->deleteFigure($pawn);
        $this->desk->fields[$newFigure->position] = $newFigure;
        $this->desk->figures[] = $newFigure;
        $this->promotionPawn = null;
        $this->promotionFigure = '';
        $this->turn = $this->swapColor($this->turn);
    }

    /**
     * @param $move
     */
    public function updateKings($move)
    {
        if ($move->figureType == 'k') {
            $this->kings[$move->figure->color] = $move->figure;
        }
    }

    /**
     * @param $move
     */
    public function updateGame($move)
    {
        $this->updateCastling($move);
        $this->updateEn_passant($move);
        $this->desk->updateFigure($move);
        $this->updateKings($move);
    }

    /**
     * @param $move
     * @return string
     * @throws FieldFormatException
     * checks if the move is valid, then makes it if it is
     * checks if the game is over
     * generally, makes a total chess move
     */
    public function makeMove(&$move)
    {
        if ($move->isValid($this, false)) {
            $this->desk->makeMove($move);
            $this->moveCount++;
            $this->updateGame($move);
            if ($this->isCheckMated($this->swapColor($this->turn))) {
                $this->isOver = ($this->turn == 'W' ? 'Whites ' : 'Blacks ') . 'won!';
            } else {
                $this->turn = $this->swapColor($this->turn);
            }
            return '';
        } else {
            return $move->failureMessage;
        }
    }
}