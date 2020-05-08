<?php 

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワード再発行認証キー入力ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// SESSIONに認証キーがあるかの確認（なければリダイレクト）
if(empty($_SESSION['auth_key'])){
    header("Location:passRemindSend.php"); //認証キー送信ページへ
}

//================================
// 画面処理
//================================
//post送信されていた場合
if(!empty($_POST)){
    debug('POST送信があります。');
    debug('POST情報：'.print_r($_POST,true));

    // 変数に認証キーを代入
    $auth_key = $_POST['token'];

    // 未入力チェック
    validRequired($auth_key,'token');

    if(empty($err_msg)){
        debug('未入力チェックOK');

        // 固定長チェック
        validLength($auth_key,'token');
        // 半角チェック
        validHalf($auth_key,'token');

        if(empty($err_msg)){
            debug('バリデーションOK');

            // POST送信された認証キーとpassRemindSend.phpで詰めた$_SESSIONの認証キーが同じかチェック
            if($auth_key !== $_SESSION['auth_key']){
                $err_msg['common'] = MSG15;
            }
            // 認証キーが有効期限内かどうかチェック
            if(time() > $_SESSION['auth_key_limit']){
                $err_msg['common'] = MSG16;
            }

            if(empty($err_msg)){
                debug('認証OK！');

                $pass = makeRandKey(); //パスワード生成

                // 例外処理
                try{
                    $dbh = dbConnect();
                    $sql = 'UPDATE users SET password = :pass WHERE email = :email AND delete_flg = 0';
                    $data = array(':email' => $_SESSION['auth_email'],'pass' => password_hash($pass,PASSWORD_DEFAULT));
                    $stmt = queryPost($dbh,$sql,$data);

                    // クエリ成功の場合、メールを送信（再発行のパスワードを発行）、セッション削除してログインページへ
                    if($stmt){
                        debug('【クエリ成功】新しいパスワードに更新完了');

                        // メール送信
                        $from = 'info@monelog.com';
                        $to = $_SESSION['auth_email'];
                        $subject = '【パスワード再発行完了】｜monelog';
                        $comment = <<<EOT
本メールアドレス宛にパスワードの再発行を致しました。
下記のURLにて再発行パスワードをご入力頂き、ログインください。
                        
ログインページ：http://localhost:8888/portfolio/monelog/login.php
再発行パスワード：{$pass}
※ログイン後、パスワードのご変更をお願い致します
                        
////////////////////////////////////////
monelog事務局
URL  http://monelog.com/
E-mail info@monelog.com
////////////////////////////////////////
EOT;
                        sendMail($from, $to, $subject, $comment);

                        // セッション削除（画面上にメッセージを表示させるためunsetを使う）
                        session_unset();
                        //画面上に「メールを送信しました。」のメッセージを表示
                        $_SESSION['msg_success'] = SUC02;
                        debug('セッション変数の中身：'.print_r($_SESSION,true));

                        header("Location:login.php");  //ログインページへ

                    }else{
                        debug('クエリ失敗！（パスワード更新できなかった）');
                        $err_msg['common'] = MSG07;
                    }

                }catch(Exception $e){
                    error_log('エラー発生：'.$e->getMessage());
                    $err_msg['common'] = MSG07;
                }
            }
        }
    }
}

?>

<?php 
$siteTitle = 'パスワード再発行認証';
require('head.php');
?>

<body class="page-1colum">
    
    <!-- メニュー -->
    <?php 
    require('header.php');
    ?>
    <p id="js-show-msg" style="display:none;" class="msg-slide">
        <?php echo getSessionFlash('msg_success'); ?>
    </p>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">

    <!-- Main -->
        <section id="main">

            <div class="form-container">
                <form action="" method="post" class="form">
                    <p>ご指定のメールアドレスお送りした【パスワード再発行認証】メール内にある「認証キー」をご入力ください。</p>
                    <div class="area-msg">
                        <?php errForm($err_msg,'common'); ?>
                    </div>
                    <!-- 認証キー入力フォーム -->
                    <label class="<?php clsPrus($err_msg,'token'); ?>">
                        認証キー <span class="area-msg"><?php errForm($err_msg,'token'); ?></span>
                        <input type="text" name="token" value="<?php formHold($_POST,'token'); ?>">
                    </label>
                    <!-- 再発行ボタン -->
                    <div class="btn-container">
                        <input type="submit" class="btn" value="再発行する">
                    </div>               
                </form>
            </div>
            <a href="passRemindSend.php">&lt; パスワード再発行メールを再度送信する</a>
        </section>
    
    </div>

<!-- フッター -->
<?php 
require('footer.php');
?>
