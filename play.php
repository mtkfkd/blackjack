<?php
session_start();
$money = 50000;
$bet = filter_input(INPUT_POST, "betValue" );
if(isset($_POST['betValue'])) {
  $_SESSION['betValue'] = $bet;
}
if(isset($_SESSION['betValue'])) {
  $bet = $_SESSION['betValue'];
}

$gameend = false;
$cards = array();
$player = array();
$playerSL = array();
$playerSR = array();
$opponent = array();
$pcnt = 0;
$ocnt = 0;
$split = false;

if (!isset($_GET['reset'])){
  if (isset($_SESSION['cards'])){$cards = $_SESSION['cards'];};
  if (isset($_SESSION['player'])){$player = $_SESSION['player'];};
  if (isset($_SESSION['opponent'])){$opponent = $_SESSION['opponent'];};
  if (isset($_SESSION['playerSL'])){$playerSL = $_SESSION['playerSL'];};
  if (isset($_SESSION['playerSR'])){$playerSR = $_SESSION['playerSR'];};
  if (isset($_SESSION['split']) && $_SESSION['split'] === true){$split = $_SESSION['split'];};

}

if (isset($_SESSION['cards']) && !isset($_GET['reset'])){
  $cards = $_SESSION['cards'];
} else {
  $cards = setcards();
  //手札を2枚引く
  $player[] = array_shift($cards);
  $player[] = array_shift($cards);
  $opponent[] = array_shift($cards);
  $opponent[] = array_shift($cards);
  $_SESSION['split'] = false;
  $pcnt = count($player);
  $ocnt = count($opponent);
}

