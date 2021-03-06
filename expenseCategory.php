<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　支出カテゴリ新規登録ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//================================
// 画面処理
//================================
// POST送信されていた場合
if(!empty($_POST)){
    debug('POST送信があります。');
    debug('POST情報：'.print_r($_POST,true));

    // 変数にユーザー情報を代入
    $e_category = $_POST['e_category'];

    // 未入力チェック
    validRequired($e_category,'e_category');

    if(empty($err_msg)){
        debug('未入力チェックOK');

        // 最大文字数チェック
        validMaxLen($e_category,'e_category');

        if(empty($err_msg)){
            debug('バリデーションOK');

            // 例外処理
            try{
                $dbh = dbConnect();
                $sql = 'INSERT INTO category_expense(name, create_date) VALUES (:name, :date)';
                $data = array(':name' => $e_category, ':date' => date('Y-m-d H:i:s'));
                $stmt = queryPost($dbh, $sql, $data);

                // クエリ成功の場合
                if($stmt){
                    $_SESSION['msg_success'] = SUC03;
                    debug('支出入力ページに遷移します。');
                    header("Location:expense.php"); //支出入力ページへ
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
$siteTitle = '支出カテゴリ新規登録';
require('head.php');
?>

<body class="page-1colum">
    
    <!-- メニュー -->
    <?php 
    require('header.php');
    ?>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">
    <h2 class="title page-title">支出カテゴリの新規登録</h2>
        <!-- Main -->
        <section id="main">
            <div class="form-container">
                <form action="" method="post" class="form">
                    <div class="area-msg">
                        <?php errForm($err_msg,'common'); ?>
                    </div>
                    <!-- 新規登録するカテゴリ入力フォーム -->
                    <label class="<?php clsPrus($err_msg,'e_category'); ?>">
                        カテゴリ名 <span class="area-msg"><?php errForm($err_msg,'e_category'); ?></span>
                        <input type="text" name="e_category" value="<?php formHold($_POST,'e_category'); ?>">
                    </label>
                    <!-- 新規登録ボタン -->
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
