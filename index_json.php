<?php

require_once 'Game.php';
require_once 'vendor/autoload.php';
error_reporting(0);

$dotenv = Dotenv\Dotenv::createImmutable(dirname('.env'));
$dotenv->load();
Predis\Autoloader::register();
$redis = new Predis\Client(array(
        'scheme' => $_ENV['SCHEME'],
        'host' => $_ENV['HOST'],
        'port' => $_ENV['PORT'])
);
$response = [];
$isPawnPick = false;

if ($_POST['newGame']) {
    $game = new Game();
    $response['message'] = 'New Game started!';
    $response['state_FEN'] = $game->getFen();
    $game_serialized = serialize($game);
    $redis->set('game', json_encode($game_serialized));
}

if ($_POST['move']) {
    $game_serialized = json_decode($redis->get('game'));
    $game = unserialize($game_serialized);
    if ($game) {
        try {
            $move = new Move($_POST['move'], $game);
            $response['moveError'] = $game->makeMove($move);
            if (!$moveErr) {
                $response['message'] .= 'Move #' . $game->moveCount . ": " . $_POST['move'] . ' move is made.<br>';
            } else {
                $response['message'] .= 'Move is not valid!';
            }
            if ($move->isPawnPromotion) {
                $isPawnPick = true;
                $game->promotionPawn = $move->figure;
            }
            $response['state_FEN'] = $game->getFen();
            $game_serialized = serialize($game);
            $redis->set('game', json_encode($game_serialized));
            if ($game->isOver) {
                $response['message'] .= 'Game is over! ' . $game->isOver;
                $redis->del(['game']);
            }
        } catch (Exception $e) {
            $response['moveError'] = $e->getMessage();
            $response['message'] = ' Try again.';
        }
    } else {
        $response['moveErr'] = 'You need to start a game at first!';
        $response['message'] = 'Cannot make move!';
    }
}

if ($_POST['promotionFigure']) {
    $game_serialized = json_decode($redis->get('game'));
    $game = unserialize($game_serialized);
    if ($game) {
        $game->promotionFigure = $_POST['promotionFigure'];
        $game->promotePawn();
        $response['message'] = 'Pawn promotion is made. <br><br>';
        $isPawnPick = false;
        $response['state_FEN'] = $game->getFen();
        $game_serialized = serialize($game);
        $redis->set('game', json_encode($game_serialized));
    } else {
        $response['moveErr'] = 'You need to start a game at first!';
        $response['message'] = 'Cannot make move!';
    }
}

if ($_POST['getState']) {
    $game_serialized = json_decode($redis->get('game'));
    $game = unserialize($game_serialized);
    if ($game) {
        $response['state_FEN'] = 'FEN - ' . $game->getFen() . '<br>Move Count - ' . $game->moveCount;
    } else {
        $response['stateError'] = 'You need to start a game at first!';
        $response['message'] = 'Cannot display state.';
    }
}

$json = json_encode($response, JSON_PRETTY_PRINT);

echo '<pre>';
echo $json;
echo '</pre>';