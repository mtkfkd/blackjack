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
      <p class="betClick">ベット額の変更</p>
      <div class="bet">
          <p class="preValue">$<input type="text" name="betValue" id="betValue" value="5000" disabled></p>
          <input type="hidden" name="betValue" id="betValue2" value="5000">
          <input type="range" name="bet" id="bet" date-input="betValue"  max="50000" min="1000" step="500" value="5000">
      </div>
      <input type="submit" class="start" value="Game Start">
    </form>
  </div> <!-- /.container -->
  <script>
    ;$(function() {
      'use strict';
        var clickflag = 0;
      $('.betClick').click(function(){
        if(clickflag === 0) {
          $('.bet:not(:animated)').animate({height:'250px',
                                            padding:'50px 0 0 0',
                                            shadow:'1px 1px 3px #333'},400);
          $('#bet:not(:animated)').animate({opacity:'1'});
          $('#betValue:not(:animated)').animate({opacity:'1'});
          $('.preValue:not(:animated)').animate({opacity:'1'});
          clickflag = 1;
        } else if(clickflag === 1) {
          $('.bet:not(:animated)').animate({height:'',padding:''},400);
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