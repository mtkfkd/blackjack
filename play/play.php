<?php
session_start();
require_once '../rank/DbManager.php';
require_once '../rank/Encode.php';

 /**
 *  必要な変数の初期化
 **/
$money = 50000;
$bet = (int)filter_input(INPUT_POST, "betValue" );

$result = 0;
$gamecount = 1;

$gameend = false;
$cards = array();
$player = array();
$playerSL = array();
$playerSR = array();
$opponent = array();
$pcnt = 0;
$ocnt = 0;
$split = false;
$btn = null;
$message = null;
$ranking = false;


 /**
 *  resetもしくはnextが押されていない場合(hit,stand,splitを押したとき)、
 *  各変数の値をセッションから取得する。
 **/
if (!isset($_GET['reset']) && !isset($_GET['next'])){
  if (isset($_SESSION['cards'])){$cards = $_SESSION['cards'];}
  if (isset($_SESSION['player'])){$player = $_SESSION['player'];}
  if (isset($_SESSION['opponent'])){$opponent = $_SESSION['opponent'];}
  if (isset($_SESSION['playerSL'])){$playerSL = $_SESSION['playerSL'];}
  if (isset($_SESSION['playerSR'])){$playerSR = $_SESSION['playerSR'];}
  if (isset($_SESSION['split']) && $_SESSION['split'] === true){$split = $_SESSION['split'];}
  if (isset($_SESSION['money'])){$money = $_SESSION['money'];}
  if (isset($_SESSION['gamecount'])){$gamecount = $_SESSION['gamecount'];}
}


 /**
 *  nextが押された場合、所持金(money)やゲームカウントを保持する。
 *  その後ゲームカウントをインクリメント。
 **/
if (isset($_GET['next'])){
  if (isset($_SESSION['money'])){$money = $_SESSION['money'];}
  if (isset($_SESSION['gamecount'])){$gamecount = $_SESSION['gamecount'];}
  $gamecount += 1;
}


 /**
 *  betの値を保持する。
 *  ひとつ前のbet額が所持金を超過してしまった場合、
 *  次のベット額を所持金と同じ値にする。
 **/
if(isset($_POST['betValue'])) {
    $_SESSION['betValue'] = $bet;
}
if(isset($_SESSION['betValue'])) {
    $bet = $_SESSION['betValue'];
    if ($bet > $money) {
      $_SESSION['betValue'] = $money;
      $bet = $money;
    }
}


 /**
 *  hit,stand,splitの場合は山札を保持し、
 *  reset,nextが押された場合は山札を再生成しゲームを再開する。
 **/
