<?php
function getDb()
{
    $dsn = 'mysql:dbname=blackjack; host=localhost;charset=utf8';
    $usr = 'root';
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