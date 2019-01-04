<?php
/**
 * Created by PhpStorm.
 * User: luanjinyang
 * Date: 2018/12/28
 * Time: 下午9:08
 */

include_once 'vendor/autoload.php';

date_default_timezone_set('Asia/shanghai');

use PHPHtmlParser\Dom;

$rootPath = isset($argv[1]) ? $argv[1] : NULL;
if (NULL === $rootPath) {
    echo '解析根目录为空' . PHP_EOL;
    die();
}

$b = new Bd($rootPath);
$b->main();

class Bd{

    private $_htmlDom = NULL;

    private $_stemDom = NULL;

    private $_answerDom = NULL;

    private $_bdJson = array();

    private $_rootPath = NULL;

    private $_file = NULL;

    private $_stem = NULL;

    private $_answer = NULL;

    private $_analyse = NULL;

    private $_retryNum = 3;

    private $_subDateDir = NULL;

    private $_outDir = '/Users/luanjinyang/test/bdjson1/';

    private $_logPath = '/Users/luanjinyang/test/soutiJson/souti.log';

    private $_downPicLog = '/Users/luanjinyang/test/soutiJson/downPic.log';

    private $_allowTags = '<p><img><span><br>'; ////先把div, ul, h1-h6,ol, li, ul 给去掉

    private $_allowPropertys = array(
        'screen', 'text-align', 'bold', 'italic', 'underline', 'color', 'bg-color', 'font-family', //'font-size',
        'border', 'padding', 'margin', 'float', 'image', 'bg-image', 'position', 'display', 'vertical-align',
    );

    const FIRST_SEPARAOR = '1+-*!';

    const SECOND_SEPARAOR = '2+-*!';

    const T_END = 'T+-*!';

    const R_END = 'R+-*!';

    const LOG_INFO = 0;

    const LOG_WARN = 1;

    const LOG_FATAL = 2;

    private $_grade = [
        '小学' => '277',
        '初中' => '278',
        '高中' => '279',
    ];

    private $_course = [
        '277' => [
            '语文' => '1138',
            '数学' => '1137',
            '英语' => '1139',
        ],
        '278' => [
            '语文' => '1140',
            '数学' => '1141',
            '英语' => '1142',
            '物理' => '1146',
            '化学' => '1148',
            '生物' => '1147',
            '政治' => '1143',
            '历史' => '1144',
            '地理' => '1145',
        ],
        '279' => [
            '语文' => '1149',
            '数学' => '1150',
            '英语' => '1151',
            '物理' => '1155',
            '化学' => '1157',
            '生物' => '1156',
            '政治' => '1152',
            '历史' => '1153',
            '地理' => '1154',
        ],
    ];

    public function __construct($rootPath){
        $this->_htmlDom = new Dom();
        $this->_stemDom = new Dom();
        $this->_answerDom = new Dom();
        $this->_rootPath = $rootPath;
    }

    public function main() {
        try{
            $this->parsePath();
        }catch (Exception $e){
            $this->log('file:' . $this->_file . 'msg:' . $e->getMessage(). 'trace:' . $e->getTraceAsString(), self::LOG_FATAL);
        }
    }

    private function parsePath() {
        if (!is_dir($this->_rootPath)) {
            echo $this->_rootPath . '文件夹不存在' . PHP_EOL;
            return false;
        }

        if (substr($this->_rootPath, strlen($this->_rootPath)-1, 1) != '/') {
            $this->_rootPath .= '/';
        }

        $dateDirs = scandir($this->_rootPath);
        foreach ($dateDirs as $dateDir) {
            if (substr($dateDir, 0, 1) == '.') {
                continue;
            }
            $dir0 = $this->_rootPath . $dateDir . '/0/';
            $dir1 = $this->_rootPath . $dateDir . '/1/';
            if (is_dir($dir0)) {
                $this->_subDateDir = $dateDir . '/0/';
                $this->scanFile($dir0);
            }
            if (is_dir($dir1)) {
                $this->_subDateDir = $dateDir . '/1/';
                $this->scanFile($dir1);
            }
        }
    }

