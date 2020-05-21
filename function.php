<?php 

//================================
// ログ
//================================
//ログを取るか
ini_set('log_errors','on');
//ログの出力ファイルを指定
ini_set('error_log','php.log');

//================================
// デバッグ
//================================
//デバッグフラグ
$debug_flg = true;
//デバッグログ関数
function debug($str){
    global $debug_flg;
    if(!empty($debug_flg)){
        error_log('デバッグ：'.$str);
    }
}

//================================
// セッション準備・セッション有効期限を延ばす
//================================
session_save_path("/var/tmp/");
ini_set('session.gc_maxlifetime',60*60*24*30);
ini_set('session.cookie_lifetime',60*60*24*30);
session_start();
session_regenerate_id();

//================================
// 画面表示処理開始ログ吐き出し関数
//================================
function debugLogStart(){
    debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> 画面表示処理開始');
    debug('セッションID：'.session_id());
    debug('セッション変数の中身：'.print_r($_SESSION,true));
    debug('現在日時タイムスタンプ：'.time());
    if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])){
        debug('ログイン期限日時タイムスタンプ：'.($_SESSION['login_date'] + $_SESSION['login_limit']));
    }
}


//================================
// 定数
//================================
//エラーメッセージを定数に設定
define('MSG01','入力必須です。');
define('MSG02', 'Emailの形式で入力してください。');
define('MSG03','パスワード（再入力）が違います。');
define('MSG04','半角英数字のみ入力可能です。');
define('MSG05','6文字以上で入力してください。');
define('MSG06','255文字以内で入力してください。');
define('MSG07','エラーが発生しました。しばらく経ってからやり直してください。');
define('MSG08', 'そのEmailは既に登録されています。');
define('MSG09', 'メールアドレスまたはパスワードが違います。');
define('MSG10', '正しくありません。');
define('MSG11', '半角数字で入力してください。');
define('MSG12', '古いパスワードが違います。');
define('MSG13', '古いパスワードと同じです。');
define('MSG14', '文字で入力してください。');
define('MSG15', '正しくありません。');
define('MSG16', '有効期限が切れています。');
define('SUC01', 'パスワードを変更しました。');
define('SUC02', 'メールを送信しました。');
define('SUC03', '登録しました。');
define('SUC04', '削除しました。');


//================================
// グローバル変数
//================================
//エラーメッセージ格納用の配列
$err_msg = array();

//================================
// バリデーション関数
//================================

//バリデーション関数（未入力チェック）
function validRequired($str,$key){
  if($str === ''){  //金額フォームなどを考えると数値の０はOKにし、空文字はダメにする
      global $err_msg;
      $err_msg[$key] = MSG01;
  }
}

//バリデーション関数（カテゴリフォームの未入力チェック）
function validRequiredCategory($str,$key){
    if(empty($str)){
        global $err_msg;
        $err_msg[$key] = MSG01;
    }
  }

//バリデーション関数（Email形式チェック）
function validEmail($str,$key){
    if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)){
        global $err_msg;
        $err_msg[$key] = MSG02;
    }
}

