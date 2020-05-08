<?php 

//================================
// ログイン認証・自動ログアウト
//================================
// ログインしている場合
if(!empty($_SESSION['login_date'])){
    debug('ログイン済みユーザーです。');

    // 現在日時が最終ログイン日時＋有効期限を超えていた場合(タイムオーバー)
    if($_SESSION['login_date'] + $_SESSION['login_limit'] < time()){
        debug('ログイン有効期限オーバー');

        // セッションを削除（ログアウトする）して、ログインページへ
        session_destroy();
        header("Location:login.php");
    }else{
        debug('ログイン有効期限内でOK！');
        //最終ログイン日時を現在日時に更新
        $_SESSION['login_time'] = time();

        // ログイン有効期限内にログインページにきたらマイページへ遷移させる
        if(basename($_SERVER['PHP_SELF']) === 'login.php'){
            debug('$_SERVERの中身：'.print_r($_SERVER,true));
            debug('支出ページへ遷移します。');
            header("Location:expense.php");
        }
    }
// 未ログインの場合はログインページへ
}else{
    debug('未ログインユーザーです。');
    if(basename($_SERVER['PHP_SELF']) !== 'login.php'){
        header("Location:login.php");
    }
}
