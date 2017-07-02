<?php

session_start();

if (isset($_GET['a']) && $_GET['a'] == "gif3d") {
    if (!file_exists("tmp")) {
        mkdir("tmp");
    }
    if (!file_exists("gif3ds")) {
        mkdir("gif3ds");
    }
    if (isset($_POST['url']) || isset($_GET['url'])) {
        if (isset($_GET['url'])) {
            $_POST['url'] = $_GET['url'];
        }
        $tmp_name = "/tmp/" . str_replace(" ", "", sha1(microtime() . date("U")));
        $name = preg_replace("/^.*\/(.*\.gif).*$/", "\${1}", $_POST['url']);
        file_put_contents($tmp_name, file_get_contents($_POST['url']));
        $_FILES['file']['name'] = $name;
        $_FILES['file']['tmp_name'] = $tmp_name;
        $_FILES['file']['size'] = filesize($tmp_name);
    }
    foreach ($_FILES as $type=>$f) {
        $fileName = $f['name'];
        $tmpFile = $f['tmp_name'];
        if (intval($f['size']) > 0 && intval($f['size']) < 15000000) {
            $orgFile = sha1(date("U") . microtime() . $fileName) . ".gif";
            $comFile = "c{$orgFile}";
            copy($tmpFile, "tmp/{$orgFile}"); 
            $pwd = getcwd();
            chdir("tmp");

            $size = getimagesize($orgFile);
            $w = $size[0];
            $h = $size[1];
            $mw = $w - 1;
            $mmw = $w - 3;
            $c = round($w / 212);
            $mh = $h - 1;
            $mmh = $h - 3;

            exec("convert -quiet \"{$orgFile}\" +repage \"${orgFile}.tmp\"");
            exec("convert \"{$orgFile}.tmp\" -virtual-pixel background -background transparent -mattecolor black -distort Perspective \"0,0 -{$c},-{$c} {$mw},0 {$mmw},{$c} {$mw},{$mh} {$mmw},{$mmh} 0,{$mh} -{$c},{$h}\" \"r.{$orgFile}\"");
            exec("convert \"{$orgFile}.tmp\" -virtual-pixel background -background transparent -mattecolor black -distort Perspective \"0,0 {$c},{$c} {$mw},0 {$w},-{$c} {$mw},{$mh} {$w},{$h} 0,{$mh} {$c},{$mmh}\" \"l.{$orgFile}\"");

            exec("convert -delay 10 \"{$orgFile}\" \"r.{$orgFile}\" \"{$orgFile}\" \"l.{$orgFile}\" -loop 0 \"{$comFile}\"");
            unlink($orgFile);
            unlink("r.{$orgFile}");
            unlink("l.{$orgFile}");
            unlink("{$orgFile}.tmp");

            rename($comFile, "../gif3ds/{$comFile}");
            chdir($pwd);
        }
    }
    header("Location:{$_SERVER['PHP_SELF']}?gif=" . urlencode($comFile) . "#bottom");
    exit();
}

$gif3dIMG = "";
if (isset($_GET['gif']) && file_exists("gif3ds/{$_GET['gif']}")) {
    $gif3dIMG = "<img src=\"gif3ds/{$_GET['gif']}\" alt=\"gif3d\" />"; 
}

$html .= <<<eof
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>GIF3D</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <link type="text/css" rel="stylesheet" href="cdn/js/bootstrap/3.0.0/css/bootstrap.min.css" />
        <link type="text/css" rel="stylesheet" href="cdn/css/siamnet/default.css" />
    </head>
    <body>
        <div class="container">
            <header class="container header">
                <h1 class="heading">GIF3D</h1>
            </header>
            <article class="container">
                <div class="panel panel-primary">
                    <div class="panel-heading">Create a 3D GIF</div>
                    <div class="panel-body">
                        <form role="form" enctype="multipart/form-data" method="post" action="index.php?a=gif3d">
                            <div class="form-group">
                                <label for="gif">Upload a GIF</label>
                                <input class="form-control" type="file" name="gif" id="gif" />
                                <br />
                                <label for="url">URL</label>
                                <input class="form-control" type="text" id="url" name="url" placeholder="Full URL to GIF" />
                            </div>
                            <button type="submit" class="btn btn-primary">create</button>
                        </form>
                    </div>
                </div>
                <div class="container">
                    <p><a href="https://github.com/wsams/gif3d">Powered by gif3d</a></p>
                </div>
                <br />
                <div id="gif3dd">{$gif3dIMG}</div>
                <br /><br /><br />
                <a name="bottom"></a>
            </article>
        </div>
        <script src="cdn/js/jquery/1.10.2/jquery-1.10.2.min.js"></script>
        <script src="cdn/js/bootstrap/3.0.0/js/bootstrap.min.js"></script>
        <script src="cdn/js/siamnet/default.js"></script>
        <script type="text/javascript">
            $(document).ready(function() {
                $("#url").focus();
            });
        </script>
    </body>
</html>
eof;

print($html);