if (isset($_GET['hit'])) {
  $player[] = array_shift($cards);
  $pcnt = count($player);
}
if (isset($_GET['split'])) {
  $split = true;
  $_SESSION['split'] = $split;
  $playerSL[] = $player[0];
  $playerSR[] = $player[1];
}
if (isset($_GET['hitL'])) {
  $playerSL[] = array_shift($cards);
}
if (isset($_GET['hitR'])) {
  $playerSR[] = array_shift($cards);
}
//相手の行動
if (isset($_GET['stand']) || isset($_GET['standR']) || sumupHands($playerSR) >= 21) {
  while(!$gameend) {
    $random = rand(1,6);
    if(sumupHands($opponent) <= 16) {
      $opponent[] = array_shift($cards);
    } elseif(sumupHands($opponent) >= 17 && sumupHands($opponent) <= 19 && $random === 1) {
      $opponent[] = array_shift($cards);
    } else {
      $gameend = true;
    }
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
  foreach ($suits as $suit) {
    for ($i=1;$i<=13;$i++) {
      if ($i === 1) {
        $cards[] = array(
        'value' => $i,
        'num' => 'A',
        'suit' => $suit,
        );
      } elseif ($i === 11){
        $cards[] = array(
        'value' => 10,
        'num' => 'J',
        'suit' => $suit,
        );
      } elseif ($i === 12){
        $cards[] = array(
        'value' => 10,
        'num' => 'Q',
        'suit' => $suit,
        );
      } elseif ($i === 13){
        $cards[] = array(
        'value' => 10,
        'num' => 'K',
        'suit' => $suit,
        );
      } else {
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
$slTotal = sumupHands($playerSL);
$srTotal = sumupHands($playerSR);
function sumupHands($hands)
{
  $total = null;
  $ace = null;
  foreach ($hands as $card) {
    if ($card['value'] != 1 ) {
      $total += $card['value'];
    } elseif ($card['value'] === 1) {
      $ace += 1 ;
      $total += $card['value'];
      }
    }
  if ($total <= 11 && $ace >=1) {
    $total += 10;
  }
  return $total;
}

//得点、リザルトメッセージ
$message = null;
if ($_SESSION['split'] === false) {
    if ($gameend == false && $pTotal < 21 && $oTotal < 21){
      $message = '      <h2 class="total">合計:' . $pTotal . '</h2>'. PHP_EOL;
    }  elseif (($pTotal < 21 || $pTotal > 21) && $oTotal === 21 && $ocnt === 2) {
      $message = '      <h2 class="oblackjack">相手がBlack Jack!!<br>あなたの負け</h2>' . PHP_EOL;
      $gameend = true;
    } elseif ($pTotal === 21 && $pcnt === 2) {
      $message = '      <h2 class="blackjack">Black Jack!!<br>あなたの勝ち!!</h2>' . PHP_EOL;
      $gameend = true;
    } elseif ($pTotal === 21 && $pcnt > 2) {
      $message = '      <h2 class="oburst">あなた:'.$pTotal.'&nbsp;相手:'.$oTotal.'<br>あなたの勝ち</h2>' . PHP_EOL;  $gameend = true;
    } elseif ($pTotal === 21 && $oTotal === 21) {
      $message = '      <h2 class="blackjack">両者Black Jack!!<br>Nice Game!!</h2>' . PHP_EOL;
      $gameend = true;
    } elseif ($pTotal > 21 && $oTotal < 21) {
      $message = '      <h2 class="burst">××Burst××<br>あなたの負け</h2>' . PHP_EOL;
      $gameend = true;
    } elseif ($pTotal > 21 && $oTotal > 21) {
      $message = '      <h2 class="burst">両者××Burst××<br>引き分け</h2>' . PHP_EOL;
      $gameend = true;
    } elseif ($pTotal < 21 && $oTotal > 21) {
      $message = '      <h2 class="oburst">相手の××Burst××<br>あなたの勝ち!!</h2>' . PHP_EOL;
      $gameend = true;
    } elseif ($gameend == true && $pTotal <= 21 && $oTotal <= 21 && $pTotal < $oTotal) {
      $message = '      <h2 class="burst">あなた:'.$pTotal.'&nbsp;相手:'.$oTotal.'<br>あなたの負け</h2>' . PHP_EOL;
    } elseif ($gameend == true && $pTotal <= 21 && $oTotal <= 21 && $pTotal == $oTotal) {
      $message = '      <h2 class="burst">あなた:'.$pTotal.'&nbsp;相手:'.$oTotal.'<br>引き分け</h2>' . PHP_EOL;
    } elseif ($gameend == true && $pTotal <= 21 && $oTotal <= 21 && $pTotal > $oTotal) {
      $message = '      <h2 class="oburst">あなた:'.$pTotal.'&nbsp;相手:'.$oTotal.'<br>あなたの勝ち</h2>' . PHP_EOL;
    }
} elseif ($_SESSION['split'] === true) {
    if ($srTotal >= 21 || isset($_GET['standR'])) {
        if ($oTotal > 21 && $slTotal > 21 && $srTotal > 21) {
            $message = '      <h2 class="burst">両者××Burst××<br>引き分け</h2>' . PHP_EOL;
            $gameend = true;
        } elseif ($oTotal > 21 && ($slTotal <= 21 || $srTotal <= 21)) {
            $message = '      <h2 class="oburst">相手の××Burst××<br>あなたの勝ち!!</h2>' . PHP_EOL;
            $gameend = true;
        } elseif ($oTotal <= 21 && $slTotal > 21 && $srTotal > 21) {
            $message = '      <h2 class="burst">××Burst××<br>あなたの負け</h2>' . PHP_EOL;
            $gameend = true;
        } elseif (($oTotal === $slTotal && $srTotal <= $slTotal) || ($oTotal === $srTotal && $srTotal >= $slTotal)) {
            $message = '      <h2 class="burst">あなた(左):'.$slTotal.'&nbsp;(右):'.$srTotal.'<br>相手:'.$oTotal.'<br>引き分け</h2>' . PHP_EOL;
            $gameend = true;
        } elseif ($oTotal < $slTotal && $oTotal < $srTotal) {
            $message = '      <h2 class="oburst">あなた(左):'.$slTotal.'&nbsp;(右):'.$srTotal.'<br>相手:'.$oTotal.'<br>あなたの勝ち</h2>' . PHP_EOL;
            $gameend = true;
        } elseif ($oTotal < $slTotal || $oTotal < $srTotal) {
            $message = '      <h2 class="oburst">あなた(左):'.$slTotal.'&nbsp;(右):'.$srTotal.'<br>相手:'.$oTotal.'<br>あなたの勝ち</h2>' . PHP_EOL;
            $gameend = true;
        } elseif ($oTotal > $slTotal && $oTotal > $srTotal) {
            $message = '      <h2 class="burst">あなた(左):'.$slTotal.'&nbsp;(右):'.$srTotal.'<br>相手:'.$oTotal.'<br>あなたの負け</h2>';
            $gameend = true;
        }
    }
}
//hit,stand,split,resetボタン
$btn = null;
if ($gameend == false && !isset($_GET['standR']) && $player[0]['num'] === $player[1]['num'] && !isset($_GET['split']) && !isset($_GET['hitL']) && !isset($_GET['standL']) && !isset($_GET['hitR']) && $pcnt ===2) {
  $btn = '<p class="btn"><a href="?stand">STAND</a></p>
          <p class="btn"><a href="?hit">HIT</a></p>
          <p class="btn"><a href="?split">SPLIT</a></p>';
} elseif ($gameend == false && !isset($_GET['standR']) && $slTotal < 21 && (isset($_GET['split']) || isset($_GET['hitL']))) {
  $btn =  '<p class="btn"><a href="?standL">STAND(左)</a></p>
          <p class="btn"><a href="?hitL">HIT(左)</a></p>';
} elseif ($gameend == false && !isset($_GET['standR']) && $srTotal < 21 && $pcnt <= 2 && $player[0]['num'] === $player[1]['num'] && ($slTotal >= 21 || isset($_GET[('standL')]) || isset($_GET[('hitR')]))) {
  $btn =  '<p class="btn"><a href="?standR">STAND(右)</a></p>
          <p class="btn"><a href="?hitR">HIT(右)</a></p>';
} elseif ($gameend == false &&  !isset($_GET['standR']) && !isset($_GET['hitR'])) {
  $btn = '<p class="btn"><a href="?stand">STAND</a></p>
        <p class="btn"><a href="?hit">HIT</a></p>'.PHP_EOL;
} elseif ($gameend == true || isset($_GET['standR']) || $srTotal >= 21) {
  $btn = '<p class="btn"><a href="?reset">RESET</a></p>';
}

$_SESSION['cards'] = $cards;
$_SESSION['player'] = $player;
$_SESSION['opponent'] = $opponent;
$_SESSION['playerSL'] = $playerSL;
$_SESSION['playerSR'] = $playerSR;


// echo '<pre>';
// var_dump($playerSL);
// var_dump($playerSR);
// echo '</pre>';

?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>ブラックジャック</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
</head>
<body>
  <div class="allwrap">
    <div class="menu">
      <p class="menubtn">BET</p>
      <form action="play.php" method="post">
        <p class="preValue">$<input type="text" name="betValue" id="betValue" value="<?= $bet ?>" disabled></p>
      <input type="hidden" name="betValue" id="betValue2" value="5000">
      <input type="range" name="bet" id="bet" date-input="betValue"  max="50000" min="1000" step="500" value="<?= $bet ?>">
      <input type="submit" class="change" value="変更する">
      </form>
    </div>
    <div class="container">
      <div class="handWrapper">
<?php
if ($gameend == false){
  foreach ($opponent as $opponentNum){
    if ($opponentNum === reset($opponent)) {
      echo '        <div class="hand">
          <p class="suit ', $opponentNum['suit'], '">', $suit_mark[$opponentNum['suit']], '</p>
          <p class="handValue">', $opponentNum['num'], '</p>
        </div>',PHP_EOL;
    } else {
    echo '
        <div class="opphand">
          <p class="suit ', $opponentNum['suit'], '"></p>
          <p class="handValue"></p>
        </div>',PHP_EOL;
    }
  }
} elseif ($gameend == true){
  foreach ($opponent as $opponentNum){
    if ($opponentNum === reset($opponent)) {
      echo '        <div class="hand">
          <p class="suit ', $opponentNum['suit'], '">', $suit_mark[$opponentNum['suit']], '</p>
          <p class="handValue">', $opponentNum['num'], '</p>
        </div>',PHP_EOL;
    } else {
    echo '      <div class="resulthand">
              <p class="suitRe ', $opponentNum['suit'], '">', $suit_mark[$opponentNum['suit']], '</p>
              <p class="handValueRe">', $opponentNum['num'], '</p>
            </div>',PHP_EOL;
    }
  }
}
?>
      </div>
      <hr>
<?php
if (!isset($_GET['standR']) &&!isset($_GET['split']) && (!isset($_GET['hitL']) && !isset($_GET['standL'])) && !isset($_GET['hitR'])) {

    echo'      <div class="handWrapper">',PHP_EOL;

    foreach ($player as $playerNum){
      echo '        <div class="hand">
          <p class="suit ', $playerNum['suit'], '">', $suit_mark[$playerNum['suit']], '</p>
          <p class="handValue">', $playerNum['num'], '</p>
        </div>',PHP_EOL;
    }
} else {
//   echo '<pre>';
// var_dump($playerSL);
// var_dump($playerSR);
// echo '</pre>';
      echo '<div class="splitValue">',PHP_EOL,
      '<h2 class="total">合計:', $slTotal, '点</h2>',PHP_EOL,
      '<h2 class="total">合計:', $srTotal, '点</h2>',PHP_EOL,
      '</div>',PHP_EOL;
      echo '<div class="handWrapperSplit">',PHP_EOL;
      // echo '<h2 class="total">合計:', $slTotal, '点</h2>',PHP_EOL;
      echo '<div class="handSplit">',PHP_EOL;
      foreach ($playerSL as $playerSLNum) {
          echo '
            <div class="hand">
              <p class="suit ', $playerSLNum['suit'], '">', $suit_mark[$playerSLNum['suit']], '</p>
              <p class="handValue">', $playerSLNum['num'], '</p>
            </div>';
      }
      echo '</div>',PHP_EOL;
      echo '<div class="handSplit">',PHP_EOL;
      foreach ($playerSR as $playerSRNum) {
          echo '
            <div class="hand">
              <p class="suit ', $playerSRNum['suit'], '">', $suit_mark[$playerSRNum['suit']], '</p>
              <p class="handValue">', $playerSRNum['num'], '</p>
            </div>';
      }
      echo '</div>',PHP_EOL;
  }
?>

      </div><!-- /.handWrapper -->

      <div class="btnwrap">
        <?=$btn?>
      </div>
      <div class="result">
  <?php
echo $message;
  ?>
      </div><!-- /.result -->
      </form>
    </div> <!-- /.container -->
  </div> <!-- /.allwrap -->
  <script>
    ;$(function() {
      'use strict';
        var clickflag = 0;
      $('.menubtn').click(function(){
        if(clickflag === 0) {
          $('.menu:not(:animated)').animate({left:'0'},400);
          $('#bet:not(:animated)').animate({opacity:'1'});
          $('#betValue:not(:animated)').animate({opacity:'1'});
          $('.preValue:not(:animated)').animate({opacity:'1'});
          clickflag = 1;
        } else if(clickflag === 1) {
          $('.menu:not(:animated)').animate({left:'-290px'},400);
          $('#bet:not(:animated)').animate({opacity:''});
          $('#betValue:not(:animated)').animate({opacity:''});
          $('.preValue:not(:animated)').animate({opacity:''});
          clickflag = 0;
        }
      });

      $('#bet').on('input', function(){
        var value = $(this).val();
        $('#betValue').val(value);
        $('#betValue2').val(value);
      });
    });

  </script>
</body>
</html>