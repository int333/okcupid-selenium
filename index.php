<?php

//while (@ ob_end_flush()); // end all output buffers if any
//
//$proc = popen("phpunit MainTest.php", 'r');
//echo '<pre>';
//while (!feof($proc))
//{
//    echo fread($proc, 4096);
//    $in = file_get_contents('in.txt');
//    @ flush();
//    if ($in == 1) {
//        pclose($proc);
//        break;
//    }
//}
//echo '</pre>';

require_once __DIR__.'/bin/DRLevSemaphore.php';

echo '<pre>';
var_dump(DRLevSemaphore::read('asd'));
var_dump(DRLevSemaphore::write('asd', 'dsa'));
echo '</pre>';