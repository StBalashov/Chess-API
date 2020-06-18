<?php
foreach (glob("../exceptions/*.php") as $filename) {
    require_once $filename;
}


class Move
{
    /** @var mixed
     * class - some of Figure childs
     */
    public $figure;

    /** @var string
     * lowercase of the figure shortname.
     */
    public $figureType;

    /** @var string */
    public $from;

    /** @var string */
    public $to;

    /** @var int */
    public $x_distance;

    /** @var int */
    public $y_distance;

    /** @var bool */
    public $isPawnPromotion;

    /** @var string */
    public $failureMessage;


    /**
     * Move constructor.
     * @param $move_str
     * @param $game
     * @throws FieldFormatException
     * @throws MoveException
     */
    public function __construct($move_str, $game)
    {
        if (!preg_match('/[a-h][1-8]:[a-h][1-8]/', $move_str)) {
            throw new FieldFormatException('Wrong fields format. Cannot create Move.');
        }
        list($from, $to) = explode(':', $move_str);
        $this->from = $from;
        $this->to = $to;
        $this->x_distance = ord($this->to[0]) - ord($this->from[0]);
        $this->y_distance = ord($this->to[1]) - ord($this->from[1]);
        if ($game->desk->fields[$this->from]) {
            $this->figure = $game->desk->fields[$this->from];
            $this->figureType = strtolower($this->figure->shortname);
        } else {
            throw new MoveException('No figure at \'from\' field. Cannot create Move.');
        }
        $this->isPawnPromotion = false;
        $this->failureMessage = '';
    }

    /**
     * @param $game
     * @return string|null
     * checks if this move is a capture move
     */
    public function isCapture($game)
    {
        if ($this->to == $game->en_passant) {
            return 'En passant';
        } else {
            if ($game->desk->fields[$this->to]) {
                return 'yes';
            } else {
                return null;
            }
        }
    }

    /**
     * @param $game
     * @return bool
     * @throws FieldFormatException
     * @throws MoveException
     * validates the castling move
     */
    public function isValidCastling($game)
    {
        if (!$game->desk->fields[$this->to]) {
            // No figure at 'to' field
            $this->failureMessage = 'No figure at \'to\' field';
            return false;
        }
        if (strtolower($game->desk->fields[$this->to]->shortname) != 'r') {
            // Not a rook at 'to' field
            $this->failureMessage = 'Not a rook at \'to\' field';
            return false;
        }
        $rook = $game->desk->fields[$this->to];
        if ($this->figure->color != $rook->color) {
            // Not your Rook!!
            $this->failureMessage = 'Not your rook at \'to\' field';
            return false;
        }
        if ($this->figure->hasMoved or $rook->hasMoved) {
            // King or Rook has moved
            $this->failureMessage = 'King or Rook has moved. Castling is unavailable';
            return false;
        }
        if ($this->figure->isChecked($game)) {
            // King is checked
            $this->failureMessage = 'King is checked. Castling is unavailable';
            return false;
        }
        try {
            $rookMove = new Move($rook->castlingMove, $game);
        } catch (FieldFormatException $e) {
            throw $e;
        }
        $this->to = $this->figure->castlingMoves[$this->x_distance / abs($this->x_distance)];
        if (!($this->isPathClear($game) and $rookMove->isPathClear($game))) {
            // Path between King and Rook is not clear.
            $this->failureMessage = 'Path between King and Rook is not clear.';
            return false;

        }
        /*
         * Creates possible game and makes a move there to check if the king is checked after.
         * Not to affect the original game.
         */
        $possible_game = clone $game;
        $possible_game->desk->makeMove($this);
        $possible_game->turn = $possible_game->swapColor($possible_game->turn);
        if ($game->kings[$game->turn]->isChecked($possible_game)) {
            // Your king is under check after the move.
            $this->failureMessage = 'Your king is under check after the move.';
            return false;
        }
        $game->desk->makeMove($rookMove);
        return true;
    }

