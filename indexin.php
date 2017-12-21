<?php
require_once 'rank/DbManager.php';
require_once 'rank/Encode.php';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>ブラックジャック</title>
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
  <link rel="stylesheet" href="style.css">
  <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
</head>
<body id="indexin">
<!-- <p class="soundBtn clicked"><i class="fa fa-volume-up" aria-hidden="true"></i><br><small>ON</small></p> -->
  <div class="rule">
    <h2 class="ruleTitle">ブラックジャックのルール</h2>
    <p class="ruleContents">ブラックジャックは、手札の合計を21に近づけるゲームです。<br>より21に近いプレイヤーが勝ちとなります。<br></p>
    <p class="ruleContents">
    初めに手札が2枚配られます。<br></p>
    <section>
      <p class="ruleContents">
        <span class="ruleImp">もう一枚カードを引く場合はHIT</span><br>
        <span class="ruleImp">そのままの手札で勝負する場合はSTAND</span><br>
      </p>
    </section>
    <p class="ruleContents">
    これらを宣言しながら合計を21に近づけてください。<br>
    初めの手札が2枚とも同じ数である場合<br>
    </p>
    <section>
      <p class="ruleContents">
        <span class="ruleImp">あなたはSPLITを宣言することができます</span><br>
      </p>
    </section>
    <p class="ruleContents">
    SPLITは手札を2つに分け、それぞれに同じ額BETすることで、<br>
    左手札、右手札それぞれで勝負することが出来ます。
    </p>
    <section>
    <p class="ruleContents">
      勝った場合は、BETした額を獲得し負けると失います。<br>
      初めの手札の合計が21だった場合ブラックジャックとなり<br>
      BETした額の1.5倍の金額を獲得します。<br></p>
    <p class="ruleContents">
      10回勝負して、より多くのお金を獲得してください！
    </p>
    </section>
    <p class="ruleClose">ルール説明を閉じる</p>
  </div>
    <h1 class="title">BLACK JACK</h1>
    <form action="play/play.php?reset" method="post">

      <div class="index-wrapper">
        <div class="index-bet">
          <p class="betClick">ベット額の変更</p>
          <div class="bet">
              <p class="preValue">$<input type="text" name="betValue" id="betValue" value="5000" disabled></p>
              <input type="hidden" name="betValue" id="betValue2" value="5000">
              <input type="range" name="bet" id="bet" date-input="betValue"  max="50000" min="1000" step="500" value="5000">
          </div>
        </div>

        <div class="index-rank">
          <p class="rankClick">ランキング</p>
          <div class="rank">
            <table border="1">
              <tr>
                <th>RANK</th>
                <th>NAME</th>
                <th>SCORE</th>
              </tr>
<?php
try {
$db = getDb();
    //SELECT文の実行
    $stt = $db->prepare(
      'SELECT
        @n:=@n+1 AS Ranking,
        ranking.*
       FROM
        ranking,
       (SELECT @n:=0) AS dummy
       ORDER BY
        ranking.score DESC'
      );

    $stt->execute();

  while ($row = $stt->fetch(PDO::FETCH_ASSOC))
  {
?>
              <tr>
                <td class="tdrank"><?= e($row['Ranking']); ?></td>
                <td class="tdname"><?= e($row['name']); ?></td>
                <td class="tdscore">$<?= e($row['score']); ?></td>
              </tr>
<?php } ?>
            </table>
<?php
} catch(PDOException $e) {
    echo "エラーメッセージ: {$e->getMessage()}";
}
?>
          </div><!-- rank -->
        </div><!-- index-rank -->
      </div><!-- index-wrapper -->

      <input type="submit" class="start" value="Game Start">
    </form>
    <h3 class="ruleClick">ルールを確認する</h3>
  <script>
    ;$(function() {
      'use strict';

 $(".soundBtn").click(function(){
    if($(this).hasClass("clicked")){
      $(this).removeClass("clicked")
      $(this).html('<i class="fa fa-volume-off" aria-hidden="true"></i><br><small>OFF</small>');
      document.getElementById("audio").currentTime = 0;
      document.getElementById("audio").volume = 0.5;
      document.getElementById("audio").play();
    }else{
      $(this).addClass("clicked");
      $(this).html('<i class="fa fa-volume-up" aria-hidden="true"></i><br><small>ON</small>');
      document.getElementById("audio").pause();
    }
  });

      // $('#audio').get(0).prop('volume', 0.2);
      // $('#audio').get(0).play();
      var betClick = 0;
      var rankClick = 0;
      $('.betClick').click(function(){
        console.log(betClick);
        if(betClick === 0) {
          $('.bet:not(:animated)').animate({height:'250px',
                                            padding:'50px 0 0 0'},400);
          $('#bet:not(:animated)').animate({opacity:'1'});
          $('#betValue:not(:animated)').animate({opacity:'1'});
          $('.preValue:not(:animated)').animate({opacity:'1'});
          betClick = 1;
        } else if(betClick === 1) {
          $('.bet:not(:animated)').animate({height:'',padding:''},400);
          $('#bet:not(:animated)').animate({opacity:''});
          $('#betValue:not(:animated)').animate({opacity:''});
          $('.preValue:not(:animated)').animate({opacity:''});
          betClick = 0;
        }
      });

      $('.rankClick').click(function(){
        if(rankClick === 0) {
          $('.rank:not(:animated)').animate({height:'280px',
                                            padding:'20px 0 0 0'},400);
          $('table').css('display','table');
          $('table:not(:animated)').animate({opacity:'1'});
          rankClick = 1;
        } else if(rankClick === 1) {
          $('.rank:not(:animated)').animate({height:'',padding:''},400);
          $('table:not(:animated)').animate({opacity:''},function(){$('table').css('display','');});
          rankClick = 0;
        }
      });

      $('.ruleClick').click(function() {
        $('.rule').css({'display': 'block'});
        $('.rule').animate({height:'110vh'},600,);
      });

      $('.ruleClose').on('click', function() {
        $('.rule').animate({height:'0'},600, function() {
          $('.rule').css({'display' : 'none',
                          'height' : '0vh'});
        });
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