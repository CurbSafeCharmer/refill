<?php
// Configuations for the stable instance
require_once __DIR__ . "/common.php";

// Commit ID in default edit summary
$config['summaryextra'] = " (" . substr( file_get_contents( ".git/refs/heads/labs-stable" ), 0, 7 ) . ")";
