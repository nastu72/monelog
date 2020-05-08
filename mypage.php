<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　マイページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// 画面処理
//================================
//ログイン認証
require('auth.php');

//================================
// 画面表示用データ取得
//================================
$u_id = $_SESSION['user_id'];
// DBから自分の収入データを取得
$incomeData = getMyIncome($u_id);
// DBから自分の支出データを取得
$expenseData = getMyExpense($u_id);

debug('取得した収入データ：'.print_r($incomeData,true));
debug('取得した支出データ：'.print_r($expenseData,true));



debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');

?>


<?php 
$siteTitle = 'マイページ';
require('head.php');
?>

<body class="page-1colum">
    
    <!-- メニュー -->
    <?php 
    require('header.php');
    ?>
    <!-- パスワード変更成功時のメッセージ表示用 -->
    <p id="js-show-msg" style="display:none;" class="msg-slide">
        <?php echo getSessionFlash('msg_success'); ?>
    </p>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">
        <div class="pic">
            <img src="img/chokin.png">
        </div>
        <h2 class="title page-title"><?php echo date('Y年m月分'); ?></h2>
        <p class="nakami">お財布の中身は・・・</p>
        <p class="money-total"><?php priceSum($incomeData,$expenseData); ?><span class="yen">円</span></p>
        
    <!-- Main -->
        <section id="main">
            <div class="form-container">
                <!-- 収支リスト -->
                <div class="contents">
                    <ul>
                        <li>日付</li>
                        <li>カテゴリ</li>
                        <li>内容</li>
                        <li>収入</li>
                        <li>支出</li>
                    </ul>
                </div>
                <div class="panel-list">
                    <!-- 収入リスト -->
                    <?php 
                    foreach($incomeData as $key => $val):
                    ?>
                    <div class="wallet-area">
                        <span class="icon i-icon">収入</span>
                        <a href="incomeEdit.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&i_id='.$val['id'] : '?i_id='.$val['id']; ?>" class="panel">
                            <ul class="panel-body">
                                <li><?php echo sanitize($val['day']); ?></li>
                                <li><?php echo sanitize($val['category']); ?></li>
                                <li><?php echo sanitize($val['name']); ?></li>
                                <li name="i-price">¥<?php echo sanitize(number_format($val['price'])); ?></li>
                            </ul> 
                        </a>
                    </div>    
                    <?php 
                    endforeach;
                    ?>

                    <!-- 支出リスト -->
                    <?php 
                    foreach($expenseData as $key => $val):
                    ?>
                    <div class="wallet-area">
                        <span class="icon e-icon">支出</span>
                        <a href="expenseEdit.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&e_id='.$val['id'] : '?e_id='.$val['id']; ?>" class="panel">
                            <ul class="panel-body">
                                <li><?php echo sanitize($val['day']); ?></li>
                                <li><?php echo sanitize($val['category']); ?></li>
                                <li><?php echo sanitize($val['name']); ?></li>
                                <li></li>
                                <li name="e-price">¥<?php echo sanitize(number_format($val['price'])); ?></li>
                            </ul>
                        </a>
                    </div>
                    <?php 
                    endforeach;
                    ?>
                </div>
            
            </div>
            
        </section>
    </div>

<!-- footer -->
<?php 
require('footer.php');
?>
