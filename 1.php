<?php
//$message = 'id:115CB8C8 sub:001 dlvrd:000 submit date:1205230932 done date:1205230933 stat:UNDELIV err:1';
//if(preg_match('/^id:([^ ]+) sub:(\d{1,3}) dlvrd:(\d{3}) submit date:(\d{10}) done date:(\d{10}) stat:([A-Z]{7}) err:(\d{1,3})(?:\stext:(.*))?/ms', $message, $matches)) print_r($matches);

$txt = 'index.php?controller=ordersAdministration&action=item&id=1510&tpl=clear';
echo preg_replace_callback('`^index.php\?controller=([a-zA-Z]+)&action=([a-zA-Z]+)(&(.*?))?$`si', create_function('$m', 'return "/".$m[1]."/".$m[2]."/".($m[4]?"?".$m[4]:"");'), $txt);