    private function scanFile($dirPath) {
        $randDirs = scandir($dirPath);
        foreach ($randDirs as $randDir) {
            if (substr($randDir, 0, 1) == '.') {
                continue;
            }
            $filePath = $dirPath . $randDir . '/' . $randDir . '.html';
            $this->initVar();
            try{
                $this->parseFile($filePath);
            }catch (Exception $e) {
                $this->log('file:' . $this->_file . 'msg:' . $e->getMessage(). 'trace:' . $e->getTraceAsString(), self::LOG_FATAL);
            }

        }
    }

    private function parseFile($filePath) {
        if (!file_exists($filePath)) {
            $this->log($filePath . '不存在', self::LOG_FATAL);
            return false;
        }
        $this->_file = $filePath;
        $this->_htmlDom->loadFromFile($this->_file);
        $this->_stem = $this->_htmlDom->find('.qb-content');    //题干
        $this->_answer = $this->_htmlDom->find('#good-answer'); //答案
        $this->_analyse = $this->_htmlDom->find('.content');    //解析

        $parsed = $this->hasParsed();
        if ($parsed) {
            return true;
        }

        if (!count($this->_stem) || !count($this->_answer)) {
            $this->log($this->_file . '的题干或者答案不存在', self::LOG_FATAL);
            return false;
        }

        $stemText = $this->rmTag($this->_stem->innerHtml);
        $answerText = $this->rmTag($this->_answer->innerHtml);
        if (empty($stemText) || empty($answerText)) {
            $this->log($this->_file . '题干或答案为空', self::LOG_FATAL);
            return false;
        }

//        $this->writeStemHtml();

        $this->log('开始处理文件：' . $this->_file);

        $this->structBdJson();

        $parseStemHtml = $this->preParseStem($this->_stem->innerHtml);
        $parseStemHtml = $this->parseHtml($parseStemHtml);
        $stemStruct = $this->structHtml($parseStemHtml);
        $this->_bdJson['que_stem'] = $stemStruct;
        $this->_bdJson['que_info']['summary'] = $stemStruct;


        $preAnswerHtml = $this->preParseAnswer($this->_answer->innerHtml);
        $parseAnswerHtml = $this->parseHtml($preAnswerHtml);
        $this->_bdJson['que_answer'] = $this->structHtml($parseAnswerHtml);

        if (count($this->_analyse)) {
            $parseAnalyseHtml = $this->parseHtml($this->_analyse->innerHtml);
            $this->_bdJson['que_info']['analysis'] = $this->structHtml($parseAnalyseHtml);
        }
//        var_dump(json_encode($this->_bdJson));
        $this->extraInfo();
        $this->saveJson();
    }

