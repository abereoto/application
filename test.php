<html>
<head>
    <title>PHP TEST</title>
	<meta charset="utf-8">
    <link href="style.css" rel="stylesheet">
    <style>
	html{
		font-size: 100%;
        }
	/* 2021/06/24 PHP部分追加　by鈴木*/
	body{
		background-image: url(music.jpeg);
		background-color:rgba(255,255,255,0.8);
		background-blend-mode:lighten;
		background-size: cover;
		background-position: center center;
		font-family: Meiryo;
		
	}
	/* keijiban.txtの中身を書き出しているタグが p */
	p{ 
		font-size: 20px; color: black;
		text-align: center;
	}
	h1{
		font-size: 64; color: black;
		text-align: center;
	}
	div{
		backgroud: #ffffff;
		padding: 10px;
		text-align: center;
	}
	iframe{
		text-align: center;
	}
    </style>
</head>
<body>
    <header>
        <nav>
            <ul class="main_nav">
            </ul>
        </nav>
    </header>
<h1>掲示板</h1>
    <p>
    <iframe width=560 height=315 src=https://www.youtube.com/embed/ZUwaudw8ht0 title=YouTube video player frameborder=0 allow=accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture allowfullscreen></iframe>
</p>
<div>
<form method="POST" action="<?php print($_SERVER['PHP_SELF']) ?>">
<input type="text" name="personal_name"><br><br>
<textarea name="contents" rows="8" cols="40">
</textarea><br><br>
<input type="submit" name="btn1" value="投稿する">
</form>
</div>
<?php

if($_SERVER["REQUEST_METHOD"] == "POST"){
    writeData();
}

readData();

function readData(){
    $keijban_file = 'keijiban.txt';

    $fp = fopen($keijban_file, 'rb');

    if ($fp){
        if (flock($fp, LOCK_SH)){
            while (!feof($fp)) {
                $buffer = fgets($fp);
                print($buffer);
            }

            flock($fp, LOCK_UN);
        }else{
            print('ファイルロックに失敗しました');
        }
    }

    fclose($fp);
}

function writeData(){
    $personal_name = $_POST['personal_name'];
    $contents = $_POST['contents'];
    $contents = nl2br($contents);

    $data = "<hr>";
    $data = $data."<p>投稿者:".$personal_name."</p><br>";
    $data = $data."<p>内容:</p><br>";
    $data = $data."<p>".$contents."</p><br>";

    $keijban_file = 'keijiban.txt';

    $fp = fopen($keijban_file, 'ab');

    if ($fp){
        if (flock($fp, LOCK_EX)){
            if (fwrite($fp,  $data) === FALSE){
                print('ファイル書き込みに失敗しました');
            }

            flock($fp, LOCK_UN);
        }else{
            print('ファイルロックに失敗しました');
        }
    }

    fclose($fp);
}

?>
</body>
</html>
