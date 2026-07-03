<?php
// logout.php
require_once 'includes/auth.php';
destroySession();
header('Location: pages/login.php?logout=1');
exit;
