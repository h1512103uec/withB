<?php
    $filename="mission_2-6ver2.txt"; 
    $data = $_POST['data'];//htmlファイル部分より、名前とコメントの内容を配列で受け取る。
    $mode = $_POST['mode'];
    $edit_num=$_POST['edit_num'];
    $comment = str_replace(array("\r\n","\r","\n"),"<br>",$data[comment]);
//    echo "入力pass: ".$data[pass]."@@@@pass";
    
    switch($mode){
/*******************通常記入用の処理****************************/        
        case normal:                    
//            echo "mode: ".$mode."<br>";
            $fp=fopen($filename,'a'); //fopenのaモード（書き込みモード）でファイルを開く
            $num=0; //最初の行記入用＆num変数リセットのため
            $num=count(file($filename));//配列の個数をcount関数により数え上げる。

            fwrite($fp,++$num."<>".$data[name]."<>".$comment."<>".date("Y/m/d H:i:s")."<>".$data[pass]."<>".PHP_EOL);
            //番号<>名前<>コメント<>投稿時間<>pass<>改行文字　の順で書き込む
            fclose($fp); //fopenで開いたテキストファイルを閉じる
            break;
        
/*******************削除用の処理****************************/
        case delete:
            if(ctype_digit($data[delete])){           //ctype_digit()で変数が半角数字か判別
//                echo "mode:削除バグ".$mode."<br>";        
                $del_arr=file($filename); //file中身リセット前にデータ避難
            //file中身のリセット
                fclose(fopen($filename,'w'));
            //再度追記モードでファイルを開く
                $fp=fopen($filename,'a');        
                $num=0; //最初の行記入用＆num変数リセットのため
                $check_pass=0;
                
                foreach((array)$del_arr as $value){//ループ
                    $ret_delarr=explode("<>",$value); //del_arrからデリミタ<>を取り除く                                       
                    $data_resave=(count(file($filename))+1)."<>".$ret_delarr[1]."<>".$ret_delarr[2]."<>".$ret_delarr[3]."<>".$ret_delarr[4]."<>".PHP_EOL;
                    //番号<>名前<>コメント<>投稿時間<>pass<>
                    
                    if($data[delete]!=$ret_delarr[0] || $data[pass]!=$ret_delarr[4]){//削除対象行番号と今調べてる行番号が不一致 or 入力passと保存passが不一致 →そのまま                  
                        fwrite($fp,$data_resave);   //そのまま保存し直す
                        if( ($data[delete]==$ret_delarr[0]) && ($data[pass]!=$ret_delarr[4]) ){
                            $check_pass=1;
                        }//削除対象行番号が一致 && 入力passが不一致 →passエラー表示用check flagをonにする
                    }else{                          //削除対象行番号と今調べてる行番号が一致 and 入力passと保存passが一致 →削除
                        echo "<br>"."削除しました!";  
                    }
                }//ループ終わり
                fclose($fp); //fopenで開いたテキストファイルを閉じる
                if($check_pass===1){echo "<br>"."passwordが不正です"."<br>";}
            }else{echo "半角数字で入力してね！"."<br>";}         
            break;
            
/*******************コメント編集用の処理****************************/        
        case edit://編集モード時の処理はここ
            if(ctype_digit($data[edit])){               //ctype_digit()で変数が半角数字か判別
//                echo "mode: ".$mode."<br>";
                $flag_edit = 1;              
                $edit_arr=file($filename);
//echo"<br>";                
                foreach((array)$edit_arr as $value){
                    $ret_editarr = explode("<>",$value);
//echo "pass:".$ret_editarr."震える"."<br>";                    
                    if($ret_editarr[0] == $data[edit]){
                        if($data[pass]==$ret_editarr[4]){
                            $name_edit = $ret_editarr[1];//予め入力フォームで表示する用
                            $comment_edit = $ret_editarr[2];//予め入力フォームで表示する用
                            break;
                        }else{
                            $check_pass=1;
                            break;
                        }                        
                    }
                }
                if($check_pass===1){echo "<br>"."passwordが不正です"."<br>";}
            }else{echo "半角数字で入力してね！"."<br>";}          
            break;
            
/*******************コメント編集後送信の処理****************************/              
        case edited://編集後コメント送信の処理はここ
            $edited_arr=file($filename);
            
            //file中身のリセット
                fclose(fopen($filename,'w'));
            //再度追記モードでファイルを開く
                $fp=fopen($filename,'a');        
            //編集したい番号を取得し　ret_editedarr[0]（番号）と比較して一致すればコメント内容を新しく保存し直す
            foreach((array)$edited_arr as $value){
                $ret_editedarr=explode("<>",$value);
                if($edit_num===$ret_editedarr[0]){
                    fwrite($fp,$edit_num."<>".$data[name]."<>".$comment."<>".date("Y/m/d H:i:s")."<>".$data[pass]."<>".PHP_EOL);
                }else{
                    fputs($fp,$value);
                }
            }
            break;
    }              
