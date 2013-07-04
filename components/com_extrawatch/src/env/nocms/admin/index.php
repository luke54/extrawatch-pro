<?php

session_start();

define("DS","/");
define("_JEXEC",1);
define("ENV",1);
define("JPATH_BASE2",dirname(__FILE__).DS."extrawatch");

$action = @$_REQUEST['action'];
$usernameFromSession = @$_SESSION['username'];
$passwordFromSession = @$_SESSION['password'];


function verify($login, $password) {
    if ($login == "admin" && $password == "test") {
        return true;
    }
    return false;
}

/* controller */
if (@$action == "logout") {
    session_destroy();
}
else if ($usernameFromSession && $passwordFromSession) {
    $authenticated = verify($usernameFromSession, $passwordFromSession);
} else if (@$action == "login") {
    $authenticated = verify(@$_POST['username'], @$_POST['password']);
    if (!$authenticated) {
        echo("wrong username / password");
    } else {
        $_SESSION['username'] = @$_POST['username'];
        $_SESSION['password'] = @$_POST['password'];
    }
}

if (@$authenticated) {
    echo("<div style='text-align: right; width:100%; font-size: 12px; '><a href='?action=logout' style='color: red;'>Logout</a></div>");
    require_once ("extrawatch".DS."components".DS."com_extrawatch".DS."config.php");
    require_once ("extrawatch".DS."components".DS."com_extrawatch".DS."src".DS."inc.extrawatch.env.php");
    require_once ("extrawatch".DS."administrator".DS."components".DS."com_extrawatch".DS."admin.extrawatch.php");

    $path = realpath(".").DS."extrawatch";
	echo extrawatch_initialize_db($path);
    echo extrawatch_mainController();
} else {
    ?>
<?php
    include("login.html");
}
?>