if (!isset($_GET['next']) && isset($_SESSION['cards']) && !isset($_GET['reset'])){
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


 /**
 *  hit:山札からカードを一枚取得。
 *  split:手札を左と右に分ける。
 *  hitL:split時の左手札を一枚追加。
 *  hitR:split時の右手札を一枚追加。
 **/
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


 /**
 *  相手の行動パターン
 *  自分がstandをするか、split時の右手札がburstした場合に行動。
 *  手持ちが16以下の場合必ずhitする。
 *  17~19までは1/6の確立でhitする。
 **/
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


 /**
 *  山札の生成関数
 *  suit_markはsuitsの名称と記号を一致させるためのキー。
 *  foreachとforで各suit毎に1~13までのカードを生成し、配列cardsに挿入。
 *  1=A,11=J,12=Q,13=Kとなるように場合分け。
 *  valueが実際にゲームで使用する際の値。
 *  numはカードの見た目の数字。
 *  最後に配列をシャッフルしてリターン。
 **/
$suit_mark = array(
  'spade' => '♠',
  'heart' => '❤',
  'diamond' => '♦',
  'club' => '♣',
  );
function setcards()
{
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


 /**
 *  手札の合計値を出すための関数
 *  基本的にはtotalにカードのvalueを足していく。
 *  Aの場合は1と11都合のいい方で取ることができる。
 *  aceでAの枚数を数えてtotalに1として足しておく。
 *  その後、21を超えない場合のみ10を足して11として取る。
 **/
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


 /**
 *  得点計算とリザルトのメッセージ表示
 **/
if ($_SESSION['split'] === false && !isset($_GET['clear'])) {
    if ($gameend == false && $pTotal < 21 && $oTotal < 21){
      $message = '      <h2 class="total">合計:' . $pTotal . '</h2>'. PHP_EOL;
    }  elseif (($pTotal < 21 || $pTotal > 21) && $oTotal === 21 && $ocnt === 2) {
      $message = '      <h2 class="oblackjack">相手がBlack Jack!!<br>あなたの負け</h2>' . PHP_EOL;
      $gameend = true;
      $result = '-$'.$bet;
      $money -= $bet;
    } elseif ($pTotal === 21 && $pcnt === 2) {
      $message = '      <h2 class="blackjack">Black Jack!!<br>あなたの勝ち!!</h2>' . PHP_EOL . '<audio src="../bgm/kansei.mp3" autoplay></audio>';
      $gameend = true;
      $result = '+$'. $bet * 1.5;
      $money += $bet * 1.5;
    } elseif ($pTotal === 21 && $pcnt > 2) {
      $message = '      <h2 class="oburst">あなた:'.$pTotal.'&nbsp;相手:'.$oTotal.'<br>あなたの勝ち</h2>' . PHP_EOL . '<audio src="../bgm/get.mp3" autoplay></audio>';
      $gameend = true;
      $result = '+$'.$bet;
      $money += $bet;
    } elseif ($pTotal === 21 && $oTotal === 21) {
      $message = '      <h2 class="blackjack">両者Black Jack!!<br>Nice Game!!</h2>' . PHP_EOL;
      $gameend = true;
      $result = '+$'. $bet * 1.5;
      $money += $bet * 1.5;
    } elseif ($pTotal > 21 && $oTotal < 21) {
      $message = '      <h2 class="burst">××Burst××<br>'.$pTotal.'点'.'<br>あなたの負け</h2>' . PHP_EOL;
      $gameend = true;
      $result = '-$'.$bet;
      $money -= $bet;
    } elseif ($pTotal > 21 && $oTotal > 21) {
      $message = '      <h2 class="burst">両者××Burst××<br>'.$pTotal.'点'.'<br>引き分け</h2>' . PHP_EOL;
      $gameend = true;
      $result = '+-$0';
    } elseif ($pTotal < 21 && $oTotal > 21) {
      $message = '      <h2 class="oburst">相手の××Burst××<br>あなたの勝ち!!</h2>' . PHP_EOL . '<audio src="../bgm/get.mp3" autoplay></audio>';
      $gameend = true;
      $result = '+$'.$bet;
      $money += $bet;
    } elseif ($gameend == true && $pTotal <= 21 && $oTotal <= 21 && $pTotal < $oTotal) {
      $message = '      <h2 class="burst">あなた:'.$pTotal.'&nbsp;相手:'.$oTotal.'<br>あなたの負け</h2>' . PHP_EOL;
      $result = '-$'.$bet;
      $money -= $bet;
    } elseif ($gameend == true && $pTotal <= 21 && $oTotal <= 21 && $pTotal == $oTotal) {
      $message = '      <h2 class="burst">あなた:'.$pTotal.'&nbsp;相手:'.$oTotal.'<br>引き分け</h2>' . PHP_EOL;
      $result = '+-$0';
    } elseif ($gameend == true && $pTotal <= 21 && $oTotal <= 21 && $pTotal > $oTotal) {
      $message = '      <h2 class="oburst">あなた:'.$pTotal.'&nbsp;相手:'.$oTotal.'<br>あなたの勝ち</h2>' . PHP_EOL . '<audio src="../bgm/get.mp3" autoplay></audio>';
      $result = '+$'.$bet;
      $money += $bet;
    }
} elseif ($_SESSION['split'] === true && !isset($_GET['clear'])) {
    if ($srTotal >= 21 || isset($_GET['standR'])) {
        if ($oTotal > 21 && $slTotal > 21 && $srTotal > 21) {
            $message = '      <h2 class="burst">両者××Burst××<br>引き分け</h2>' . PHP_EOL;
            $gameend = true;
            $result = '+-$0';
        } elseif ($oTotal > 21 && ($slTotal <= 21 || $srTotal <= 21)) {
            $message = '      <h2 class="oburst">相手の××Burst××<br>あなたの勝ち!!</h2>' . PHP_EOL . '<audio src="../bgm/get.mp3" autoplay></audio>';
            $gameend = true;
            $result = '+$'.$bet * 2;
            $money += $bet * 2;
        } elseif ($oTotal <= 21 && $slTotal > 21 && $srTotal > 21) {
            $message = '      <h2 class="burst">××Burst××<br>あなたの負け</h2>' . PHP_EOL;
            $gameend = true;
            $result = '-$'.$bet * 2;
            $money -= $bet * 2;
        } elseif (($oTotal === $slTotal && ($srTotal <= $slTotal || $srTotal > 21)) || ($oTotal === $srTotal && ($srTotal >= $slTotal || $slTotal > 21))) {
            $message = '      <h2 class="burst">あなた(左):'.$slTotal.'&nbsp;(右):'.$srTotal.'<br>相手:'.$oTotal.'<br>引き分け</h2>' . PHP_EOL;
            $gameend = true;
            $result = '-$'.$bet;
            $money -= $bet;
        } elseif (($oTotal < $slTotal && $slTotal < 22) && ($oTotal < $srTotal && $srTotal < 22)) {
            $message = '      <h2 class="oburst">あなた(左):'.$slTotal.'&nbsp;(右):'.$srTotal.'<br>相手:'.$oTotal.'<br>あなたの勝ち</h2>' . PHP_EOL . '<audio src="../bgm/get.mp3" autoplay></audio>';
            $gameend = true;
            $result = '+$'.$bet * 2;
            $money += $bet * 2;
        } elseif ($oTotal > $slTotal && $oTotal > $srTotal) {
            $message = '      <h2 class="burst">あなた(左):'.$slTotal.'&nbsp;(右):'.$srTotal.'<br>相手:'.$oTotal.'<br>あなたの負け</h2>';
            $gameend = true;
            $result = '-$'.$bet * 2;
            $money -= $bet * 2;
        } elseif (($oTotal < $slTotal && $slTotal < 22 ) || ($oTotal < $srTotal && $srTotal < 22)) {
            $message = '      <h2 class="oburst">あなた(左):'.$slTotal.'&nbsp;(右):'.$srTotal.'<br>相手:'.$oTotal.'<br>あなたの勝ち</h2>'    . PHP_EOL . '<audio src="../bgm/get.mp3" autoplay></audio>';
            $gameend = true;
            $result = '+$'.$bet;
            $money += $bet;
        } elseif (($oTotal > $slTotal && $srTotal > 21 ) || ($oTotal > $srTotal && $slTotal > 21)) {
            $message = '      <h2 class="oburst">あなた(左):'.$slTotal.'&nbsp;(右):'.$srTotal.'<br>相手:'.$oTotal.'<br>あなたの負け</h2>'    . PHP_EOL;
            $gameend = true;
            $result = '-$'.$bet * 2;
            $money -= $bet * 2;
        }
    }
}


 /**
 *  状況により表示するボタンのパターン
 **/
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
} elseif ($money > 0 && ($gameend == true || isset($_GET['standR']) || $srTotal >= 21) && $gamecount < 10) {
  $btn = '<p class="btn"><a href="?next">NEXT</a></p>';
} elseif ($money > 0 && ($gameend == true || isset($_GET['standR']) || $srTotal >= 21) && $gamecount == 10) {
  $btn = '<p class="btn"><a href="?clear">CLEAR</a></p>';
} elseif ($money <= 0 || ($gameend == true || isset($_GET['standR']) || $srTotal >= 21)) {
  $btn = '<p class="btn"><a href="../indexin.php">GAME OVER</a></p>'.PHP_EOL;
  $ranking = true;
}


 /**
 *  ゲームに必要な変数をセッションに格納
 **/
$_SESSION['cards'] = $cards;
$_SESSION['player'] = $player;
$_SESSION['opponent'] = $opponent;
$_SESSION['playerSL'] = $playerSL;
$_SESSION['playerSR'] = $playerSR;
$_SESSION['money'] = $money;
$_SESSION['betValue'] = $bet;
$_SESSION['gamecount'] = $gamecount;
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>ブラックジャック</title>
  <link rel="stylesheet" href="../style.css">
  <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
</head>
<body id="play">
  <div class="allwrap">

    <div class="menu">
      <p class="menubtn">BET</p>
      <form action="play.php?next" method="post">
        <p class="preValue">$<input type="text" name="betValue" id="betValue" value="<?= $bet ?>" disabled></p>
        <input type="hidden" name="betValue" id="betValue2" value="<?= $bet ?>">
        <input type="range" name="bet" id="bet" date-input="betValue"  max="<?= $money ?>" min="1000" step="500" value="<?= $bet ?>">
        <?php if ($gameend == true): ?>
        <input type="submit" class="change" value="変更する">
      <?php elseif ($gameend == false): ?>
<input type="submit" class="change dis" value="変更不可" disabled>
      <?php endif; ?>
</form>
    </div>

    <div class="money">
      <p class="betmoney"><span class="count"><?= $gamecount <= 10 ? $gamecount : 10 ?>/10</span><br>ベット<br>
      <span class="valu">$<?= $bet ?></span></p>
      <p class="reward">結果<br>
      <span class="valu"><?= $result ?></span></p>
      <p class="havemoney">所持金<br>
      <span class="valu">$<?= $money ?></span></p>
      <input type="hidden" id="moneyvalu" value="<?= $money ?>">
    </div>

    <div class="container">

<?php
    if (isset($_GET['clear'])) {
      echo'
      <div class="rankingform">
        <form action="../Register.php" method="post">
        <h2>ランキング登録!</h2>
          <p>Name<br><input type="text" id="rankname" name="name" placeholder="名前を入力"><br>
          SCORE<br>$<input type="text" id="rankValue" name="score" value="'. $money .'" disabled><br>
          <input type="hidden" name="score" value="'. $money .'">
          <input type="submit" value="登録する" id="register"></p>
          <a href="../indexin.php" class="change no">登録しない</a>
        </form>
      </div>';
    }
?>
      <div class="handWrapper">
<?php

 /**
 *  相手の手札の表示
 *  最初のif文はopponent(相手の手札)の一枚目だけ表向きで表示するための記述。elseは裏向き表示。
 *  その後のelseifはゲーム終了時にすべて表向きにするための記述。
 *  裏向きだった手札だけアニメーションするために1枚目と2枚目以降で分けている。
 **/
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
      </div><!-- /.handwrapper -->

      <hr>
<?php

 /**
 * 自分の手札の表示
 * splitしていないときはそのまま表向き表示
 * split時は手札を二つに分けて表示
 **/
if (!$_SESSION['split']) {

    echo'      <div class="handWrapper">',PHP_EOL;

    foreach ($player as $playerNum){
      echo '        <div class="hand">
          <p class="suit ', $playerNum['suit'], '">', $suit_mark[$playerNum['suit']], '</p>
          <p class="handValue">', $playerNum['num'], '</p>
        </div>',PHP_EOL;
    }
} else {
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
      </div><!-- /.handwrapper -->

      <div class="btnwrap">
        <?=$btn?>
      </div>

      <div class="result">
  <?php
echo $message;
  ?>
      </div><!-- /.result -->

    </div> <!-- /.container -->
  </div> <!-- /.allwrap -->

  <script>
    ;$(function() {
      'use strict';
        var clickflag = 0;
        var windowWidth = window.innerWidth;
      $('.menubtn').click(function(){
        if(clickflag === 0) {
          $('.menu:not(:animated)').animate({left:'0',shadow:'5px 5px 3px #333'},550);
          $('#bet:not(:animated)').animate({opacity:'1'});
          $('#betValue:not(:animated)').animate({opacity:'1'});
          $('.preValue:not(:animated)').animate({opacity:'1'});
          clickflag = 1;
        } else if(clickflag === 1) {
          if (windowWidth > 1024) {
            $('.menu:not(:animated)').animate({left:'-290px'},400);
            $('#bet:not(:animated)').animate({opacity:''});
            $('#betValue:not(:animated)').animate({opacity:''});
            $('.preValue:not(:animated)').animate({opacity:''});
          } else if (windowWidth <= 1024) {
              $('.menu:not(:animated)').animate({left:'-500px'},400);
              $('#bet:not(:animated)').animate({opacity:''});
              $('#betValue:not(:animated)').animate({opacity:''});
              $('.preValue:not(:animated)').animate({opacity:''});
          }
          clickflag = 0;
        }
      });

      $('#bet').on('input', function(){
        var value = $(this).val();
        $('#betValue').val(value);
        $('#betValue2').val(value);
      });
      $('#moneyvalu').on('input', function() {
        var moneyV = $(this).val();
        for(i=0;i == moneyV;i++) {
          $('#money').val(moneyV);
        }
      })
    });

  </script>
</body>
</html>