    private function writeStemHtml() {
        $stemHtml = $this->preParseStem($this->_stem->innerHtml);

        $file = $this->_outDir . basename($this->_file);
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }
        file_put_contents($file, $stemHtml);
    }

    private function preParseStem($html) {
        $this->_stemDom->load($html);
        $ext = $this->_stemDom->find('.ext-info')[0];
        if (NULL == $ext) {
            return $html;
        }
        $ext->delete();

        $stemHtml = $this->_stemDom;

        return $stemHtml;
    }

    private function extraInfo() {
        $course = $this->_htmlDom->find('.course');    //科目
        $grade = $this->_htmlDom->find('.grade');      //年纪
        $mapGrade = '';
        $mapCourse = '';
        if (count($grade)) {
           $mapGrade = $this->mapGrade(trim($grade->text));
        }
        if (count($course)) {
            $mapCourse = $this->mapCourse($mapGrade, trim($course->text));
        }
        $type = $this->parseQueType();
        $this->_bdJson['que_info']['type'] = $type;
        $this->_bdJson['que_info']['score'] = '';
        $this->_bdJson['que_info']['year'] = '';
        $this->_bdJson['que_info']['course'] = $mapCourse;
        $this->_bdJson['que_info']['grade'] = $mapGrade;
        $this->_bdJson['que_info']['region'] = '';
        $this->_bdJson['que_info']['test_type'] = '';
        $this->_bdJson['que_info']['source'] = '';
        $this->_bdJson['que_info']['edition'] = '';
        $this->_bdJson['que_info']['chapter'] = '';
        $this->_bdJson['que_info']['time'] = '';
        $this->_bdJson['que_info']['difficulty'] = '';
        $this->_bdJson['que_info']['nature'] = '';
        $this->_bdJson['que_info']['frequency'] = '';
        $this->_bdJson['que_info']['type_show'] = '';
        $this->_bdJson['que_info']['skill'] = '';
    }

    private function mapGrade($grade) {
        $mapGrade = '';
        switch ($grade) {
            case '小学':
            case '一年级':
            case '二年级':
            case '三年级':
            case '四年级':
            case '五年级':
            case '六年级':
                $mapGrade = $this->_grade['小学'];
                break;
            case '初中':
            case '初一':
            case '初二':
            case '初三':
                $mapGrade = $this->_grade['初中'];
                break;
            case '高中':
            case '高一':
            case '高二':
            case '高三':
                $mapGrade = $this->_grade['高中'];
                break;
            default:
                break;

        }
        return $mapGrade;
    }

    private function mapCourse($mapGrade, $course) {
        $mapCourse = '';
        if (in_array($mapGrade, $this->_grade)) {
            if (isset($this->_course[$mapGrade][$course])) {
                $mapCourse = $this->_course[$mapGrade][$course];
            }
        }

        return $mapCourse;
    }

    private function parseQueType() {
        $stemHtml = $this->_stem->innerHtml;

        $multiPattern1 = '/(\(1\)|（1）)/';
        $multiPattern2 = '/(\(2\)|（2）)/';
        preg_match($multiPattern1, $stemHtml, $matchMulti1);
        preg_match($multiPattern2, $stemHtml, $matchMulti2);
        if (!empty($matchMulti1[1]) && !empty($matchMulti2[1])) {
            return 'multi';
        }

        //选择题
        $posA = strpos($stemHtml, 'A.');
        $posB = strpos($stemHtml, 'B.');
        $posC = strpos($stemHtml, 'C.');

        if ($posA !== false && $posB !== false && $posC !== false) {
            $countA = substr_count($stemHtml, 'A.');
            $countB = substr_count($stemHtml, 'B.');
            $countC = substr_count($stemHtml, 'C.');
            if ($countA == $countB && $countA == $countC && $countA == 1) {
                return 'sc';
            }
        }

        //由于太多选择中不是 A. 所以，判断带有A,B,C且仅出现一次为选择题
        $posA = strpos($stemHtml, 'A');
        $posB = strpos($stemHtml, 'B');
        $posC = strpos($stemHtml, 'C');

        if ($posA !== false && $posB !== false && $posC !== false) {
            $countA = substr_count($stemHtml, 'A');
            $countB = substr_count($stemHtml, 'B');
            $countC = substr_count($stemHtml, 'C');
            if ($countA == $countB && $countA == $countC && $countA == 1) {
                return 'sc';
            }
        }

        $blankPattern = '/(___|&nbsp;&nbsp;&nbsp;)/';
        preg_match($blankPattern, $stemHtml, $matchBlank);
        if (!empty($matchBlank[1])) {
            return 'bk';
        }


        return 'qa';
    }

    private function saveJson() {

        $numberDir = $this->_outDir . $this->_subDateDir;

        if(!is_dir($numberDir)) {
            mkdir($numberDir, 0777, true);
        }
        list($dirname,) =explode('.', basename($this->_file));
        $jsonDirPath = $numberDir . $dirname . '/';
        if (!is_dir($jsonDirPath)) {
            mkdir($jsonDirPath);
        }
        $filename = $dirname . '.json';
        $outJsonPath = $jsonDirPath .  $filename;

        file_put_contents($outJsonPath, json_encode($this->_bdJson));
        if ($this->_bdJson['que_info']['meta']['img_que_stem']) {
            //复制content.png到这个目录
            $contentPath = dirname($this->_file) . '/content.png';
            exec('cp ' . $contentPath . ' ' . $jsonDirPath);
        }
    }

    private function hasParsed(){
        $jsonDir = $this->_outDir . $this->_subDateDir;

        list($filename,) =explode('.', basename($this->_file));
        $filename .= '.json';
        $outJsonPath = $jsonDir .  $filename;

        return file_exists($outJsonPath);
    }

    private function initVar() {
        //初始化重置变量为空
        $this->_file = NULL;
        $this->_stem = NULL;
        $this->_answer = NULL;
        $this->_analyse = NULL;
        $this->_bdJson = NULL;
    }

    private function structBdJson() {
        $this->_bdJson['t'] = 'sdiv';
        $this->_bdJson['datatype'] = 'question';
        $this->_bdJson['c'] = '';
        $this->_bdJson['que_stem'] = array();
//        $this->_bdJson['que_options'] = array();
        $this->_bdJson['que_answer'] = array();
        $this->_bdJson['que_info'] = array();
        $this->_bdJson['qid'] = '';
        $this->_bdJson['title'] = '';
        $this->_bdJson['errno'] = 0;

        $this->_bdJson['que_info']['analysis'] = array();
        $this->_bdJson['que_info']['meta']['img_que_stem'] = false;
        $this->_bdJson['que_info']['meta']['img_que_options'] = false;

        if (strpos($this->_stem->innerHtml, '<img') !== false) {
            $this->_bdJson['que_info']['meta']['img_que_stem'] = true;
        }
    }

    private function preParseAnswer($html) {
        $this->_answerDom->load($html);

        $title = $this->_answerDom->find('.title')[0];
        $trace = $this->_answerDom->find('.trace')[0];
        $ext = $this->_answerDom->find('.ext-info')[0];
        if (NULL == $title || NULL == $trace || NULL == $ext) {
            return false;
        }
        $title->delete();
        $trace->delete();
        $ext->delete();

        $answerHtml = $this->_answerDom;
        $headSpanOps = strpos($answerHtml, '<span>');
        if ($headSpanOps === false) {
            $this->log($this->_file . '头部最外面居然没有span', self::LOG_FATAL);
            return false;
        }
        $answerHtml = substr($answerHtml, $headSpanOps + 6);  //还要加上<span> 的长度

        $tailSpanOps = strrpos($answerHtml, '</span>');
        if ($tailSpanOps === false) {
            $this->log($this->_file . '尾部最外面居然没有span', self::LOG_FATAL);
            return false;
        }
        $answerHtml = substr($answerHtml, 0, $tailSpanOps);

        return $answerHtml;
    }

    private function parseHtml($html) {

        $html = $this->parseSpecialCase($html);

        $html = $this->rmTag($html, $this->_allowTags);

        //总共有二层关系，第一层  p.  第三层  img, span, br没有见过
        if (strpos($html, '<p') !== false){    // || strpos($html, '<h') !== false) {
            //把第一层进行替换, p, 替换为  第一层标签符 . 第一层标签 . T标识结束符 . 标签属性 . R标识结束符
            $html = preg_replace('/<(p)(.*?)>/', self::FIRST_SEPARAOR . '$1' .self::T_END . '$2' . self::R_END, $html);
            $html = preg_replace('/<\/p>/', self::FIRST_SEPARAOR, $html);
        }
        if (strpos($html, '<span') !== false) {
            //把第二层进行替换, span, 替换为  第二层标签符 . 第二层标签 . T标识结束符 . 标签属性 . R标识结束符
            $html = preg_replace('/<(span)(.*?)>/', self::SECOND_SEPARAOR . '$1' .self::T_END . '$2' . self::R_END, $html);
            $html = preg_replace('/<\/(span)>/', self::SECOND_SEPARAOR, $html);
        }
        if (strpos($html, '<br') !== false || strpos($html, '<img') !== false) {
            //把第二层进行替换, img, span, br,  替换为  第二层标签符 . 第二层标签 . T标识结束符 . 标签属性 . R标识结束符
            $html = preg_replace('/<(br|img)(.*?)>/', self::SECOND_SEPARAOR . '$1' .self::T_END . '$2' . self::R_END . self::SECOND_SEPARAOR, $html);
        }

        return $html;
    }

    private function parseSpecialCase($html) {
        //替换特殊标签


        return $html;
    }

    private function structHtml($html) {
        $finalRet = array();
        if (strpos($html, self::FIRST_SEPARAOR) !== false) {
            list($firstRet, $secondTagArr) = $this->structFirstTag($html);
            $secondRet = $this->structSecondTagArr($secondTagArr);

            if (count($firstRet) != count($secondRet)) {
                $this->log($this->_file . '第一层解析的个数和第二层解析的个数不一样', self::LOG_FATAL);
                return false;
            }

            foreach ($firstRet as $key => $value) {
                //第二层解析中有可能范湖的是null数组
                if (!empty($secondRet[$key])) {
                    $firstRet[$key]['c'] = $secondRet[$key];
                } else {
                    unset($firstRet[$key]);
                }
            }
            $finalRet = array_values($firstRet);
        } else {
            $secondRet = $this->structSecondTagStr($html);
            $finalRet = $secondRet;
        }

        return $finalRet;
    }

    private function structFirstTag($html) {
        $firstInfos = array();
        $secondTagArr = array();
        $firstItems = array_filter(explode(self::FIRST_SEPARAOR, $html), 'trim');
        foreach ($firstItems as $firstItem) {
            if (empty(trim($firstItem, '/'))) {
                continue;
            }
            $tmpFirstInfo = array();
            //首先看是否有p标签
            $tEndPos = strpos($firstItem, self::T_END);
            if ($tEndPos !== false) {
                $tTag = substr($firstItem, 0, $tEndPos);
                $tmpFirstInfo['t'] = $tTag;
                $firstItem = substr($firstItem, $tEndPos + strlen(self::T_END));
                $rEndPos = strpos($firstItem, self::R_END);
                if ($rEndPos !== false) {
                    $rTag = substr($firstItem, 0, $rEndPos);
                    $parseRTagRet= $this->parseRTag($rTag);
                    !empty($parseRTagRet) && $tmpFirstInfo['r'] = $parseRTagRet;
                    $firstItem = substr($firstItem, $rEndPos + strlen(self::R_END));
                }
            } else {
                //如果没有标签开头，那么默认给个p
                $tmpFirstInfo['t'] = 'p';
            }
            if (!empty(trim($firstItem))) {
                $firstInfos[] = $tmpFirstInfo;
                $secondTagArr[] = $firstItem;
            }
        }
        return array($firstInfos, $secondTagArr);
    }

    private function structSecondTagStr($html) {
        $secondInfos = array();
        $secondItems = array_filter(explode(self::SECOND_SEPARAOR, $html), 'trim');
        foreach ($secondItems as $secondItem) {
            if (empty(trim($secondItem, '/'))) {
                continue;
            }
            $tmpSecondInfo = array();
            $tEndPos = strpos($secondItem,self::T_END);
            if ($tEndPos !== false) {
                $tTag = substr($secondItem, 0, $tEndPos);
                $tmpSecondInfo['t'] = $tTag;
                if ($tTag == 'br') {
                    $tmpSecondInfo['t'] = 'br';
                    $secondInfos[] = $tmpSecondInfo;
                    continue;
                }
                $secondItem = substr($secondItem, $tEndPos + strlen(self::T_END));
                $rEndPos = strpos($secondItem, self::R_END);
                if ($rEndPos !== false) {
                    $rTag = substr($secondItem, 0, $rEndPos);
                    $parseRTagRet= $this->parseRTag($rTag);
                    !empty($parseRTagRet) && $tmpSecondInfo['r'] = $parseRTagRet;
                    if ($tTag == 'img') {
                        $imgRet = $this->parseImg($rTag);
                        $tmpSecondInfo = array_merge($tmpSecondInfo, $imgRet);
                    }
                    $secondItem = substr($secondItem, $rEndPos + strlen(self::R_END));
                }
            } else {
                //如果没有标签开头，那么默认给个span
                $tmpSecondInfo['t'] = 'span';
            }

            //span类型的内容为空直接过滤掉
            if ($tmpSecondInfo['t'] == 'span') {
                if (empty(trim($secondItem))) {
                    continue;
                } else {
                    $tmpSecondInfo['c'] = $secondItem;
                }
            }

            $secondInfos[] = $tmpSecondInfo;

        }
        return $secondInfos;
    }

    private function structSecondTagArr($htmlArr) {
        $secondRet = array();
        foreach ($htmlArr as $htmlItem) {
            $secondRet[] = $this->structSecondTagStr($htmlItem);  //这边的这个[] 还需要吗   如果只是一个这个[]会不会套两层？？？？ 应该是需要的，因为后面有解析secondArr
        }
        return $secondRet;
    }

    private function preParseTag($tagStr) {
        //对于这种属性的分隔要特别注意。。。。。。。。
        $tagStr = str_replace('style=','', $tagStr);
        $tagStr = str_replace(': ', ':', $tagStr);
        $tagStr = preg_replace('/(http|https):/', '$1', $tagStr);
        //只用 ';' 进行分隔
        $tagArr = array_filter(preg_split('/;|\s/', trim($tagStr)), 'trim');

        return $tagArr;
    }

    private function parseRTag($rTagStr) {

        $tagArr = $this->preParseTag($rTagStr);

        $tagInfos = array();
        foreach ($tagArr as $tagItem) {
            if (strpos($tagItem, ':') === false && strpos($tagItem, '=') === false) {
                continue;
            }
            $tagItem = trim($tagItem, '""/');
            if (empty($tagItem)) {
                continue;
            }
            list($tagName, $tagValue) = array_filter(preg_split('/:|=/', $tagItem), 'trim');
            $tagName = trim($tagName, '"/"');
            $tagValue = trim($tagValue, '"/"');
            $tagName = strtolower($tagName);
            if (in_array($tagName, $this->_allowPropertys)) {
                switch ($tagName) {
                    case 'vertical-align':
                        $tagName = 'valign';
                        break;
                    default:
                        break;
                }
                $tagInfos[] = 'ext_' . $tagName . '_' . $tagValue;
            }
        }
        return $tagInfos;
    }

    private function parseImg($imgTag) {

        $tagArr = $this->preParseTag($imgTag);
        $imgInfo = array();

        foreach ($tagArr as $tagItem) {
            if (strpos($tagItem, ':') === false && strpos($tagItem, '=') === false) {
                continue;
            }
            $tagItem = trim($tagItem, '""/');
            if (empty($tagItem)) {
                continue;
            }
            list($tagName, $tagValue) = array_filter(preg_split('/:|=/', $tagItem), 'trim');
            $tagName = trim($tagName, '""');
            $tagValue = trim($tagValue, '""');
            if ($tagName == 'width' || $tagName == 'zwidth') {
                $imgInfo['w'] = $tagValue;
            }
            if ($tagName == 'height' || $tagName == 'zheight') {
                $imgInfo['h'] = $tagValue;
            }
            if ($tagName == 'src') {
                $hPos = strpos($tagValue, '//');
                if ($hPos !== false) {
                    if ($hPos == 4) {
                        $newUrl = 'http:' . substr($tagValue, $hPos);
                    }else{
                        $newUrl = 'https:' . substr($tagValue, $hPos);
                    }
                    $imgFullPath = dirname($this->_file) . '/' . basename($newUrl);
                    $this->downloadPic($newUrl, $imgFullPath);
                    $imgSize = getimagesize($imgFullPath);
                    $imgInfo['w'] = $imgSize[0];
                    $imgInfo['h'] = $imgSize[1];
                    $tagValue = './' . basename($newUrl);   //这里的src采用的是本地文件的地址
                }
                $imgInfo['src'] = $tagValue;
            }
        }
        return $imgInfo;
    }

    private function downloadPic($url, $path) {
        $downloadFlag = false;
        if (empty($url) || substr($url, 0, 4) != 'http') {
            return $downloadFlag;
        }

        if (file_exists($path)) {
            return true;
        }

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        for($i=0; $i<$this->_retryNum; $i++) {
            $imgCong = file_get_contents($url);
            if (!empty($imgCong)) {
                $downloadFlag = true;
                file_put_contents($path, $imgCong);
                break;
            }
        }

        if (!$downloadFlag) {
            $this->log($this->_file . '下载图片失败，图片url' . $url, self::LOG_FATAL, $this->_downPicLog);
        }

        return $downloadFlag;
    }

    private function rmTag($html, $allowTags='') {
        if (empty($html)) {
            return $html;
        }
        return strip_tags($html, $allowTags);
    }

    private function log($msg, $type = self::LOG_INFO, $logPath=NULL) {
        if ($logPath === NULL) {
            $logPath = $this->_logPath;
        }
        $infoType = 'info';
        if (substr($msg, strlen($msg)-1, 1) != PHP_EOL) {
            $msg .= PHP_EOL;
        }
        switch ($type) {
            case self::LOG_INFO:
                $infoType = 'info';
                break;
            case self::LOG_WARN:
                $infoType = 'warn';
                break;
            case self::LOG_FATAL:
                $infoType = 'fatal';
                break;
            default:
                $infoType = 'unknown';
                break;

        }
        $message = '[' . date('Y-m-d H:i:s', time()) . '] ' . $infoType . ' '.  $msg . PHP_EOL;
        file_put_contents($logPath, $message, FILE_APPEND);
    }
}