<?php 

//共通変数・関数ファイルの読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　退会ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//================================
// 画面処理
//================================
// post送信されていた場合
if(!empty($_POST)){
    debug('POST送信があります。');

    // 例外処理
    try{
        $dbh = dbConnect();
        // SQL文作成(ユーザー情報、収支情報の削除)
        $sql1 = 'UPDATE users SET delete_flg = 1 WHERE id = :u_id';
        $sql2 = 'UPDATE expense SET delete_flg = 1 WHERE user_id = :u_id';
        $sql3 = 'UPDATE income SER delete_flg = 1 WHERE user_id = :u_id';

        $data = array(':u_id' => $_SESSION['user_id']);

        $stmt1 = queryPost($dbh,$sql1,$data);
        $stmt2 = queryPost($dbh,$sql2,$data);
        $stmt3 = queryPost($dbh,$sql3,$data);

        // クエリ実行成功の場合はセッション削除して新規登録画面へ
        if($stmt1){
            session_destroy();
            debug('$_SESSIONの中身：'.print_r($_SESSION,true));
            debug('新規登録画面へ遷移します。');
            header("Location:signup.php");
        }else{
            debug('エラー発生');
            $err_msg['common'] = MSG07;
        }

    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
        $err_msg['common'] = MSG07;
    }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php 
$siteTitle = '退会';
require('head.php');
?>

<body class="page-1colum">
    <!-- メニュー -->
    <?php 
    require('header.php');
    ?>
     <!-- メインコンテンツ -->
     <div id="contents" class="site-width">
         <!-- Main -->
         <section id="main">
             <div class="form-container">
                 <form action="" method="post" class="form">
                     <h2 class="title">退会</h2>
                     <div class="area-msg">
                         <?php errForm($err_msg,'common'); ?>
                     </div>
                     <div class="btn-container">
                         <input type="submit" class="btn" value="退会する" name="submit">
                     </div>
                 </form>
             </div>
             <a href="mypage.php">&lt; マイページに戻る</a>
         </section>
     </div>

<!-- フッター -->
<?php 
require('footer.php');
?>
    