// バリデーション関数（Email重複チェック）
function validEmailDup($email){
    global $err_msg;
    // 例外処理
    try{
        $dbh = dbConnect();
        // usersテーブルのemailカラムにpost送信されたemailと一致するものの件数を検索
        $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
        $data = array(':email' => $email);
        $stmt = queryPost($dbh,$sql,$data);
          debug('$stmtの中身：'.print_r($stmt,true));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
          debug('$resultの中身：'.print_r($result,true));

        // 重複していたらエラーメッセージを表示
        if(!empty(array_shift($result))){
            $err_msg['email'] = MSG08;
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
        $err_msg['email'] = MSG07;
    }
}

// バリデーション関数（同値チェック）
function validMatch($str1,$str2,$key){
    if($str1 !== $str2){
        global $err_msg;
        $err_msg[$key] = MSG03;
    }
}

//バリデーション関数（最小文字数チェック）
function validMinLen($str,$key,$min = 6){
    if(mb_strlen($str) < $min){
        global $err_msg;
        $err_msg[$key] = MSG05;
    }
}

//バリデーション関数（最大文字数チェック）
function validMaxLen($str,$key,$max = 256){
    if(mb_strlen($str) > $max){
        global $err_msg;
        $err_msg[$key] = MSG06;
    }
}

//バリデーション関数（半角英数字チェック）
function validHalf($str,$key){
    if(!preg_match("/^[a-zA-Z0-9]+$/", $str)){
        global $err_msg;
        $err_msg[$key] = MSG04;
    }
}

// バリデーション関数（パスワードチェック）
function validPass($str,$key){
    // 半角英数字チェック
    validHalf($str,$key);
    // 最大文字数チェック
    validMaxLen($str,$key);
    // 最小文字数チェック
    validMinLen($str,$key);
}

// バリデーション関数（半角数字チェックprice）
function validNumber($str,$key){
    if(!preg_match("/^[0-9]+$/", $str)){
        global $err_msg;
        $err_msg[$key] = MSG11;
    }
}

//バリデーション関数（selectboxチェック）
function validSelect($str, $key){
    if(!preg_match("/^[0-9]+$/", $str)){
      global $err_msg;
      $err_msg[$key] = MSG10;
    }
  }


// バリデーション関数（固定長チェック）
function validLength($str,$key,$len = 8){
    if(mb_strlen($str) !== $len){
        global $err_msg;
        $err_msg[$key] = $len.MSG14;
    }
}

//================================
// エラー時のメッセージ関数
//================================
//HTML：エラーメッセージの表示
function errForm($str,$key){
    if(!empty($str[$key])) echo $str[$key];
}

//HTML：エラー時にclass='err'を付与（エラー部分のフォームが赤くなる）
function clsPrus($str,$key){
    if(!empty($str[$key])) echo 'err';
}

//HTML：POST送信時の内容保持
function formHold($str,$key){
    if(!empty($str[$key])) echo $str[$key];
}

//================================
// ログイン認証
//================================
// ゴミ箱機能時に使用（ログインしてない場合でもログインページに飛ばないように。DB処理をしないだけの状態にする）
function isLogin(){
    // ログインしている場合
    if( !empty($_SESSION['login_date']) ){
      debug('ログイン済みユーザーです。');
  
      // 現在日時が最終ログイン日時＋有効期限を超えていた場合
      if( ($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
        debug('ログイン有効期限オーバーです。');
  
        // セッションを削除（ログアウトする）
        session_destroy();
        return false;
      }else{
        debug('ログイン有効期限以内です。');
        return true;
      }
  
    }else{
      debug('未ログインユーザーです。');
      return false;
    }
  }
  
//================================
// データベース
//================================
//DB接続関数
function dbConnect(){
    //DBへの接続準備
    $dsn = 'mysql:dbname=monelog;host=localhost;charset=utf8';
    $user = 'root';
    $password = 'root';
    $options = array(
      PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    );
    // PDOオブジェクト生成（DBへ接続）
    $dbh = new PDO($dsn, $user, $password, $options);
    return $dbh;
  }

  //クエリを作成し実行する関数
  function queryPost($dbh,$sql,$data){
    //クエリ作成
    $stmt = $dbh->prepare($sql);
    // $dataの実行に失敗した場合
    if(!$stmt->execute($data)){
        debug('クエリに失敗しました。');
        debug('失敗したSQL：'.print_r($stmt,true));
        $err_msg['common'] = MSG07;
        return 0;
    }
    debug('クエリ成功');
    return $stmt;
  }

//   DBに登録された支出を検索する関数
  function getExpense($u_id,$e_id){
    debug('支出情報を取得します。');
    debug('ユーザーID：'.$u_id);
    debug('支出ID：'.$e_id);

    // 例外処理
    try{
        $dbh = dbConnect();
        $sql = 'SELECT * FROM expense WHERE user_id = :u_id AND id = :e_id AND delete_flg =0';
        $data = array(':u_id'=>$u_id,':e_id'=>$e_id);
        $stmt = queryPost($dbh,$sql,$data);
        debug('$stmtの中身：'.print_r($stmt,true));

        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }

    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
  }

// DBに登録されたユーザー情報の取得（パスワード変更用）
function getUser($u_id){
    debug('ユーザー情報を取得します。');

    //例外処理
  try {
      $dbh = dbConnect();
      $sql = 'SELECT * FROM users  WHERE id = :u_id AND delete_flg = 0';
      $data = array(':u_id' => $u_id);
      $stmt = queryPost($dbh, $sql, $data);
      debug('$stmtの中身：'.print_r($stmt,true));

      // クエリ結果のデータを１レコード返却
      if($stmt){
        return $stmt->fetch(PDO::FETCH_ASSOC);
      }else{
        return false;
      }
      
  }catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

//   DBに登録された収入を検索する関数
function getIncome($u_id,$i_id){
    debug('収入情報を取得します。');
    debug('ユーザーID：'.$u_id);
    debug('収入ID：'.$i_id);

    // 例外処理
    try{
        $dbh = dbConnect();
        $sql = 'SELECT * FROM income WHERE user_id=:id AND id=:i_id AND delete_flg=0';
        $data = array(':id'=>$u_id,':i_id'=>$i_id);
        $stmt = queryPost($dbh,$sql,$data);
        debug('$stmtの中身：'.print_r($stmt,true));

        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);  // クエリ結果のデータを１レコード返却
        }else{
            return false;    
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

//   DBに登録されたカテゴリー情報を取得する関数（支出）
function getCategoryExpense(){
    debug('カテゴリー情報を取得します。');
    try{
        $dbh = dbConnect();
        $sql ='SELECT * FROM category_expense';
        $data = array();
        $stmt = queryPost($dbh, $sql, $data);
        debug('$stmtの中身：'.print_r($stmt,true));

        if($stmt){
            return $stmt->fetchAll();
        }else{
            return false;
        }

    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

//   DBに登録されたカテゴリー情報を取得する関数（収入）
function getCategoryIncome(){
    //例外処理
    try{
        $dbh = dbConnect();
        $sql = 'SELECT * FROM category_income';
        $data = array();
        $stmt = queryPost($dbh,$sql,$data);

        if($stmt){
            return $stmt->fetchAll();
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

// 支出情報の取得(マイページ用)
function getMyExpense($u_id){
    debug('支出情報を取得します。');
    debug('ユーザーID：'.$u_id);

    try{
        $dbh = dbConnect();
        $sql = 'SELECT e.id, e.day, e.name, e.price, e.user_id, e.create_date, e.update_date, ce.name AS category FROM expense AS e LEFT JOIN category_expense AS ce ON e.category_id = ce.id WHERE user_id = :u_id AND e.delete_flg = 0 AND ce.delete_flg = 0 AND month(day) = month(now())';
        $data = array(':u_id' => $u_id);
        $stmt = queryPost($dbh,$sql,$data);

        if($stmt){
            return $stmt -> fetchAll();
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}


// 収入情報の取得(マイページ用)
function getMyIncome($u_id){
    debug('収入情報を取得します。');
    debug('ユーザーID：'.$u_id);
    
    try{
        $dbh = dbConnect();
        // 収入の情報（詳細とカテゴリ）を結合
        $sql = 'SELECT i.id, i.day, i.name, i.price, i.user_id, i.create_date, i.update_date, ci.name AS category FROM income AS i LEFT JOIN category_income AS ci ON i.category_id = ci.id WHERE user_id = :u_id AND i.delete_flg = 0 AND ci.delete_flg = 0 AND month(day) = month(now())';
        $data = array(':u_id' => $u_id);
        $stmt = queryPost($dbh,$sql,$data);

        if($stmt){
            return $stmt -> fetchAll();
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

// マイページの収支合計の算出
function priceSum($str1,$str2){
    $sum1 = 0;
    $sum2 = 0;
    foreach($str1 as $key => $val){
    $sum1 += $val['price'];}
    debug('$sum1の中身：'.$sum1);
    foreach($str2 as $key => $val){
    $sum2 += $val['price'];}
    debug('$sum2の中身：'.$sum2);
    echo number_format($sum1 - $sum2);  
}



//================================
// メール送信
//================================
function sendMail($from, $to, $subject, $comment){
    if(!empty($to) && !empty($subject) && !empty($comment)){
        // 文字化け防止
        mb_language("Japanese");
        mb_internal_encoding("UTF-8");

        // メールを送信
        $result = mb_send_mail($to, $subject, $comment, "From:".$from);

        if($result){
            debug('メールを送信しました。');
        }else{
            debug('エラー発生：メールの送信に失敗しました。');
        }        
    }
}

//================================
// その他
//================================
// サニタイズ(無害化)
function sanitize($str){
    return htmlspecialchars($str,ENT_QUOTES);
}

// フォーム入力保持
function getFormData($str,$flg = false){
    if($flg){
        $method = $_GET;
    }else{
        $method = $_POST;
    }
    global $dbFormData;
    // DBに登録された情報がある場合（編集画面）
    if(!empty($dbFormData)){
        // フォームのエラーがある場合（=POST送信されている）
        if(!empty($err_msg[$str])){
            // POSTにデータがある場合
            if(isset($method[$str])){
                return sanitize($method[$str]);
            }else{
                // （あり得ないが）POSTにデータがない場合はDBの情報を表示
                return sanitize($dbFormData[$str]);
            }
        }else{
            // POSTにデータがあり、DBの情報と違う場合
            if(isset($method[$str]) && $method[$str] !== $dbFormData[$str]){
                return sanitize($method[$str]);
            }else{
                return sanitize($dbFormData[$str]);
            }  
        }
    }else{
        // その他（DBに登録された情報がない場合など）
        if(isset($method[$str])){
            return sanitize($method[$str]);
        }
    }
}

//sessionを1回だけ取得、その後に消す（サクセスメッセージ用）
function getSessionFlash($key){
    if(!empty($_SESSION[$key])){
        $data = $_SESSION[$key];
        $_SESSION[$key] = ''; //中身を空に（消す）
        return $data;
    }
}


// 認証キー生成
function makeRandKey($length = 8){
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
    $str = ''; //変数の初期化（同じ文字が連続してもいいように）
    for($i = 0;$i<$length;++$i){
        $str .= $chars[mt_rand(0,61)];
    }
    return $str;
}

//画像処理
function uploadImg($file,$key){
    debug('画像アップロード処理スタート！');
    debug('FILE情報：'.print_r($file,true));

    if(isset($file['error']) && is_int($file['error'])){
    try{
        // バリデーション実行（例外が発生したらRuntimeExveptionでcatchの処理へ）
        // ①エラーの種類をチェック
        switch($file['error']){
            case UPLOAD_ERR_OK; //エラーはなくアップロード成功（値：0）
            break;
            case UPLOAD_ERR_INI_SIZE:  //php.ini定義の最大サイズ超過
            case UPLOAD_ERR_FORM_SIZE: //フォーム定義の最大サイズ超過
            throw new RuntimeException('ファイルサイズが大きすぎます。');
            default: //その他の場合
            throw new RuntimeException('その他のエラーが発生しました。');
        }
        // ②MINEタイプをチェックして偽装防止
        $type = @exif_imagetype($file['tmp_name']);
        if(!in_array($type,[IMAGETYPE_GIF,IMAGETYPE_JPEG,IMAGETYPE_PNG],true)){
            throw new RuntimeException('画像形式が未対応です。');
        }
        // ③ファイル名をハッシュ化してファイルを移動
        $path = 'uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
        if(!move_uploaded_file($file['tmp_name'],$path)){
            throw new RuntimeException('ファイル保存時にエラー発生');
        }

        // 保存したファイルのパーミッション（権限）を変更
        chmod($path,0644);

        debug('ファイルは正常にアップロードされました');
        debug('ファイルパス：',$path);
        return $path;

    }catch(RuntimeException $e){
        debug($e->getMessage());
        global $err_msg;
        $err_msg[$key] = $e->getMessage();
    }
  }
}

//GETパラメータ付与(URLの作成)
// $del_key : 付与から取り除きたいGETパラメータのキー
// $_GETがあればforeachで展開し、$keyの値が$arr_del_keyに含まれなければ$str（=?）に$keyと$valを追加してURLを生成している。
function appendGetParam($arr_del_key = array()){  //配列の形に
    if(!empty($_GET)){
        $str = '?';
        foreach($_GET as $key => $val){
            if(!in_array($key,$arr_del_key,true)){
                $str .=  $key. '='.$val.'&';
            }
        }
        $str = mb_substr($str,0,-1,"UTF-8");
        return $str; 
    }
}
