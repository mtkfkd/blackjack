<?php
session_start();

$gameend = false;
$cards = array();
$player = array();
$opponent = array();

if(!isset($_GET['reset'])){
  if(isset($_SESSION['cards'])){$cards = $_SESSION['cards'];};
  if(isset($_SESSION['player'])){$player = $_SESSION['player'];};
  if(isset($_SESSION['opponent'])){$opponent = $_SESSION['opponent'];};
}

if(isset($_SESSION['cards']) && !isset($_GET['reset'])){
  $cards = $_SESSION['cards'];
} else {
  $cards = setcards();
  //手札を2枚引く
  $player[] = array_shift($cards);
  $player[] = array_shift($cards);
  $opponent[] = array_shift($cards);
  $opponent[] = array_shift($cards);
}

if(isset($_GET['hit'])){
  $player[] = array_shift($cards);
}
//相手の行動
if(isset($_GET['hit']) || isset($_GET['stand'])) {
  $random = rand(1,3);
  if(sumupHands($opponent) < 15) {
    $opponent[] = array_shift($cards);
  } elseif(sumupHands($opponent) >= 15 && sumupHands($opponent) < 19 && $random === 1) {
    $opponent[] = array_shift($cards);
  } elseif(isset($_GET['stand'])){
    $gameend = true;
  }
}

//suitを用意
$suit_mark = array(
  'spade' => '♠',
  'heart' => '❤',
  'diamond' => '♦',
  'club' => '♣',
  );
//山札を用意
function setcards()
{
$cards = array();
$suits = array('spade', 'heart', 'diamond', 'club');
  foreach($suits as $suit) {
    for($i=1;$i<=13;$i++) {
      if($i === 1) {
        $cards[] = array(
        'value' => $i,
        'num' => 'A',
        'suit' => $suit,
        );
      } elseif($i === 11){
        $cards[] = array(
        'value' => 10,
        'num' => 'J',
        'suit' => $suit,
        );
      } elseif($i === 12){
        $cards[] = array(
        'value' => 10,
        'num' => 'Q',
        'suit' => $suit,
        );
      } elseif($i === 13){
        $cards[] = array(
        'value' => 10,
        'num' => 'K',
        'suit' => $suit,
        );
      } else{
      $cards[] = array(
        'value' => $i,
        'num' => $i,
        'suit' => $suit,
        );
      }
    }
  }
  shuffle($cards);
  return $cards;
}

//echo '<pre>';
//var_dump($player);
//echo '</pre>';

//2枚の合計値
$pTotal = sumupHands($player);
$oTotal = sumupHands($opponent);
function sumupHands($hands)
{
  $total = null;
  $ace = null;
  foreach($hands as $card){
    if($card['value']!=1){
      $total += $card['value'];
    } elseif($card['value'] === 1) {
      $ace +=1;
      $total += $card['value'];
      }
    }
  if($total <=11 && $ace >=1) {
    $total += 10;
  }
  return $total;
}

//得点、リザルトメッセージ
$message = null;
if($gameend == false && $pTotal < 21 && $oTotal < 21){
  $message = '<h2 class="total">合計:' . $pTotal . '</h2>'. PHP_EOL;
}  elseif(($pTotal < 21 || $pTotal > 21) && $oTotal === 21) {
  $message = '<h2 class="oblackjack">相手がBlack Jack!!<br>あなたの負け</h2>' . PHP_EOL;
  $gameend = true;
} elseif($pTotal === 21) {
  $message = '<h1 class="blackjack">Black Jack!!<br>あなたの勝ち!!</h2>' . PHP_EOL;
  $gameend = true;
}  elseif($pTotal === 21 && $oTotal === 21) {
  $message = '<h1 class="blackjack">両者Black Jack!!<br>Nice Game!!</h2>' . PHP_EOL;
  $gameend = true;
} elseif($pTotal > 21 && $oTotal < 21) {
  $message = '<h2 class="burst">××Burst××<br>あなたの負け</h2>' . PHP_EOL;
  $gameend = true;
} elseif($pTotal > 21 && $oTotal > 21) {
  $message = '<h2 class="burst">両者××Burst××<br>引き分け</h2>' . PHP_EOL;
  $gameend = true;
} elseif($pTotal < 21 && $oTotal > 21) {
  $message = '<h2 class="oburst">相手の××Burst××<br>あなたの勝ち!!</h2>' . PHP_EOL;
  $gameend = true;
} elseif($gameend == true && $pTotal < 21 && $oTotal < 21 && $pTotal < $oTotal) {
  $message = '<h2 class="burst">あなた:'.$pTotal.'&nbsp;相手:'.$oTotal.'<br>あなたの負け</h2>' . PHP_EOL;
} elseif($gameend == true && $pTotal < 21 && $oTotal < 21 && $pTotal == $oTotal) {
  $message = '<h2 class="burst">あなた:'.$pTotal.'&nbsp;相手:'.$oTotal.'<br>引き分け</h2>' . PHP_EOL;
} elseif($gameend == true && $pTotal < 21 && $oTotal < 21 && $pTotal > $oTotal) {
  $message = '<h2 class="oburst">あなた:'.$pTotal.'&nbsp;相手:'.$oTotal.'<br>あなたの勝ち</h2>' . PHP_EOL;
}

//hit,stand,resetボタン
$btn = null;
if(!$gameend) {
  $btn = '<p class="btn"><a href="?hit">HIT</a></p><p class="btn"><a href="?stand">STAND</a></p>';
} elseif($gameend) {
  $btn = '<p class="btn"><a href="?reset">RESET</a></p>';
}

$_SESSION['cards'] = $cards;
$_SESSION['player'] = $player;
$_SESSION['opponent'] = $opponent;
//echo '<pre>';
//var_dump($player);
//echo '</pre>';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>ブラックジャック</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
      <div class="handWrapper">
<?php
if($gameend == false){
  foreach($opponent as $opponentNum){
    echo '<div class="handwrap">
            <div class="opphand">
              <p class="suit ', $opponentNum['suit'], '"></p>
              <p class="handValue"></p>
            </div>
          </div>';
  }
} elseif($gameend == true){
  foreach($opponent as $opponentNum){
    echo '<div class="handwrap">
            <div class="resulthand">
              <p class="suit ', $opponentNum['suit'], '">', $suit_mark[$opponentNum['suit']], '</p>
              <p class="handValueRe">', $opponentNum['num'], '</p>
            </div>
          </div>';
  }
}
?>
      </div>
      <hr>
      <div class="handWrapper">
<?php
foreach($player as $playerNum){
  echo '<div class="handwrap">
          <div class="hand">
            <p class="suit ', $playerNum['suit'], '">', $suit_mark[$playerNum['suit']], '</p>
            <p class="handValue">', $playerNum['num'], '</p>
          </div>
        </div>';
}
?>

      </div><!-- /.handWrapper -->

      <div class="btnwrap">
        <?=$btn?>
      </div>
      <div class="result">
  <?php
  if(!$gameend){
    echo $message;
  } elseif($gameend) {
    echo $message;
  }

  ?>
      </div><!-- /.result -->
    </form>
  </div> <!-- /.container -->
</body>
</html>