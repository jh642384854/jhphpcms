<?php
//$url = $this->getConfig ()->url ();

$data = [
    'atuh' => [
        'session' =>3
    ],
    'test' => 'val'
];

print_r(array_key_exists('test',$data));

print_r(array_key_exists('auth.session',$data));


echo md5(123456);