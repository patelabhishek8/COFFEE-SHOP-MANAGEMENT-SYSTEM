<?php
session_start();
session_unset();
session_destroy();
header('Location: ../HTML/vp_login.php');
exit();
?>