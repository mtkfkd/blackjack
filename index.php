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
<body id="playing">
<p class="soundBtn clicked"><i class="fa fa-volume-off" aria-hidden="true"></i><br><small>ON</small></p>
<audio preload="none" id="audio" loop>
  <source src="bgm/casino.mp3">
</audio>
 <iframe id="baseFrame" src="indexin.php" width="100%" height="100%" frameborder="0"></iframe>
<script>
  ;$(function() {
    'use strict';

    $(".soundBtn").click(function(){
      if($(this).hasClass("clicked")){
        $(this).removeClass("clicked")
        $(this).html('<i class="fa fa-volume-up" aria-hidden="true"></i><br><small>OFF</small>');
        document.getElementById("audio").currentTime = 0;
        document.getElementById("audio").volume = 0.5;
        document.getElementById("audio").play();
      }else{
        $(this).addClass("clicked");
        $(this).html('<i class="fa fa-volume-off" aria-hidden="true"></i><br><small>ON</small>');
        document.getElementById("audio").pause();
      }
    });
  });

</script>
</body>
</html>