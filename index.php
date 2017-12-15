<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>ブラックジャック</title>
  <link rel="stylesheet" href="style.css">
  <script
  src="https://code.jquery.com/jquery-3.2.1.min.js"
  integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
  crossorigin="anonymous"></script>
</head>
<body>
  <div class="container">
    <h1 class="title">BLACK JACK</h1>
    <p class="betClick">ベット額を変更</p>
    <div class="bet">
      <form action="" method="get">
        <input type="range" max="50000" min="1000" step="500">
      </form>
    </div>
    <p class="start"><a href="play.php">Game Start</a></p>
  </div> <!-- /.container -->
  <script>
    $(window).on("load",function(){
      $('.betClick').click(function(){
        
      })
    }

  </script>
</body>
</html>