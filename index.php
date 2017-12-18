<?php
require_once 'rank/DbManager.php';
require_once 'rank/Encode.php';
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
  <div>
    <h1 class="title">BLACK JACK</h1>
    <form action="play.php?reset" method="post">
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
        ranking.score DESC
       LIMIT
        5'
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