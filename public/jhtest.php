<?php
$sn = 'WWOW2020D96DE72E30577F1A29F8F6';
$secret = 'SHyuYpn9xrwXOFEd';
$date = date('Ymd', time());
$skey = md5($sn . $secret . $date);

echo $skey.'<br />';
echo 'b0160df7b401f9cf8f3fe5fa3078ef14';