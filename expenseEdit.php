<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　支出編集ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//================================
// 画面処理
//================================

// 画面表示用データ取得
//================================
// GETデータを格納
$e_id = (!empty($_GET['e_id'])) ? $_GET['e_id'] : '';
// DBから支出データを取得
$dbFormData = (!empty($e_id)) ? getExpense($_SESSION['user_id'],$e_id) : '';
// DBからカテゴリデータを取得
$dbCategoryData = getCategoryExpense();

debug('支出ID：'.$e_id);
debug('フォーム用DBデータ($dbFormData)：'.print_r($dbFormData,true));
debug('カテゴリデータ($dbCategoryData)：'.print_r($dbCategoryData,true));

// パラメータ改ざんチェック
//================================
if(!empty($e_id) && empty($dbFormData)){
    debug('GETパラメータの支出IDが違います。マイページへ遷移します。');
    header("Location:mypage.php"); //マイページへ
}

// POST送信時処理
//================================
if(!empty($_POST)){
    debug('POST送信があります。');
    debug('POST情報：'.print_r($_POST,true));
    debug('FILE情報：'.print_r($_FILES,true));  //pic用

    //変数にユーザー情報を代入
    $day = $_POST['day'];
    $category = $_POST['category_id'];
    $name = $_POST['name'];
    $price = (!empty($_POST['price'])) ? $_POST['price'] : 0;  //0や空欄の場合は0を入れる。（デフォルトのフォームには0が入っている）
    //画像をアップロードし、パスを格納
    $pic = (!empty($_FILES['pic']['name'])) ?  uploadImg($_FILES['pic'],'pic') : '';
    // 画像をPOSTしてない（登録していない）が既にDBに登録されている場合、DBのパスを入れる
    $pic = (empty($pic) && !empty($dbFormData['pic'])) ? $dbFormData['pic'] : $pic;

    // DBの情報と入力情報が異なる場合にバリデーションを行う
        if($dbFormData['day'] !== $day){
            //未入力チェック
            validRequired($day,'day');
        }
        if($dbFormData['category_id'] !== $category){
            //未入力チェック
            validRequiredCategory($category,'category_id');
            //セレクトボックスチェック
            validSelect($category, 'category_id');
        }
        if($dbFormData['name'] !== $name){
            //未入力チェック
            validRequired($name, 'name');
            //最大文字数チェック
            validMaxLen($name, 'name');
        }
        if($dbFormData['price'] != $price){
            //未入力チェック
            validRequired($price, 'price');
            //半角数字チェック
            validNumber($price, 'price');
        }
    

    if(empty($err_msg)){
        debug('バリデーションOKだよ〜！');

        try{
            $dbh = dbConnect();
            $sql = 'UPDATE expense SET day=:day,category_id=:category,name=:name,price=:price,pic=:pic WHERE user_id=:u_id AND id=:e_id';
            $data = array(':day'=>$day,':category'=>$category,':name'=>$name,':price'=>$price,':pic'=>$pic,':u_id'=>$_SESSION['user_id'],':e_id'=>$e_id);
            
            debug('SQL：'.$sql);
            debug('流し込みデータ：'.print_r($data,true));
            // クエリ実行
            $stmt = queryPost($dbh,$sql,$data);

            // クエリ成功の場合
            if($stmt){
                debug('マイページへ遷移します。');
                header("Location:mypage.php");  //マイページへ
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
$siteTitle = '支出の編集';
require('head.php');
?>

<body class="page-1colum">
    
    <!-- メニュー -->
    <?php 
    require('header.php');
    ?>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">
    <h2 class="title page-title">支出の編集</h2>
    <!-- Main -->
    <section id="main">
        <div class="form-container">
            <form action="" method="post" class="form" enctype="multipart/form-data">
                <!-- ゴミ箱（削除） -->
                <div class="gomi">
                <i class="fa fa-trash js-click-trash2"  data-trashid2="<?php echo sanitize($dbFormData['id']); ?>"></i>
                </div>
                <div class="area-msg">
                <?php errForm($err_msg,'common'); ?>
                </div>
                <!-- 日付フォーム -->
                <label class="<?php clsPrus($err_msg,'day'); ?>">
                日付 <span class="area-msg"><?php errForm($err_msg,'day'); ?></span>
                <input type="date" name="day" max="9999-12-31" value="<?php echo getFormData('day'); ?>">
                </label>
                <!-- カテゴリフォーム -->
                <label class="<?php clsPrus($err_msg,'category_id'); ?>">
                カテゴリ <span class="area-msg"><?php errForm($err_msg,'category_id'); ?></span>
                　　<!-- プルダウン -->
                  <select name="category_id">
                    <!-- 先頭 -->
                    <option value="0" <?php if(getFormData('category_id') == 0){ echo 'selected';} ?>>選択してください</option>
                    <!-- 2つ目以降 -->
                    <?php 
                    foreach($dbCategoryData as $key => $val){
                    ?>
                    <option value="<?php echo $val['id'] ?>" <?php if(getFormData('category_id') == $val['id']){ echo 'selected';} ?>>
                        <?php echo $val['name']; ?>
                    </option>
                    <?php 
                    }
                    ?>  
                  </select>
                </label>
                <!-- 内容フォーム -->
                <label class="<?php clsPrus($err_msg,'name'); ?>">
                    内容 <span class="area-msg"><?php errForm($err_msg,'name'); ?></span>
                    <input type="text" name="name" value="<?php echo getFormData('name'); ?>">
                </label>
                <!-- 金額フォーム -->
                <label class="<?php clsPrus($err_msg,'price'); ?>">
                    支出 <span class="area-msg"><?php errForm($err_msg,'price'); ?></span>
                    <div class="form-group">
                        <input type="text" name="price" style="width:200px" placeholder="0" value="<?php echo (!empty(getFormData('price'))) ? number_format(getFormData('price')) : ''; ?>"><span class="option">円</span>
                    </div>
                </label>
                <!-- photoフォーム -->
                <div class="imgDrop-container">
                    Photo <span class="area-msg"><?php errForm($err_msg,'pic'); ?></span>
                    <label class="area-drop <?php clsPrus($err_msg,'pic') ?>">
                        <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                        <input type="file" name="pic" class="input-file">
                        <img src="<?php echo getFormData('pic'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic'))) echo 'display:none'; ?>">
                        ドラッグ&ドロップ
                    </label>
                </div>

                <!-- 登録ボタン -->
                <div class="btn-container">
                    <input type="submit" class="btn" value="編集する">
                </div>
            </form>
        
        </div>
    
    </section>
     
    </div>

<!-- フッター -->
<?php 
require('footer.php');
?>
