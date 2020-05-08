<?php

//共通変数・関数ファイルの読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ログインページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//================================
// ログイン画面処理
//================================
// post送信されていた場合
if(!empty($_POST)){
    debug('POST送信があります。');

    //変数にユーザー情報を代入
    $email = $_POST['email'];
    $pass = $_POST['pass'];
    $pass_save = (!empty($_POST['pass_save'])) ? true : false;

    // 未入力チェック
    validRequired($email,'email');
    validRequired($pass,'pass');

    if(empty($err_msg)){
        // emailの形式チェック
        validEmail($email,'email');
        // emailの最大文字数チェック
        validMaxLen($email,'email');

        // パスワードチェック（半角・最大・最小）
        validPass($pass,'pass');

        if(empty($err_msg)){
            debug('バリデーションOK');

            // 例外処理
            try{
                $dbh = dbConnect();
                $sql = 'SELECT password,id FROM users WHERE email = :email AND delete_flg = 0';
                $data = array(':email' => $email);
                $stmt = queryPost($dbh,$sql,$data);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                debug('クエリ結果の中身：'.print_r($result,true));

                // パスワード照合
                if(!empty($result) && password_verify($pass,array_shift($result))){
                    debug('パスワードがマッチしました。');

                    //ログイン有効期限（デフォルトを１時間とする）
                    $sesLimit = 60 * 60;
                    // 最終ログイン日時を現在日時に
                    $_SESSION['login_date'] = time(); 

                    // ログイン保持にチェックがある場合
                    if($pass_save){
                        debug('ログイン保持にチェックあり');
                        // ログイン有効期限を30日に
                        $_SESSION['login_limit'] = $sesLimit * 24 * 30;
                    }else{
                        debug('ログイン保持にチェックなし');
                        // 次回からログイン保持しないので、ログイン有効期限を1時間後に
                        $_SESSION['login_limit'] = $sesLimit;
                    }

                    // ユーザーIDを格納
                    $_SESSION['user_id'] = $result['id'];

                    debug('セッション変数の中身：'.print_r($_SESSION,true));
                    debug('支出ページへ遷移します。');

                    header("Location:mypage.php");  //マイページへ

                }else{
                    debug('パスワードが合致するものがありません。');
                    $err_msg['common'] = MSG09;
                }
                
            }catch(Exception $e){
                error_log('エラー発生：'.$e->getMessage());
                $err_msg['common'] = MSG07;
            }
        }
    }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');

?>

<?php 
$siteTitle = 'ログイン';
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
                <h2 class="title login-title">ログイン</h2>
                <div class="area-msg">
                    <?php errForm($err_msg,'common'); ?>
                </div>
                <!-- emailフォーム -->
                <label class="<?php clsPrus($err_msg,'email'); ?>">
                    Email <span class="area-msg"><?php errForm($err_msg,'email'); ?></span>
                    <input type="text" name="email" value="<?php formHold($_POST,'email'); ?>">
                </label>
                <!-- パスワードフォーム -->
                <label class="<?php clsPrus($err_msg,'pass'); ?>">
                    パスワード <span class="area-msg"><?php errForm($err_msg,'pass'); ?></span>
                    <input type="password" name="pass" value="<?php formHold($_POST,'pass'); ?>">
                </label>
                <!-- ログイン保持チェック -->
                <label>
                    <input type="checkbox" name="pass_save">次回ログインを省略する
                </label>
                <!-- ログインボタン -->
                <div class="btn-container">
                    <input type="submit" class="btn" value="ログイン">
                </div>
                パスワードを忘れた方は<a href="passRemindSend.php">こちら</a>
            
            </form>
        </div>
    </section>
  
  </div>

<!-- フッター -->
<?php 
require('footer.php');
?>
