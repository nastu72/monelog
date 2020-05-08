<?php 

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　収入登録ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//================================
// 画面処理
//================================

// 画面表示用データ取得
//================================
// DBからカテゴリデータを取得
$dbCotegoryData = getCategoryIncome();

debug('カテゴリデータ：'.print_r($dbCotegoryData,true));


// POST送信時処理
//================================
if(!empty($_POST)){
    debug('POST送信があります。');
    debug('POST情報：'.print_r($_POST,true));

    //変数にユーザー情報を代入
    $day = $_POST['day'];
    $category = $_POST['category_id'];
    $name = $_POST['name'];
    $price = (!empty($_POST['price'])) ? $_POST['price'] : 0;  //0や空欄の場合は0を入れる。（デフォルトのフォームには0が入っている）

    
        //未入力チェック
        validRequired($day,'day');
        validRequiredCategory($category,'category_id');
        validRequired($name,'name');
        validRequired($price,'price');
        //最大文字数チェック
        validMaxLen($name,'name');
        //セレクトボックスチェック
        validSelect($category, 'category_id');
        //半角数字チェック
        validNumber($price, 'price');

    if(empty($err_msg)){
        debug('バリデーションOKだよ〜！');

        try{
            $dbh = dbConnect();
            $sql = 'INSERT INTO income(day,category_id,name,price,user_id,create_date) VALUES (:day,:category,:name,:price,:u_id,:date)';
            $data = array(':day'=>$day,':category'=>$category,':name'=>$name,':price'=>$price,':u_id'=>$_SESSION['user_id'],':date'=>date('Y-m-d H:i:s'));

            debug('SQL：'.$sql);
            debug('流し込みデータ：'.print_r($data,true));
            // クエリ実行
            $stmt = queryPost($dbh,$sql,$data);

            // クエリ成功の場合
            if($stmt){
                debug('マイページへ遷移します。');
                header("Location:mypage.php");  //自画面へ
            }

        }catch(Exception $e){
            error_log('エラー発生：'.$e->getMessage());
            $err_msg['common'] = MSG07;
        }
    }
}


debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
$siteTitle = '収入の新規登録';
require('head.php');
?>

<body class="page-1colum">
    
    <!-- メニュー -->
    <?php 
    require('header.php');
    ?>
    <!-- カテゴリ新規登録成功時のメッセージ表示用 -->
    <p id="js-show-msg" style="display:none;" class="msg-slide">
        <?php echo getSessionFlash('msg_success'); ?>
    </p>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">
        <h2 class="title page-title">【収入】いくら入った？</h2>
        <!-- Main -->
        <section id="main">
            <div class="form-container">
                <form action="" method="post" class="form">
                    <div class="area-msg">
                        <?php errForm($err_msg,'common'); ?>
                    </div>
                    <!-- 日付フォーム -->
                    <label class="<?php clsPrus($err_msg,'day'); ?>">
                        日付 <span class="area-msg"><?php errForm($err_msg,'day'); ?></span>
                        <input type="date" name="day" max="9999-12-31">
                    </label>
                    <!-- カテゴリフォーム -->
                    <label class="<?php clsPrus($err_msg,'category_id'); ?>">
                        カテゴリ <span class="area-msg"><?php errForm($err_msg,'category_id'); ?></span>
                        <!-- プルダウン -->
                        <select name="category_id">
                            <!-- 先頭 -->
                            <option value="0">選択してください</option>
                            <!-- 2つ目以降 -->
                            <?php
                            foreach($dbCotegoryData as $key => $val){
                            ?>
                                <option value="<?php echo $val['id']; ?>">
                                    <?php echo $val['name']; ?>
                                </option>
                            <?php 
                            }
                            ?>
                        </select>
                    </label>
                    <div class="new-category">
                    <a href="incomeCategory.php">収入カテゴリの新規登録</a>
                    </div>
                    <!-- 内容フォーム -->
                    <label class="<?php clsPrus($err_msg,'name'); ?> ">
                        内容 <span class="area-msg"><?php errForm($err_msg,'name'); ?></span>
                        <input type="text" name="name">
                    </label>
                    <!-- 金額フォーム -->
                    <label class="<?php clsPrus($err_msg,'price'); ?>">
                        収入 <span class="area-msg"><?php errForm($err_msg,'price'); ?></span>
                        <div class="form-group">
                            <input type="text" name="price" placeholder="0" style="width:200px"><span class="option">円</span>
                        </div>
                    </label>

                    <!-- 登録ボタン -->
                    <div class="btn-container">
                        <input type="submit" class="btn" value="登録する">
                    </div>

                </form>
            </div>

        </section>

    </div>

<!-- フッター -->
<?php 
require('footer.php');
?>