    /**
     * @return array
     * generates an array of field strings between from and to fields
     */
    public function generatePath()
    {
        if (abs($this->x_distance) == abs($this->y_distance)) {
            $path = [];
            $y = range((int)$this->from[1], (int)$this->to[1]);
            $x = range($this->from[0], $this->to[0]);
            for ($i = 0; $i < count($x); $i++) {
                array_push($path, $x[$i] . (string)$y[$i]);
            }
            return $path;
        } else {
            if ($this->x_distance == 0) {
                $y = range((int)$this->from[1], (int)$this->to[1]);
                array_walk($y, function (&$value, $key) {
                    $value = $this->from[0] . (string)$value;
                });
                return $y;
            } else {
                $x = range($this->from[0], $this->to[0]);
                array_walk($x, function (&$value, $key) {
                    $value = $value . $this->from[1];
                });
                return $x;
            }
        }
    }

    /**
     * @param $game
     * @return bool
     * checks if the path is clear and if the end of the path is the opponents figure or an empty field
     */
    public function isPathClear($game)
    {
        $desk = $game->desk;
        if ($this->figureType != 'n') {
            $path = $this->generatePath();
            array_pop($path);
            array_shift($path);
            foreach ($path as $field) {
                if ($desk->fields[$field]) {
                    // Some figure is on the path.
                    $this->failureMessage = 'Path is not clear.';
                    return false;
                }
            }
        }
        if (!$desk->fields[$this->to]) {
            return true;
        }
        if (ctype_lower($desk->fields[$this->to]->shortname) == ctype_lower($this->figure->shortname)) {
            // You're trying to capture your own figure.
            $this->failureMessage = 'You\'re trying to capture your own figure.';
            return false;
        }
        return true;
    }

    /**
     * @param $game
     * @param $possiblyChecked
     * @return bool
     * @throws FieldFormatException
     * @throws MoveException
     * main function that validates the move.
     * Divides move here to different types and calls additional check if necessary
     */
    public function isValid($game, $possiblyChecked)
    {
        if ($this->figure->color != $game->turn) {
            // Not your figure
            $this->failureMessage = 'Not your figure at \'from\' field';
            return false;
        }
        if (($this->figureType == 'k') and (abs($this->x_distance) >= 2)) {
            try {
                return $this->isValidCastling($game);
            } catch (FieldFormatException $e) {
                throw $e;
            }
        }
        if ($this->figureType == 'p') {
            if ($this->isCapture($game)) {
                $this->figure->moveType = 'capture';
            } elseif ($this->figure->moveType = 'capture') { // КОСТЫЛЬ!!!!
                $this->figure->moveType = 'initial';
            }
        }

        if (!$this->figure->isValidDistance([$this->x_distance, $this->y_distance])) {
            // Figure cannot move like that!
            $this->failureMessage = 'Figure cannot move like that!';
            return false;
        }
        if (!$this->isPathClear($game)) {
            return false;
        }
        if ($this->figureType == 'p') {
            if (($this->figure->color == 'W') and ($this->to[1] == '8')) {
                $this->isPawnPromotion = true;
            } elseif (($this->figure->color == 'b') and ($this->to[1] == '1')) {
                $this->isPawnPromotion = true;
            }
        }
        if (!$possiblyChecked) {
            $possible_game = clone $game;
            $possible_game->desk->makeMove($this);
            $possible_game->updateGame($this);
            $possible_game->turn = $possible_game->swapColor($possible_game->turn);
            if ($possible_game->kings[$game->turn]->isChecked($possible_game)) {
                // Your king is under check after the move.
                $this->failureMessage = 'Your king is under check after the move.';
                return false;
            }
        }
        /*
        Move is valid
        */
        if ($this->figureType == 'p') {
            $this->figure->moveType = 'ordinar';
        }
        if ($this->isCapture($game) == 'En passant') {
            $pawn = $game->desk->fields[($game->turn == 'W') ? ($game->en_passant[0] . (string)((int)$game->en_passant[1] - 1))
                : ($game->en_passant[0] . (string)((int)$game->en_passant[1] + 1))];
            $game->desk->deleteFigure($pawn);
        }
        return true;
    }
}