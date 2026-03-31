<?php

// cron_polling.php
require_once __DIR__ . '/config.php';
//$_GET['azione'] = 'polling';
$_SERVER['REQUEST_METHOD'] = 'GET';
require_once __DIR__ . '/api/piattaforma.php';