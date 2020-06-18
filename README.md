# Chess API
Backend for playing a chess game.  <br  />
Used [Forsyth–Edwards notation](https://en.wikipedia.org/wiki/Forsyth%E2%80%93Edwards_Notation) to describe game state.  <br  />
Figures are encoded in algebraic chess notation (White queen - Q, Black king - k, black knight - n, etc.)

# Rules implemeted:
Basic chess rules, en passant, castling, pawn promotion. <br  />
Draw situations are not implemented.

# Installation guide:
- update vendor directory using composer and composer files 
  - To install composer into PhpStorm, follow [this tutorial](https://www.jetbrains.com/help/phpstorm/using-the-composer-dependency-manager.html)
  - Then open composer.json and at the top of the editor page click on install or update
- run redis server and specify your host and port in .env file
  - download from [here](https://redis.io/download)
  - unpack it, then in terminal in redis directory run make command.
  - then type
  ```
  $ redis-server start
  ```
  
- run index.php or index_json.php to talk with api
  - download and install php >7.4, open project folder in terminal and type 
  ```
  $ php -S localhost:8000 index.php
  ```
  - then open it in your browser
  - or use any other way to run it

# How to use:
1. Open index.php in browser to see a simple HTML form for playing. 
2. Start the game via 'Start new game' button, then type your moves in the field in format "from:to" (e.g. e2:e4)
3. If you want to castle, type "your_king_field:your_rook_field"
4. When you are about to promote your pawn, you will be asked, which figure would you like to pick

5. After all your actions you will get feedback in the OUTPUT section

6. If you want to get messages from API in json format, run index_json.php.  Keys are : 
    - newGame(1/0);
    - move('your_move');
    - getState(1/0);
    - promotionFigure('q'/'r'/'b'/'n');
  




