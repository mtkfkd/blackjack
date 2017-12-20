<?php
function getDb()
{
    $dsn = 'mysql:dbname=helloworld_blackjack; host=mysql1.star.ne.jp;charset=utf8';
    $usr = 'helloworld_bj';
    $password = 'fuku0502';

    try
    {
      $db = new PDO($dsn, $usr, $password);
      return $db;
    }
    catch (PDOException $e)
    {
      die("接続エラー：{$e->getMessage()}");
    }
}