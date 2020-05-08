<?php 

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワード変更ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//================================
// 画面処理
//================================
// DBからユーザーデータを取得
$userData = getUser($_SESSION['user_id']);
debug('取得したユーザー情報：'.print_r($userData,true));

// post送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります。');
  debug('POST情報：'.print_r($_POST,true));

    // 変数にユーザー情報を代入
    $pass_old = $_POST['pass_old'];
    $pass_new = $_POST['pass_new'];
    $pass_new_re = $_POST['pass_new_re'];

    // 未入力チェック
    validRequired($pass_old,'pass_old');
    validRequired($pass_new,'pass_new');
    validRequired($pass_new_re,'pass_new_re');

    if(empty($err_msg)){
    debug('未入力チェックOK！');

        // 古いパスワードのチェック
        validPass($pass_old,'pass_old');
        // 新しいパスワードのチェック
        validPass($pass_new,'pass_new');

        // 古いパスワードとDBパスワードを照合
        if(!password_verify($pass_old,$userData['password'])){
            $err_msg['pass_old'] = MSG12;
        }

        // 新しいパスワードと古いパスワードが一緒でないかチェック
        if($pass_old === $pass_new){
            $err_msg['pass_new'] = MSG13;
        }

        // 新しいパスワードとパスワード（再入力）が同じかどうかチェック
        validMatch($pass_new,$pass_new_re,'pass_new_re');

        if(empty($err_msg)){
            debug('バリデーションOK');

            // 例外処理
            try{
                $dbh = dbConnect();
                $sql = 'UPDATE users SET password = :pass WHERE id = :id';
                $data = array(':id' => $_SESSION['user_id'], ':pass' => password_hash($pass_new,PASSWORD_DEFAULT));
                $stmt = queryPost($dbh, $sql, $data);

                // クエリ成功の場合
                if($stmt){
                    // サクセスメッセージを表示する準備(マイページ遷移時に表示)
                    $_SESSION['msg_success'] = SUC01;

                    // メールを送信
                    $username = ($userData['username']) ? $userData['username'] : '名無し';
                    $from = 'info@monelog.com';
                    $to = $userData['email'];
                    $subject = 'パスワード変更通知｜monelog';
                    $comment = <<<EOT
{$username}　さん
パスワードが変更されました。

////////////////////////////////////////
monelog事務局
URL  http://monelog.com/
E-mail info@monelog.com
////////////////////////////////////////
EOT;
                    sendMail($from, $to, $subject, $comment);

                    header("Location:mypage.php");  //マイページへ

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
$siteTitle = 'パスワード変更';
require('head.php');
?>

<body class="page-1colum">
    
    <!-- メニュー -->
    <?php 
    require('header.php');
    ?>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">
        <h2 class="title page-title">パスワード変更</h2>
        <!-- Main -->
        <section id="main">
            <div class="form-container">
                <form action="" method="post" class="form">
                    <div class="area-msg">
                        <?php errForm($err_msg,'commn'); ?>
                    </div>
                    <!-- 古いパスワードフォーム -->
                    <label class="<?php clsPrus($err_msg,'pass_old'); ?>">
                        古いパスワード <span class="area-msg"><?php errForm($err_msg,'pass_old'); ?></span>
                        <input type="password" name="pass_old" value="<?php formHold($_POST,'pass_old'); ?>">
                    </label>
                    <!-- 新しいパスワードフォーム -->
                    <label class="<?php clsPrus($err_msg,'pass_new'); ?>">
                        新しいパスワード <span class="area-msg"><?php errForm($err_msg,'pass_new'); ?></span>
                        <input type="password" name="pass_new" value="<?php formHold($_POST,'pass_new'); ?>">
                    </label>
                    <!-- 新しいパスワード(再入力）フォーム -->
                    <label class="<?php clsPrus($err_msg,'pass_new_re'); ?>">
                        新しいパスワード（再入力） <span class="area-msg"><?php errForm($err_msg,'pass_new_re'); ?></span>
                        <input type="password" name="pass_new_re" value="<?php formHold($_POST,'pass_new_re'); ?>">
                    </label>
                    <!-- 送信ボタン -->
                    <div class="btn-container">
                        <input type="submit" class="btn" value="変更する">
                    </div>
                </form>
            </div>

        </section>
    </div>

<!-- フッター -->
<?php 
require('footer.php');
?>
