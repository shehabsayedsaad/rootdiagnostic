<?php
require __DIR__.'/../lib.php'; start_session();
if($_SERVER['REQUEST_METHOD']==='POST' && csrf_ok()){ unset($_SESSION['cust']); }
header('Location: ../index.html');
