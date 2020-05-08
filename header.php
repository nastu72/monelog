<header>
    <div class="site-width">
        <!-- タイトル -->
        <h1><a href="mypage.php">monelog</a></h1>
        <!-- ヘッダーナビ -->
        <nav id="top-nav">
            <div id="nav-drawer">
                <input id="nav-input" type="checkbox" class="nav-unshown">
                <label id="nav-open" for="nav-input"><span></span></label>
                <label class="nav-unshown" id="nav-close" for="nav-input"></label>
                <div id="nav-content">
                    <ul>
                        <?php
                        if(empty($_SESSION['user_id'])){
                        ?>
                        <li><a href="signup.php">新規登録</a></li>
                        <li><a href="login.php" class="login">ログイン</a></li>
                        <?php 
                        }else{
                        ?>
                        <li><a href="expense.php">支出の登録</a></li>
                        <li><a href="income.php">収入の登録</a></li>
                        <li><a href="mypage.php">マイページ</a></li>
                        <li><a href="logout.php" class="logout">ログアウト</a></li>
                        <li><a href="passEdit.php">パスワード変更</a></li>
                        <li><a href="withdraw.php">退会</a></li>
                        <?php
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </nav>
    </div>

</header>
