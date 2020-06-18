<?php

require_once 'Move.php';
foreach (glob("core/figures/*.php") as $filename) {
    if ($filename != '/core/figures/Figure.php') {
        require_once $filename;
    }
}

class Desk
{
    /**
     * @var array
     */
    public $fields;

    /**
     * @var array
     */
    public $figures;

    /**
     * Desk constructor.
     */
    public function __construct()
    {
        $cleanDesk = [];
        $letters = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];
        for ($i = 1; $i < 9; $i++) {
            foreach ($letters as $letter) {
                $piece = $letter . (string)$i;
                $cleanDesk[$piece] = null;
            }
        }
        $startFields = [
            'a1' => new Rook('a1', 'W', 'a1:d1'),
            'b1' => new Knight('b1', 'W'),
            'c1' => new Bishop('c1', 'W'),
            'd1' => new Queen('d1', 'W'),
            'e1' => new King('e1', 'W'),
            'f1' => new Bishop('f1', 'W'),
            'g1' => new Knight('g1', 'W'),
            'h1' => new Rook('h1', 'W', 'h1:f1'),
            'a2' => new Pawn('a2', 'W'),
            'b2' => new Pawn('b2', 'W'),
            'c2' => new Pawn('c2', 'W'),
            'd2' => new Pawn('d2', 'W'),
            'e2' => new Pawn('e2', 'W'),
            'f2' => new Pawn('f2', 'W'),
            'g2' => new Pawn('g2', 'W'),
            'h2' => new Pawn('h2', 'W'),
            'a8' => new Rook('a8', 'b', 'a8:d8'),
            'b8' => new Knight('b8', 'b'),
            'c8' => new Bishop('c8', 'b'),
            'd8' => new Queen('d8', 'b'),
            'e8' => new King('e8', 'b'),
            'f8' => new Bishop('f8', 'b'),
            'g8' => new Knight('g8', 'b'),
            'h8' => new Rook('h8', 'b', 'h8:f8'),
            'a7' => new Pawn('a7', 'b'),
            'b7' => new Pawn('b7', 'b'),
            'c7' => new Pawn('c7', 'b'),
            'd7' => new Pawn('d7', 'b'),
            'e7' => new Pawn('e7', 'b'),
            'f7' => new Pawn('f7', 'b'),
            'g7' => new Pawn('g7', 'b'),
            'h7' => new Pawn('h7', 'b')
        ];
        $this->figures = array_values( $startFields);
        $this->fields = array_replace($cleanDesk, $startFields);
    }

    /**
     * clone constructor.
     */
    public function __clone()
    {
        foreach ($this->fields as $field => $figure) {
            if ($figure) {
                $this->fields[$field] = clone $figure;
            } else {
                $this->fields[$field] = null;
            }
        }
        foreach ($this->figures as $figure) {
            $temp = clone $figure;
            $this->figures[] = $temp;
        }
    }

    /**
     * @param $figure
     * deletes figure from fields and figures
     */
    public function deleteFigure($figure)
    {
        if ($figure) {
            array_splice($this->figures, array_search($figure, $this->figures), 1);
            $this->fields[$figure->position] = null;
        }
    }

    /**
     * @param Move $move
     * records move on desk
     */
    public function makeMove(Move &$move)
    {
        $this->deleteFigure($this->fields[$move->to]);
        $this->fields[$move->to] = $this->fields[$move->from];
        $this->fields[$move->from] = null;
        $move->figure->position = $move->to;
    }

    /**
     * @param $move
     * after modifying figure properties, updates this figure in $fields array
     */
    public function updateFigure($move)
    {
        $this->fields[$move->to] = $move->figure;
    }

    /**
     * @return string
     * generates a FEN representation of the desk
     */
    public function generateFen()
    {
        $fen = '';
        $count_empty = 0;
        foreach ($this->fields as $field => $figure) {
            if (!$figure) {
                $count_empty++;
            } else {
                if ($count_empty != 0) {
                    $fen .= (string)($count_empty);
                }
                $fen .= $figure->shortname;
                $count_empty = 0;
            }
            if (($field[0] == 'h')) {
                if ($count_empty != 0) {
                    $fen .= (string)($count_empty);
                }
                $fen .= '/';
                $count_empty = 0;
            }
        }
        $fen = rtrim($fen, '/');
        $rows = explode('/', $fen);
        $fen = join("/", array_reverse($rows));
        return $fen;
    }
}
