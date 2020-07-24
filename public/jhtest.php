<?php
$mask = 'jhwebmask';
echo md5($mask . md5('btdl2020'));

$filename = 'aa.txt';
print_r(pathinfo($filename)['filename']);