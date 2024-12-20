<?php
session_start();
if(isset($_SESSION["UserID"])){
	unset($_SESSION["UserID"]);
	unset($_SESSION["email"]);
	header("location:Login.php");
}
?>