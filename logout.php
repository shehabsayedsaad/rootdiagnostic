<?php
require __DIR__.'/../lib.php'; start_session();
if($_SERVER['REQUEST_METHOD']==='POST' && csrf_ok()){ $_SESSION=[]; session_destroy(); }
header('Location: login.php');
