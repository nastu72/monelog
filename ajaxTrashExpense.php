<?php
//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　Ajax(支出の削除用）　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// Ajax処理
//================================

// postがあり、ユーザーIDがあり、ログインしている場合
if(isset($_POST['expenseId']) && isset($_SESSION['user_id']) && isLogin()){
    debug('POST送信があります。');
    $e_id = $_POST['expenseId'];
    debug('削除予定の支出ID：'.$e_id);

    //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    $sql = 'DELETE FROM expense WHERE id = :id AND user_id = :u_id';
    $data = array(':u_id' => $_SESSION['user_id'], ':id' => $e_id);
    

    debug('SQL：'.$sql);
    debug('流し込みデータ：'.print_r($data,true));

    $stmt = queryPost($dbh, $sql, $data);

    // クエリ成功の場合
    if($stmt){
        $_SESSION['msg_success'] = SUC04;
        debug('マイページへ遷移します。');
    }

    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
    }
    }
debug('Ajax処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
