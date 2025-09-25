<?php

session_start();
$_SESSION = ['user_name'];
$_SESSION = ['user_id'];
session_unset();
session_destroy();

header('Location:login.php');
?>