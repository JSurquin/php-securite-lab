<?php
require_once 'config.php';

$p = $_GET['p'] ?? 'home';

include($p . '.php');
