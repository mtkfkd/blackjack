<?php
require_once 'rank/DbManager.php';

try {
    //データベースへの接続を確立
    $db = getDb();

    //データベースに情報を登録するSQL文
    $stt = $db -> prepare(
      'INSERT INTO ranking(name,score) VALUES(:name, :score)'
      );

    //INSERT命令に送信されたデータをセットする
    $stt->bindValue(':name', $_POST['name']);
    $stt->bindValue(':score', $_POST['score']);

    //INSERT命令を実行
    $stt->execute();

    //処理が完了したら、入力フォームにリダイレクトする
    header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/');
} catch (PDOException $e) {
    echo "エラーメッセージ: {$e->getMessage()}";
}

