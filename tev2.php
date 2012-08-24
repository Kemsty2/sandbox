<?php
$descriptorspec = array(
   0 => array("pipe", "r"),  // stdin это канал, из которого потомок будет читать
   1 => array("pipe", "w"),  // stdout это канал, в который потомок будет записывать
   2 => array("file", "error-output.txt", "a"), // stderr это файл для записи
);
$process = null;


//$redirect_program = "ipconfig";
$fh = fopen('php://stdin', 'r');
//stream_set_blocking($fh, 0);
$last_line = false;
$message = '';
////stream_set_blocking($fh, 0);

    // $pipes выглядит теперь примерно так:
    // 0 => записываемый дескриптор, соединённый с дочерним stdin
    // 1 => читаемый дескриптор, соединённый с дочерним stdout
    // Любой вывод ошибки будет присоединён к /tmp/error-output.txt

while (!$last_line) {
    
    $next_line = fgets($fh);
    if(preg_match('#^-end$#si', $next_line)) $last_line = true;                          
    else {
	if (!is_resource($process)) {
		try {
			$process = proc_open("time", $descriptorspec, $pipes);
		} catch(Exception $e) {
			die('Fatum Fatal');
		}
	} 
    	//fopen($pipes[0], "a");
    	fwrite($pipes[0], $next_line."\r\n");
    	//fclose($pipes[0]);
    	echo stream_get_contents($pipes[1]);

    
//        while(!feof($pipes[1])) 
//        { 
//        //while(TRUE) { 
//        echo fgets($pipes[1]);
//        }
//        fclose($pipes[1]);
    }
    // Важно, чтобы вы закрыли любые каналы до вызова
    // proc_close, чтобы исключить тупиковую блокировку
}

fclose($pipes[0]);    
fclose($pipes[1]);
$return_value = proc_close($process);

    echo "command returned $return_value\n";















//while (!$last_line) {
//    //$next_line = fgets($fh, 2); // read the special file to get the user input from keyboard
//    //echo $last_line;
//    echo "wraper:> ";
//    $next_line = fgets($fh);
//    switch ($next_line) {
//        case "-exit\r\n":
//            $last_line = true;
//            break;
//        case "-exit\n":
//            $last_line = true;
//            break;
//        case "-exec\r\n":
//            $out = passthru($redirect_program);
//            echo $out;
//            break; 
//        case "-exec\n":
//            $out = passthru($redirect_program);
//            echo $out;
//            break;
//        case "-te\r\n":
//            
//            $rrr = popen("powershell","r");
//            //stream_set_blocking($rrr, 0);
//            $rrr1 = popen("powershell","r");
//            //stream_set_blocking($rrr1, 0);
//            //echo $out;
//            break;         
//        default:
//            echo "#\r\n";
//            break;
//    }
//}
?>