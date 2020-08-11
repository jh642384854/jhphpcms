<?php
//$url = $this->getConfig ()->url ();
$content = "console.log('123456')";
$expire = 604800;
header ( 'Content-type: application/x-javascript' );
header ( 'Cache-Control: max-age=' . $expire );
header ( 'Accept-Ranges: bytes' );
header ( 'Content-Length: ' . strlen ( $content ) );
echo $content;

