<?php 

//共通変数・関数ファイルの読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワード再発行メール送信ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// 画面処理
//================================
//post送信されていた場合
if(!empty($_POST)){
    debug('POST送信があります。');
    debug('POST情報：'.print_r($_POST,true));

    //変数にPOST情報代入
    $email = $_POST['email'];

    // 未入力チェック
    validRequired($email,'email');

    if(empty($err_msg)){
        debug('未入力チェックOK');

        // emailの形式チェック
        validEmail($email,'email');
        // emailの最大文字数チェック
        validMaxLen($email,'email');

        if(empty($err_msg)){
            debug('バリデーションOK');

            // 例外処理
            try{
                $dbh = dbConnect();
                // POST送信されたemailと合致するレコードの件数を取得
                $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
                $data = array(':email' => $email);
                $stmt = queryPost($dbh,$sql,$data);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                debug('$resultの中身：'.print_r($result,true));

                // EmailがDBに登録されていた場合
                if($stmt && array_shift($result)){
                    debug('クエリ成功。DB登録のあるアドレスです。');
                    $_SESSION['msg_success'] = SUC02; //メッセージ表示：メールを送信しました。

                    $auth_key = makeRandKey(); //認証キー生成

                    // メールを送信
                    $from = 'info@monelog.com';
                    $to = $email;
                    $subject = '【パスワード再発行認証】｜monelog';
                    $comment = <<<EOT
本メールアドレス宛にパスワード再発行のご依頼がありました。
下記のURLにて認証キーをご入力頂くとパスワードが再発行されます。

パスワード再発行認証キー入力ページ：http://localhost:8888/portfolio/monelog/passRemindRecieve.php
認証キー：{$auth_key}
※認証キーの有効期限は30分となります

認証キーを再発行されたい場合は下記ページより再度再発行をお願い致します。
http://localhost:8888/portfolio/monelog/passRemindSend.php

////////////////////////////////////////
monelog事務局
URL  http://monelog.com/
E-mail info@monelog.com
////////////////////////////////////////
EOT;

                    sendMail($from, $to, $subject, $comment);

                    //認証に必要な情報をセッションへ保存
                    $_SESSION['auth_key'] = $auth_key;
                    $_SESSION['auth_email'] = $email;
                    $_SESSION['auth_key_limit'] = time()+(60*30); //認証キーの有効期限30分に
                    debug('$_SESSIONの中身：'.print_r($_SESSION,true));

                    // 認証キー入力ページへ
                    header("Location:passRemindRecieve.php");

                    
                }else{
                    debug('クエリに失敗したかDBに登録のないEmailが入力されました。');
                    $err_msg['common'] = MSG07;
                }

            }catch(Exception $e){
                error_log('エラー発生：'.$e->getMessage());
                $err_msg['common'] = MSG07;
            }
        }
    }
}

?>

<?php 
$siteTitle = 'パスワード再発行メール送信';
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
                <p>ご指定のメールアドレス宛にパスワード再発行のURLと認証キーをお送り致します。</p>
                <div class="area-msg">
                    <?php errForm($err_msg,'common'); ?>
                </div>
                <!-- Emailフォーム -->
                <label class="<?php clsPrus($err_msg,'email'); ?>">
                    Email <span class="area-msg"><?php errForm($err_msg,'email'); ?></span>
                    <input type="text" name="email" value="<?php formHold($_POST,'email'); ?>">
                </label>
                <div class="btn-container">
                    <input type="submit" class="btn" value="送信する">
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
