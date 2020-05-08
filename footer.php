<footer id="footer">
    Copyright monelog .All Right Reserved.
</footer>

<script src="js/jquery-3.4.1.min.js"></script>

<script>
    $(function(){

        // フッターを最下部に固定(①画面の高さ > ②フッタートップから上の高さの場合、フッタートップを①-②pxの位置に固定する)
        var $ftr = $('#footer');
        if(window.innerHeight > $ftr.offset().top + $ftr.outerHeight()){
        $ftr.attr({'style':'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) + 'px;'});
        }

        // メッセージ表示
        var $jsShowMsg = $('#js-show-msg');
        var msg = $jsShowMsg.text();  //$jsShowMsgの中身のメッセージを取得して代入
        // 半角・全角のスペースを取り除いた後に文字が入っていれば、メッセージを5秒表示
        if(msg.replace(/^[\s　]+|[\s　]+$/g, "").length){
            $jsShowMsg.slideToggle('slow');
            setTimeout(function(){ $jsShowMsg.slideToggle('slow'); }, 3000);
        }

        // 画像ライブレビュー（photoフォームで画像をscr属性にセットする）
        var $dropArea = $('.area-drop');
        var $fileInput = $('.input-file');

        // ①画像がドラッグ＆ドロップの上に乗った時のcssを変更
        $dropArea.on('dragover',function(e){
            e.stopPropagation();
            e.preventDefault();
            $(this).css('border','3px #ccc dashed');
        });
        // ②画像をドラッグ＆ドロップの上から離した時のcssを変更
        $dropArea.on('dragleave',function(e){
            e.stopPropagation();
            e.preventDefault();
            $(this).css('border','none');
        });
        // ③実際に画像プレビューするためにファイル情報を取得し、画像ファイルを文字列に変換してsrc属性にセット
        $fileInput.on('change',function(e){
            $dropArea.css('border','none');
            var file = this.files[0],  //files配列にファイルが入っている
                $img = $(this).siblings('.prev-img'),  //siblingsメソッドで$fileInput（＝.input-file）の兄弟のすべてのimgを取得
                fileReader = new FileReader(); //ファイルを読み込むFileReaderオブジェクト

            fileReader.onload = function(event){  //onload以下で、読み込みが完了したら実行したい処理を記述する
                $img.attr('src',event.target.result).show();  //読み込んだデータをimgに設定
            };

            fileReader.readAsDataURL(file);  //画像の読み込み
        });

        // ゴミ箱（登録した収入を削除）
        var $trash,
        trashId;
        $trash = $('.js-click-trash1')||null;
        trashId = $trash.data('trashid1')||null;

        if(trashId !== undefined && trashId !== null){
            $trash.on('click',function(){
                var $this = $(this);
                $.ajax({
                    type:"POST",
                    url:"ajaxTrashIncome.php",
                    data:{incomeId:trashId}
                }).done(function(data){
                    console.log('Ajax Success');
                    location.href = 'http://localhost:8888/portfolio/monelog/mypage.php'  //削除に成功したらマイページへ
                }).fail(function(msg){
                    console.log('Ajax Error');
                });
            });
        }

        // ゴミ箱（登録した支出を削除）
        var $trashExpense,
        trashIdExpense;
        $trashExpense = $('.js-click-trash2')||null;
        trashIdExpense = $trashExpense.data('trashid2')||null;

        if(trashIdExpense !== undefined && trashIdExpense !== null){
            $trashExpense.on('click',function(){
                var $this = $(this);
                $.ajax({
                    type:"POST",
                    url:"ajaxTrashExpense.php",
                    data:{expenseId:trashIdExpense}
                }).done(function(data){
                    console.log('Ajax Success');
                    location.href = 'http://localhost:8888/portfolio/monelog/mypage.php'  //削除に成功したらマイページへ
                }).fail(function(msg){
                    console.log('Ajax Error');
                });
            });
        }

    });

</script>
</body>
</html>