?>

<!DOCTYPE html> 
	<head>
		<meta charset = "UFT-8">
		<title>旅ブログ</title>
	</head>

    <body>
		<h1>掲示板タイトル</h1>
		<hr>
        
        
<!--名前とコメント専用送信フォーム-->
        <p>
		<form action = "" method = "post">	<!--GET関数(URL内に記載する方式)で　mission_2-2.phpにて実行-->
            <h2>名前</h2>                                   <!--色やフォントは後ほどcssにて指定する。-->
            <input type = "text" name ="data[name]" required value="<?php echo "$name_edit";?>"><!--名前入力欄作成　配列[キー]に入力を格納-->
            <h2>コメント</h2>
            <textarea name ="data[comment]" required maxlength=128 cols=32 rows=4><?php echo str_replace("<br>","\n",$comment_edit);?></textarea><br><!--入力窓の作成　入力した内容は配列[キー]に代入-->

<!--編集後/ノーマルモードの判別処理　＆名前コメント入力フォーム--> 
<?php
    if($flag_edit!=1){
        $mode="normal";
        $switch="送信";
    }else{
        $mode="edited";
        $switch="訂正";
        $edit_num=$data[edit];
    }
?>
            <input type = "hidden" name="mode" value="<?php echo "$mode"; ?>">
            <input type = "hidden" name="edit_num" value="<?php echo"$edit_num";?>">
            編集/削除用パスワード
            <input type = "password" name="data[pass]" placeholder="password" required maxlength=4>
			<input type = "submit"  value ="<?php echo "$switch";?>">
            <!--送信ボタンの作成-->
        </form>
        </p>
  
        
<!--削除専用送信フォーム-->
        <p>        
        <h2>削除対象行番号</h2>
        <form action = "" method="post">
            <input type = "text" name="data[delete]" placeholder="半角数字を入力してください" required>
            <input type = "password" name="data[pass]" placeholder="password" required maxlength=4>     
            <input type = "hidden" name="mode" value="delete">
            <input type = "submit"  value="削除"><br>
		</form>
        </p>
        
        
<!--編集番号専用送信フォーム--> 
        <p>
        <h2>編集対象行番号</h2>       
        <form action = "" method="post">
            <input type="text" name="data[edit]" placeholder="半角数字を入力してください" required>
            <input type="password" name="data[pass]" placeholder="password" required maxlength=4>
            <input type="hidden" name="mode" value="edit">
            <input type="submit" value="編集"><br>
        </form>
        </p>
            
        <p>
            <a href="../">トップへ戻る</a>
            <a href="../mission_2-6ver2.txt">確認</a>
            <form action="" method="post">
                <input type="hidden" name="reset" value="reset">
                <input type="submit" value="ファイルリセット">
            </form>
            <?php
                $reset=$_POST['reset'];
                if($reset==="reset"){fclose(fopen($filename,'w'));}
            ?>
        </p>
        

<?php      
//以下結果画面表示用
    echo "mode: ".$mode."<br>";
    echo "<hr>";    
    $arr=file($filename); //配列arrに１行ずつ格納
    foreach((array)$arr as $value){
        $ret_arr=explode("<>",$value);
        echo "No.".$ret_arr[0]." 名前:".$ret_arr[1]." 投稿日時:".$ret_arr[3];
        echo "<br>";
        echo "@コメント内容@";
        echo "<br>";
        echo $ret_arr[2];//コメント
        echo "<hr>";
    }
?>

	</body>
</html>
