# Chess API
Backend for playing a chess game.

# Rules implemeted:
Basic chess rules, en passant, castling, pawn promotion
Draw situations are not implemented.

# Installation guide:
- update vendor directory using composer and composer files 
- run redis server and specify your host and port in .env file

# How to use:
Open index.php in browser to see a simple HTML form for playing. 
Start the game via 'Start new game' button, then type your moves in the field in format "from:to" (e.g. e2:e4)
If you want to castle, type "your_king_field:your_rook_field"
When you are about to promote your pawn, you will be asked, which figure would you like to pick

After all your actions you will get feedback in the OUTPUT section

If you want to get messages from API in json format, run index_json
keys are : 
  newGame(1/0);
  move('your_move');
  getState(1/0);
  promotionFigure('q'/'r'/'b'/'n');
  




