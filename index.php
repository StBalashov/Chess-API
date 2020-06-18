<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
<?php

require_once 'core/Game.php';
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

$isPawnPick = false;
$message = $moveErr = $stateErr = '';
$extended_message = "";

if ($_POST['newGame']) {
    $game = new Game();
    $message = 'New Game started! ';
    $extended_message = $game->getFen();
    $game_serialized = serialize($game);
    $redis->set('game', json_encode($game_serialized));
}

if ($_POST['move']) {
    $game_serialized = json_decode($redis->get('game'));
    $game = unserialize($game_serialized);
    if ($game) {
        try {
            $move = new Move($_POST['move'], $game);
            $moveErr = $game->makeMove($move);
            if (!$moveErr) {
                $message .= 'Move #' . $game->moveCount . ": " . $_POST['move'] . ' move is made.<br>';
            } else {
                $message .= 'Move is not valid!';
            }
            if ($move->isPawnPromotion) {
                $isPawnPick = true;
                $game->promotionPawn = $move->figure;
            }
            $extended_message = $game->getFen();
            $game_serialized = serialize($game);
            $redis->set('game', json_encode($game_serialized));
            if ($game->isOver) {
                $message .= 'Game is over! ' . $game->isOver;
                $redis->del(['game']);
            }
        } catch (Exception $e) {
            $moveErr = $e->getMessage();
            $message = ' Try again.';
        }
    } else {
        $moveErr = 'You need to start a game at first!';
        $message = 'Cannot make move!';
    }
}

if ($_POST['promotionFigure']) {
    $game_serialized = json_decode($redis->get('game'));
    $game = unserialize($game_serialized);
    if ($game) {
        $game->promotionFigure = $_POST['promotionFigure'];
        $game->promotePawn();
        $message = 'Pawn promotion is made. <br><br>';
        $isPawnPick = false;
        $extended_message = $game->getFen();
        $game_serialized = serialize($game);
        $redis->set('game', json_encode($game_serialized));
    } else {
        $moveErr = 'You need to start a game at first!';
        $message = 'Cannot make move!';
    }

}
if ($_POST['getState']) {
    $game_serialized = json_decode($redis->get('game'));
    $game = unserialize($game_serialized);
    if ($game) {
        $message = 'FEN - ' . $game->getFen() . '<br>Move Count - ' . $game->moveCount;
    } else {
        $stateErr = 'You need to start a game at first!';
        $message = 'Cannot display state.';
    }
}

?>
<h2>INPUT</h2>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <input type="submit" name="newGame" value="Start a new game!"/>
    <br><br><br>
    <label>Enter your move:</label>
    <input type="text" name="move" placeholder="e2:e4"/>
    <span class="error" style="color:red"> <?php echo $moveErr; ?></span>
    <br>
    <button type="submit" name="submit">Make a move!</button>
    <br><br><br>
    <input type="submit" name="getState" value="Get a current state"/>
    <span class="error" style="color:red"> <?php echo $stateErr; ?></span>
    <br><br>
    <!-- Pawn Promotion Form   -->
    <input type="radio" name="promotionFigure" value="q"
        <?php if (!($isPawnPick)) echo 'hidden'; ?>>
    <label <?php if (!($isPawnPick)) echo 'hidden'; ?>> Queen </label>
    <input type="radio" name="promotionFigure" value="b"
        <?php if (!($isPawnPick)) echo 'hidden'; ?>>
    <label <?php if (!($isPawnPick)) echo 'hidden'; ?>> Bishop </label>
    <input type="radio" name="promotionFigure" value="r"
        <?php if (!($isPawnPick)) echo 'hidden'; ?>>
    <label <?php if (!($isPawnPick)) echo 'hidden'; ?>> Rook </label>
    <input type="radio" name="promotionFigure" value="n"
        <?php if (!($isPawnPick)) echo 'hidden'; ?>>
    <label <?php if (!($isPawnPick)) echo 'hidden'; ?>> Knight </label>
    <br><br>
    <input type="submit" name="submit" value="Pick pawn as this figure"
        <?php if (!($isPawnPick)) echo 'hidden'; ?>>
    <!---->
</form>
<?php
echo "<h2>OUTPUT:</h2>";
echo $message;
echo '<br><br>' . $extended_message;
?>

</body>
</html>
