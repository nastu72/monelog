<?php 

// 共通変数・関数ファイルの読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ユーザー登録ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// post送信されていた場合
if(!empty($_POST)){
  
  //変数にユーザー情報を代入
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_re = $_POST['pass_re'];

  //未入力チェック
  validRequired($email,'email');
  validRequired($pass,'pass');
  validRequired($pass_re,'pass_re');

  if(empty($err_msg)){
    //emailの形式チェック
    validEmail($email,'email');
    // emailの最大文字数チェック
    validMaxLen($email,'email');
    // emailの重複チェック
    validEmailDup($email);

    // パスワードチェック（半角・最大・最小）
    validPass($pass,'pass');

    if(empty($err_msg)){
        
        //パスワードとパスワード再入力が合っているかチェック
        validMatch($pass,$pass_re,'pass_re');
        
        if(empty($err_msg)){

            // 例外処理
            try{
                $dbh = dbConnect();
                $sql = 'INSERT INTO users (email,password,login_time,create_date) VALUES(:email,:pass,:login_time,:create_date)';
                $data = array(':email'=>$email,':pass'=>password_hash($pass, PASSWORD_DEFAULT),':login_time'=>date('Y-m-d H:i:s'),':create_date'=>date('Y-m-d H:i:s'));
                $stmt = queryPost($dbh,$sql,$data);

                // クエリ成功の場合（ユーザー登録がうまくいった場合）
                if($stmt){
                    // ログイン有効期限をデフォルトで1時間とする
                    $sesLimit = 60*60;
                    // 最終ログイン日時を現在時刻に
                    $_SESSION['login_date'] = time();
                    $_SESSION['login_limit'] = $sesLimit;
                    // ユーザー情報を格納
                    $_SESSION['user_id'] = $dbh->lastInsertId();

                    debug('セッション変数の中身：'.print_r($_SESSION,true));

                    header("Location:expense.php"); //支出登録ページへ
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
    $siteTitle = 'ユーザー登録';
    require('head.php');
?>

<body class="page-1colum">
    <!-- メニュー -->
    <?php 
    require('header.php');
    ?>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">
    <!-- メイン -->
      <section id="main">
        <div class="form-container">

         <form action="" method="post" class="form">
            <h2 class="site-title">monelog</h2>

            <div class="area-msg">
            <?php errForm($err_msg,'common'); ?>
            </div>

            <label class="<?php clsPrus($err_msg,'email'); ?>">
                Email <span class="area-msg"><?php errForm($err_msg,'email'); ?></span>
                <input type="text" name="email" value="<?php formHold($_POST,'email'); ?>">
            </label>

            
            <label class="<?php clsPrus($err_msg,'pass'); ?>">
                パスワード <span style="font-size:12px">※英数字6文字以上</span> <span class="area-msg"><?php errForm($err_msg,'pass'); ?></span>
                <input type="password" name="pass" value="<?php formHold($_POST,'pass'); ?>"> 
            </label>


            <label class="<?php clsPrus($err_msg,'pass_re'); ?>">
                パスワード（再入力）<span class="area-msg"><?php errForm($err_msg,'pass_re'); ?></span>
                <input type="password" name="pass_re" value="<?php formHold($_POST,'pass_re'); ?>">
            </label>


            <div class="btn-container">
             <input type="submit" class="btn" value="新規登録">
            </div>

         </form>
        </div>
    
      </section>
  
    </div>

<!-- フッター -->
<?php 
    require('footer.php');
?>
