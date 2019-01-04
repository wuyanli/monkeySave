<?php
/**
 * Created by PhpStorm.
 * User: luanjinyang
 * Date: 2019/1/2
 * Time: 下午7:18
 */

$dateDir = '';
$randDirs = scandir($dateDir);
foreach ($randDirs as $randDir) {
    if (substr($randDir, 0, 1) == '.') {
        continue;
    }

}