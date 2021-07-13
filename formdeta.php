<?php
//ファイルのパス指定
define('FILENAME','./message.txt');

date_default_timezone_set('Asia/Tokyo');

// 変数の初期化
$current_date = null;
$data = null;
$file_handle = null;
$split_data = null;
$url = null;
$youtube_url = null;
$message = array();
$message_array = array();
$error_message = array();
$clean = array();
$pdo = null;
$stmt = null;
$res = null;
$option = null;

// データベースに接続
try{
    $option = array(
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
	);
    $pdo = new PDO('mysql:charset=UTF8;dbname=board;host=localhost', 'root', 'youtubelist',$option);
} catch(PDOException $e) {

    // 接続エラーのときエラー内容を取得する
    $error_message[] = $e->getMessage();
}


if(isset($_REQUEST["url_name"]) == true)
{
	/** 入力内容を取得 */
	$url = $youtube_url = $_REQUEST["url_name"];

	$url = htmlspecialchars($url, ENT_QUOTES);

	if (strpos($youtube_url, "watch") != false)	/* ページURL ? */
	{
		/** コードを変換 */
		$youtube_url = substr($youtube_url, (strpos($youtube_url, "=")+1));
	}
	else
	{
		/** 短縮URL用を変換 */
		$youtube_url = substr($youtube_url, (strpos($youtube_url, "youtu.be/")+9));
	}

}

