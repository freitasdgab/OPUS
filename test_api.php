<?php
session_start();
$_SESSION['user_id']=1;
$_GET['action']='get_messages';
$_GET['target_id']=2;
include 'back/api_chat.php';
