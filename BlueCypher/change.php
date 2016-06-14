<?php
session_start();
if(!isset($_SESSION["loginok"]) || $_SESSION['loginok']!=1) {session_destroy();die("0");}
require_once("function/sqllink.php");
require_once("function/encryption.php");
$link=sqllink();
if(!$link) {session_destroy();die("0");}
$usr=$_SESSION['user'];
$pw=$_SESSION['pwd'];
$id = $_SESSION['userid'];
if($usr==""||$pw=="" || $id=="")  {session_destroy();die("0");}
$sql="SELECT * FROM `pwdusrrecord` WHERE `username`= ? AND `password`= ? AND `id`= ?";
$res=sqlexec($sql,array($usr,$pw,$id),$link);
$record= $res->fetch(PDO::FETCH_ASSOC);
if($record==FALSE) {session_destroy();die("0");}
$index=$_POST['index']; 
if(!$link->beginTransaction()) die('0');
$sql="SELECT * FROM `password` WHERE `userid`= ? AND `index`= ? ";
$res=sqlexec($sql,array($id,(int)$index),$link);
$record= $res->fetch(PDO::FETCH_ASSOC);
if($record==FALSE) {$link->commit();die("0");}
$ppwd=$_POST['newpwd'];
$pubkey=mt_rand(10000000,99999999);
$newpw=encrypt($ppwd,$pubkey);
$changedCols="`key` = ? ,`pwd` = ?";
$values=array($pubkey,$newpw);
if (isset($_POST["name"])){
    $changedCols .= " ,`name` = ?";
    array_push($values, $_POST["name"]);
}
if (isset($_POST["other"])){
    $changedCols .= " ,`other` = ?";
    array_push($values, $_POST["other"]);
}
array_push($values, $id, (int)$index);
$sql="UPDATE `password` SET ".$changedCols." WHERE `userid` = ? AND `index`= ?";
$res=sqlexec($sql,$values,$link);
if($res==NULL) {$link->rollBack();die("0");}
$link->commit();
echo "1";
?>