if(!empty($_POST['btn_submit'])){
    //表示名の入力チェック
    if( empty($_POST['list_name']) ) {
		$error_message[] = 'プレイリスト名を入れてください。';
	}else {
		$clean['list_name'] = htmlspecialchars( $_POST['list_name'], ENT_QUOTES, 'UTF-8');
        $clean['list_name'] = preg_replace( '/\\r\\n|\\n|\\r/', '', $clean['list_name']);
	}

    if( empty($_POST['view_name']) ) {
		$clean['view_name'] = '名無しさん';
	}else {
		$clean['view_name'] = htmlspecialchars( $_POST['view_name'], ENT_QUOTES, 'UTF-8');
        $clean['view_name'] = preg_replace( '/\\r\\n|\\n|\\r/', '', $clean['view_name']);
	}

    if( !empty($_POST['message']) ) {
	$clean['message'] = htmlspecialchars( $_POST['message'], ENT_QUOTES, 'UTF-8');
        $clean['message'] = preg_replace( '/\\r\\n|\\n|\\r/', '<br>', $clean['message']);
	}
    
    if( empty($_POST['url_name']) ) {
		$error_message[] = 'URLを入れてください。';
	}else {
		$clean['url_name'] = htmlspecialchars( $_POST['url_name'], ENT_QUOTES, 'UTF-8');
	}
    if(empty($error_message)){
    /*
    if($file_handle = fopen(FILENAME,"a")){
        //書き込み日時を取得
        $current_date = date("Y-m-d H:i:s");
        //書き込むデータを作成
        $data = "'".$clean['list_name']."','".$clean['view_name']."','".$youtube_url."','".$clean['message']."','".$current_date."'\n";
        //書き込み
        fwrite( $file_handle, $data);

        fclose($file_handle);
    }*/
        // 書き込み日時を取得
            $current_date = date("Y-m-d H:i:s");

            // SQL作成
            $stmt = $pdo->prepare("INSERT INTO message (list_name, view_name, youtube_url, message, post_date) VALUES (:list_name, :view_name, :youtube_url, :message, :post_date)");

            // 値をセット
            $stmt->bindParam( ':list_name', $clean['list_name'], PDO::PARAM_STR);
            $stmt->bindParam( ':view_name', $clean['view_name'], PDO::PARAM_STR);
            $stmt->bindParam( ':youtube_url', $youtube_url, PDO::PARAM_STR);
            $stmt->bindParam( ':message', $clean['message'], PDO::PARAM_STR);
            $stmt->bindParam( ':post_date', $current_date, PDO::PARAM_STR);

            // SQLクエリの実行
            $res = $stmt->execute();
            
            if( $res ) {
                $success_message = 'メッセージを書き込みました。';
            } else {
                $error_message[] = '書き込みに失敗しました。';
            }
            
            // プリペアドステートメントを削除
            $stmt = null;
        }

    // データベースの接続を閉じる
    $pdo = null;
}
//ファイルを読み込んでHTMLに返す
if( $file_handle = fopen( FILENAME,'r') ) {
    while( $data = fgets($file_handle) ){
        $split_data = preg_split( '/\'/', $data);

        $message = array(
            'list_name' => $split_data[1],
            'view_name' => $split_data[3],
            'url_name' => $split_data[5],
            'message' => $split_data[7],
            'post_date' => $split_data[9]
        );
        array_unshift( $message_array, $message);
    }

    // ファイルを閉じる
    fclose( $file_handle);
}
?>
<!DOCTYPE HTML>
<html lang="ja">
    <head>
        <meta charset="utf-8">
	<link href="style.css" rel="stylesheet">
    <meta charset="utf-8">   
    <script src="http://code.jquery.com/jquery-1.11.3.min.js"></script>
    <script type="text/javascript">
    $(function(){
        var size = $('li').length;
        alert(size);
        //「+」を押したら増やす
        $('.add').click(function(){
            $('.addInput').append('<li><input id="url_name" type="text" name="url_name[]" value=""></li>');
        });
        //「-」を押したら減らす
        $('.del').click(function(){
            size = $('li').length
            if(size > 1){
                $('.addInput li:last-child').remove();
           }
        });
    });
    </script>
    </head>
    <body>
    <?php if( !empty($error_message) ): ?>
	    <ul class="error_message">
		    <?php foreach( $error_message as $value ): ?>
			    <li>・<?php echo $value; ?></li>
		    <?php endforeach; ?>
	    </ul>
    <?php endif; ?>
        <header>
        </header>
	    <h1>YouTubeプレイリスト掲示板</h1>

            <div class="box1">
              <p>プレイリスト作成＆投稿</p>
            </div>
    <form method="post">
    <div>
        <label for="list_name">プレイリスト名</label>
		<input id="list_name" type="text" name="list_name" value="">
    </div>
	<div>
		<label for="view_name">投稿者名</label>
		<input id="view_name" type="text" name="view_name" value="">
	</div>
    <div>
       
		<label for="url_name">URL</label>
        <ul class="addInput">
		<li><input id="url_name" type="text" name="url_name" value=""></li>
        </ul>
	</div>
	<table>
	<div>
		<label for="minus"></label>
		<a class="del" name="minus">－</a>
		<label for="puls"></label>
		<a class="add" name="puls">＋</a>
	</div>
	</table>
	<div>
		<label for="message">ひと言メッセージ</label>
		<textarea id="message" name="message"></textarea>
	</div>
	<input type="submit" name="btn_submit" value="投稿">
</form>
<hr>
<section>
    <?php if( !empty($message_array) ): ?>
    <?php foreach( $message_array as $value ): ?>
    <article>
        <div class="info">
            <h2><?php echo $value['view_name']; ?></h2>
            <h3><?php echo $value['list_name']; ?></h3>
            <time><?php echo date('Y年m月d日 H:i', strtotime($value['post_date'])); ?></time>
        </div>
        <div class="YouTube">
            <iframe 
            width="560" height="315" 
            src="https://www.youtube.com/embed/<?php echo $value['url_name'];?>"
            title="YouTube video player" 
            frameborder="0" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
            </iframe -->
        </div>
        <p><?php echo $value['message']; ?></p>
	    <hr>
    </article>
    <?php endforeach; ?>
    <?php endif; ?>
</section>
    </body>
</html>

<!--iframe width="560" height="315" src="https://www.youtube.com/embed/ba600DlIRAo" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe -->
