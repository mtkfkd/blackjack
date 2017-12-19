<?php
require_once 'rank/DbManager.php';
require_once 'rank/Encode.php';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>ブラックジャック</title>
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
  <link rel="stylesheet" href="style.css">
  <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
</head>
<body>
<!-- <p class="soundBtn clicked"><i class="fa fa-volume-up" aria-hidden="true"></i><br><small>ON</small></p> -->
<!-- <audio preload="none" id="audio" loop> -->
  <source src="bgm/casino.mp3">
</audio>
  <div>
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
  </div>
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
        console.log(rankClick);
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

      $('#bet').on('input', function(){
        var value = $(this).val();
        $('#betValue').val(value);
        $('#betValue2').val(value);
      });
    });

  </script>
</body>
</html>