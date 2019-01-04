<?php
/**
 * Created by PhpStorm.
 * User: luanjinyang
 * Date: 2018/12/16
 * Time: 下午3:30
 */

$rootPath = '/tmp/daanjun';

//var_dump(scandir('/tmp/testPic/理学/数学/工程数学:矢量分析与场论(第四版)课后习题答案'));
//die();

$categorys = scandir($rootPath);

foreach ($categorys as $category) {
    if (substr($category, 0, 1) == '.') {
        continue;
    }
    $categoryPath = $rootPath . '/' . $category;    // test/理学
    $subjects = scandir($categoryPath);
    foreach ($subjects as $subject) {
        if (substr($subject, 0, 1) == '.') {
            continue;
        }
        $subjectPath = $categoryPath . '/' . $subject;  // test/理学/数学
        $books = scandir($subjectPath);
        foreach ($books as $book) {
            if (substr($book, 0, 1) == '.') {
                continue;
            }
            if (strpos($book, ' ') !== false ||
                strpos($book, '(') !== false ||
                strpos($book, ')') !== false ||
                strpos($book, ':') !== false
            ){
//                $book = str_replace(' ', '\ ', $book);
//                $book = str_replace('(', '\(', $book);
//                $book = str_replace(')', '\)', $book);
//                $book = str_replace(':', '\:', $book);
            }
            $bookPath = $subjectPath . '/' . $book;  //  test/理学/数学/大学数学
            echo 'bookPath:' . $bookPath . PHP_EOL;
            $files = scandir($bookPath);
            foreach ($files as $file) {
                echo 'file1:' . $file . PHP_EOL;
                if (substr($file, 0, 1) == '.' || $file == 'book.json') {
                    continue;
                }
                echo 'file2:' . $file . PHP_EOL;
                if (strpos($file, ' ') !== false ||
                    strpos($file, '(') !== false ||
                    strpos($file, ')') !== false ||
                    strpos($file, ':') !== false
                ){
                    $file = str_replace(' ', '\ ', $file);
                    $file = str_replace('(', '\(', $file);
                    $file = str_replace(')', '\)', $file);
                    $file = str_replace(':', '\:', $file);
                }
                $filePath = $bookPath .'/' . $file;
                $dirPath = $filePath . 'dir';
                if (!is_dir($dirPath)) {
                    mkdir($dirPath, 0777, true);
                }
                $fileCont = file_get_contents($filePath);
                $fileArray = json_decode($fileCont, true);
                downPic($dirPath, $fileArray);
            }
        }
    }

}

function downPic($dirPath, $fileArray) {
   foreach ($fileArray as $file) {
       $filename = $dirPath . '/' . $file['page'] . '.png';
       $cont = file_get_contents($file['url']);
       file_put_contents($filename, $cont);
   }
}