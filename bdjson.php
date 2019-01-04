<?php
/**
 * Created by PhpStorm.
 * User: luanjinyang
 * Date: 2018/12/24
 * Time: 下午10:07
 */

include_once 'vendor/autoload.php';
//require_once 'vendor/sunra/php-simple-html-dom-parser/Src/Sunra/PhpSimple/simplehtmldom_1_5/simple_html_dom.php';

use Sunra\PhpSimple\HtmlDomParser;
use PHPHtmlParser\Dom;

$rootPath = isset($argv[1]) ? $argv[1] : null;
if ($rootPath == null) {
    echo '解析根目录为空' . PHP_EOL;
    die();
}

$bdJson = new Bdjson($rootPath);
$bdJson->parse();

class Bdjson{
    private $_htmlDom = null;

    private $_stemDom = null;

    private $_pDom = null;

    private $_rootPath = null;

    private $_stem = null;

    private $_answer = null;

    private $_analyse = null;

    private $_retryNum = 3;

    const FRIST_SEPARAOR = '1+-*!';

    const SECOND_SEPARAOR = '2+-*!';

    const THIRD_SEPARAOR = '3+-*!';

    const T_END = 'T+-*!';

    const R_END = 'R+-*!';

    private $_htmlDir = null;

    private $_isChoice = false;

    private $_answerType = 'unknown'; //未知题型unknown，

    private $_choiceAnsweNum = null;

    private $_file = null;

    private $_bdJson = array();

    private $_outDir = '/Users/luanjinyang/test/soutiJson/';

    private $_allowTags = "<p><h1><h2><h3><h4><h5><h6><img><span><br><ol><li><obj>"; //先把div， ul给去掉

    private $_allowPropertys = [
        'screen', 'text-align', 'bold', 'italic', 'underline', 'color', 'bg-color', 'font-size', 'font-family',
        'border', 'padding', 'margin', 'float', 'image', 'bg-image', 'position', 'display',
    ];

    private $_perfectSaprate = '-----------------------perfect----------------------------' . PHP_EOL;

    public function __construct($rootPath){
        if (substr($rootPath, strlen($rootPath)-1 , 1) != '/') {
            $rootPath .= '/';
        }
        $this->_rootPath = $rootPath;
        $this->_htmlDom = new Dom();
        $this->_stemDom = new Dom();
        $this->_pDom = new Dom();
    }

    public function parse() {
        $numDirs = scandir($this->_rootPath);
        foreach ($numDirs as $numDir) {
            if (substr($numDir, 0, 1) == '.') {
                continue;
            }
            $numDirPath = $this->_rootPath . $numDir;
            if (!is_dir($numDirPath)) {
                continue;
            }

            echo 'numdirPath:' . $numDirPath . PHP_EOL;
            $this->parseNum($numDirPath);

            die();
        }
    }

    private function parseNum($numDirPath) {
        $dir0Path = $numDirPath . '/0/';
        $this->parseNoPicDir($dir0Path);

        $dir1Path = $numDirPath . '/1/';
        $this->parseHasPicDir($dir1Path);
    }

    private function parseNoPicDir($dir0Path) {
        $noPicDirs = scandir($dir0Path);
        foreach ($noPicDirs as $key => $noPicDir) {
            if (substr($noPicDir, 0, 1) == '.') {
                continue;
            }
            $noPicPath = $dir0Path . $noPicDir;
            if (!is_dir($noPicPath)) {
                continue;
            }
            $noPicHtml = $noPicPath . '/' . $noPicDir . '.html';
            $this->_htmlDir = dirname($noPicHtml);
            if (!file_exists($noPicHtml)) {
                echo $noPicHtml . '不存在' . PHP_EOL;
                continue;
            }
            echo 'noPicHtml:' . $noPicHtml . PHP_EOL;
            $this->_file = $noPicHtml;

            $this->_htmlDom->loadFromFile($noPicHtml);


            $this->_stem = $this->_htmlDom->find('.qb-content');    //题干
            $this->_answer = $this->_htmlDom->find('#good-answer'); //答案
            $this->_analyse = $this->_htmlDom->find('.content');    //解析
            if (!count($this->_stem) || !count($this->_answer)) {
                echo $this->_file . '的题干或者答案不存在' . PHP_EOL;
                continue;
            }
//            var_dump($this->_answer->innerHtml);
//            $class = new \ReflectionClass('PHPHtmlParser\Dom\HtmlNode');
//            $methods = $class->getMethods();
//            var_dump($methods);

            $stemText = $this->removeTag($this->_stem->innerHtml);
            $answerText = $this->removeTag($this->_answer->innerHtml);
            if (empty($stemText) || empty($answerText)) {
                echo $this->_file . '题干或答案为空' . PHP_EOL;
                continue;
            }

            $this->structBdJson();

            $this->parseOption($this->_stem->innerHtml);

            $stemHtml = $this->parseStem($this->_stem->innerHtml);
var_dump($stemHtml) . PHP_EOL;
            $this->structStem($stemHtml);


            $parseAnswerHtml = $this->parseAnswer();
            if ($parseAnswerHtml == 1) {
                echo '解析answer失败' . PHP_EOL;
                continue;
            }
            $this->structAnswer($parseAnswerHtml);

            $parseAnalyseHtml = $this->parseAnalyse();
            $this->structAnalyse($parseAnalyseHtml);

            $this->_stem = null;
            $this->_answer = null;
            $this->_analyse = null;

            if (!is_dir($this->_outDir)) {
                mkdir($this->_outDir, 0777, true);
            }
            $jsonFile = $this->_outDir . basename($this->_file);
            file_put_contents($jsonFile, json_encode($this->_bdJson));

//die();





            //            $this->parseAnswer();

//            $this->parseInfo();
//            die();
        }

    }

    private function parseHasPicDir($hasPicPath) {

    }

    private function parseAnalyse() {
        if (!is_object($this->_analyse)) {
            echo '解析不存在' . PHP_EOL;
            return false;
        }


        try{
            $analyseText = $this->removeTag($this->_analyse->innerHtml);
            if (empty($analyseText)) {
                echo '解析为空' . PHP_EOL;
                return false;
            }
        }catch (Exception $e){
            return false;
        }

        $parseAnalyseHtml = $this->parseStem($this->_analyse->innerHtml);

        return $parseAnalyseHtml;
    }

    private function structAnalyse($parseAnalyseHtml) {
        if ($parseAnalyseHtml == false) {
            $this->_bdJson['que_info']['analysis'] = array();
        }else{
            if (strpos($parseAnalyseHtml, self::SECOND_SEPARAOR) !== false) {
                list($structSecondRet, $needThirdParseArr) = $this->structSecondStem($parseAnalyseHtml);
                if ($needThirdParseArr === true) {
                    $finalStruct = $structSecondRet;
                }else{
                    $structThirdRet = $this->structThirdStem($needThirdParseArr);
                    if (count($structSecondRet) != count($structThirdRet)) {
                        echo '为什么二和三组的个数会不一样？？？？' . PHP_EOL;
                        return false;
                    }
                    foreach ($structSecondRet as $key => $structSecondItem) {
                        $structSecondRet[$key]['c'] = $structThirdRet[$key];
                    }
                    $finalStruct = $structSecondRet;
                }
            }else{
                $tmpStruct = $this->structThirdStemString($parseAnalyseHtml);
                if (count($tmpStruct) > 1) {
                    $tmpStruct2['t'] = 'p';
                    $tmpStruct2['c'] = $tmpStruct;
                    $finalStruct[] = $tmpStruct2;
                }else{
                    $finalStruct = $tmpStruct;
                }
            }
            $this->_bdJson['que_info']['analysis'] = $finalStruct;
        }
//        echo $this->_perfectSaprate;

//        var_dump(json_encode($this->_bdJson));
//        die();
//        var_dump(json_encode($this->_bdJson));

    }

    private function parseInfo() {
        if (!is_object($this->_analyse)) {
            echo '文件不存在解析模块' . PHP_EOL;
            return 1;
        }
        $analyseText = $this->removeTag($this->_analyse);
        if (empty($analyseText)) {
            echo '文件的解析模块内容为空' . PHP_EOL;
            return 2;
        }
        $this->structAnalyse();
    }



    private function parseAnswer() {
        $answerHtml = $this->_answer->innerHtml;
        $this->_htmlDom->load($answerHtml);
        try {
            $title = $this->_htmlDom->find('.title')[0];
            $trace = $this->_htmlDom->find('.trace')[0];
            $ext = $this->_htmlDom->find('.ext-info')[0];
            if (NULL == $title || NULL == $trace || NULL == $ext) {
                return 1;
            }
            $title->delete();
            $trace->delete();
            $ext->delete();
        }catch (Exception $e) {
            return 1;
        }
        $answerHtml = $this->_htmlDom;
        $headSpanOps = strpos($answerHtml, '<span>');
        if ($headSpanOps === false) {
            echo '有问题，头部最外面居然没有span' . PHP_EOL;
            return 1;
        }
        $answerHtml = substr($answerHtml, $headSpanOps + 6);  //还要加上<span> 的长度

        $tailSpanOps = strrpos($answerHtml, '</span>');
        if ($tailSpanOps === false) {
            echo '有问题，尾部最外面居然没有span' . PHP_EOL;
            return 1;
        }
        $answerHtml = substr($answerHtml, 0, $tailSpanOps);


        $answerParseHtml = $this->parseStem($answerHtml);
        echo 'fsdafds' . $answerParseHtml . PHP_EOL;
//        die();
        return $answerParseHtml;
//        var_dump($answerParseHtml);
//        die();


//        echo $answerHtml . PHP_EOL;
//        die();
//
//        $separaorPattern = '/<(dd|br[\s\/]*)>/'; // <dd> <br />
//        $spacePattern = '/<(\/dd|[\/]*span)>/';  // </dd> <span> </span>
//
//        $answerHtml = preg_replace($spacePattern, '', $answerHtml);
//        $answerHtml = preg_replace($separaorPattern, self::SEPARAOR, $answerHtml);
//        $answerArr = array_filter(explode(self::SEPARAOR, $answerHtml), 'trim');
//
//        $this->structAnswer($answerArr);
    }

    private function parseStem($stemHtml) {
//        $stemHtml = $this->_stem->innerHtml;

        $spanPattern = '/<span(.*?)<\/span>/';
        preg_match_all($spanPattern,$stemHtml, $matches);
//        if (isset($matches) && !empty($matches[1])) {
//            foreach ($matches[1] as $matchItem) {
//                $biPattern = '/<(br|img)/';
//                $pPattern = '/<p/';
//                preg_match($biPattern, $matchItem, $bimatch);
//                preg_match($pPattern, $matchItem, $pmatch);
//                if (isset($bimatch[1]) && !empty($bimatch[1]) && (empty($pmatch[1]))) {
//                    $stemHtml = str_replace('<span', '<p', $stemHtml);
//                    $stemHtml = str_replace('span>', 'p>', $stemHtml);
//                }
//            }
//        }

        $stemHtml = $this->removeTag($stemHtml, $this->_allowTags);
echo 'stem:' . $stemHtml . PHP_EOL;
        //总共有三层关系，第一层  div, （object)没有见过.  第二层  p, h1-h6(目前没有见过).  第三层  img, span, br, (ul, li, ol)没有见过
        if (strpos($stemHtml, '<div') !== false) {
            //把第一层进行替换 div, obj, 替换为 第一层标签符 . 第一层标签 . T标识结束符 . 标签属性 . R标识结束符
            $stemHtml = preg_replace('/<div(.*?)>/', self::FRIST_SEPARAOR . 'div'. self::T_END . '$1' . self::R_END , $stemHtml);
            $stemHtml = preg_replace('/<\/div>/', self::FRIST_SEPARAOR, $stemHtml);
        }

        if (strpos($stemHtml, '<p') !== false || strpos($stemHtml, '<h') !== false) {
            //把第二层进行替换, p, h1-h6, 替换为  第二层标签符 . 第二层标签 . T标识结束符 . 标签属性 . R标识结束符
            $stemHtml = preg_replace('/<(p|h1|h2|h3|h4|h5|h6)(.*?)>/', self::SECOND_SEPARAOR . '$1' .self::T_END . '$2' . self::R_END, $stemHtml);
            $stemHtml = preg_replace('/<\/(p|h1|h2|h3|h4|h5|h6)>/', self::SECOND_SEPARAOR, $stemHtml);
        }
        if (strpos($stemHtml, '<span') !== false) {
            //把第三层进行替换, img, span, br,  替换为  第三层标签符 . 第三层标签 . T标识结束符 . 标签属性 . R标识结束符
            $stemHtml = preg_replace('/<(span)(.*?)>/', self::THIRD_SEPARAOR . '$1' .self::T_END . '$2' . self::R_END, $stemHtml);
            $stemHtml = preg_replace('/<\/(span)>/', self::THIRD_SEPARAOR, $stemHtml);
        }
        if (strpos($stemHtml, '<br') !== false || strpos($stemHtml, '<img') !== false) {
            //把第三层进行替换, img, span, br,  替换为  第三层标签符 . 第三层标签 . T标识结束符 . 标签属性 . R标识结束符
            $stemHtml = preg_replace('/<(br|img)(.*?)>/', self::THIRD_SEPARAOR . '$1' .self::T_END . '$2' . self::R_END . self::THIRD_SEPARAOR, $stemHtml);
        }

        return $stemHtml;
    }


    private function structStem($stemHtml) {
        //如果有第二层就从第二层开始组合，如果没有就从第三层开始组合
        if (strpos($stemHtml, self::SECOND_SEPARAOR) !== false) {
            echo '开始解析第二层' . PHP_EOL;
            list($structSecondRet, $needThirdParseArr) = $this->structSecondStem($stemHtml);
            if ($needThirdParseArr === true) {
                $finalStruct = $structSecondRet;
            }else {
                echo '应该不是一个special的吧' . PHP_EOL;
                $structThirdRet = $this->structThirdStem($needThirdParseArr);
                if (count($structSecondRet) != count($structThirdRet)) {
                    echo '为什么二和三组的个数会不一样？？？？' . PHP_EOL;
                    return false;
                }
                foreach ($structSecondRet as $key => $structSecondItem) {
                    $structSecondRet[$key]['c'] = $structThirdRet[$key];
                }
                $finalStruct = $structSecondRet;
            }
        }else{
            //假如里面有两个span， 或者有br, 这样finalStruct会是一个数组，而且上面没有p标签
            $tmpStruct = $this->structThirdStemString($stemHtml);
            var_dump($tmpStruct);
            if (count($tmpStruct) > 1) {
                $tmpStruct2['t'] = 'p';
                $tmpStruct2['c'] = $tmpStruct;
                $finalStruct[] = $tmpStruct2;
            }else{
                $finalStruct = $tmpStruct;
            }
        }

        $this->_bdJson['que_stem'] = $finalStruct;
        $this->_bdJson['que_info']['summary'] = $finalStruct;
//        var_dump(json_encode($this->_bdJson));
    }

    private function structThirdStemString($thirdStem) {
        $thirdItemInfos = array();
        if (strpos($thirdStem, self::THIRD_SEPARAOR) === false) {
            $thirdItemInfo['t'] = 'span';
            $thirdItemInfo['c'] = $thirdStem;
            $thirdItemInfos[] = $thirdItemInfo;  //如果只是一句话不需要多层嵌套
            return $thirdItemInfos;
        }
        //有三的标签
        $thirdStemArr = array_filter(explode(self::THIRD_SEPARAOR, $thirdStem), 'trim');
        foreach ($thirdStemArr as $thirdStemItem) {
            $tmpThirdInfo = array();
            $thirdTagPos = strpos($thirdStemItem, self::T_END);
            if ($thirdTagPos === false) {
                $tmpThirdInfo['t'] = 'span';
                $tmpThirdInfo['c'] = $thirdStemItem;
                $thirdItemInfos[] = $tmpThirdInfo;
                continue;
            }
            $thirdTag = substr($thirdStemItem, 0, $thirdTagPos);
            $thirdStemItem = substr($thirdStemItem, $thirdTagPos + strlen(self::T_END));

            switch ($thirdTag) {
                case 'br' :
                    $tmpThirdInfo['t'] = 'br';
                    $thirdItemInfos[] = $tmpThirdInfo;
                    $brRPos = strpos($thirdStemItem, self::R_END);
                    if (false !== $brRPos) {
                        $thirdStemItem = trim(substr($thirdStemItem, $brRPos + strlen(self::R_END)));
                        if (!empty($thirdStemItem)) {
                            $tmpThirdInfo['t'] = 'span';
                            $tmpThirdInfo['c'] = $thirdStemItem;
                            $thirdItemInfos[] = $tmpThirdInfo;
                        }
                    }

                    continue;
                case 'span' :
                    $thirdProperty = array();
                    $thirdPropertyPos = strpos($thirdStemItem, self::R_END);
                    if ($thirdPropertyPos !== false) {
                        $thirdPropertyStr = substr($thirdStemItem, 0, $thirdPropertyPos);
                        $thirdStemItem = substr($thirdStemItem, $thirdPropertyPos + strlen(self::R_END));

                        $thirdPropertyArr = array_filter(preg_split('/\s|;|=/', $thirdPropertyStr), 'trim');
                        foreach ($thirdPropertyArr as $thirdPropertyItem) {
                            if (strpos($thirdPropertyItem, ':') === false) {
                                continue;
                            }
                            $thirdPropertyItem = trim($thirdPropertyItem, '""');
                            list($thirdPropertyName, $thirdPropertyValue) = explode(':', $thirdPropertyItem);
                            if (in_array($thirdPropertyName, $this->_allowPropertys)) {
                                $thirdProperty[] = 'ext_' . $thirdPropertyName . '_' . $thirdPropertyValue;
                            }
                        }
                    }
                    $tmpThirdInfo['t'] = 'span';
                    $tmpThirdInfo['c'] = $thirdStemItem;
                    if (!empty($thirdProperty)) {
                        $tmpThirdInfo['r'] = $thirdProperty;
                    }
                    $thirdItemInfos[] = $tmpThirdInfo;
                    continue;
                case 'img' :
                    $thirdProperty = array();
                    $thirdPropertyPos = strpos($thirdStemItem, self::R_END);
                    if ($thirdPropertyPos !== false) {
                        $thirdPropertyStr = substr($thirdStemItem, 0, $thirdPropertyPos);
                        $thirdStemItem = substr($thirdStemItem, $thirdPropertyPos + strlen(self::R_END));

                        //对于这种属性的分隔要特别注意。。。。。。。。
                        $thirdPropertyStr = str_replace('style=','', $thirdPropertyStr);
                        $thirdPropertyStr = str_replace(': ', ':', $thirdPropertyStr);
                        $thirdPropertyStr = preg_replace('/(http|https):/', '$1', $thirdPropertyStr);
                        //只用 ';' 进行分隔
                        $thirdPropertyArr = array_filter(preg_split('/;|\s/', trim($thirdPropertyStr)), 'trim');

                        foreach ($thirdPropertyArr as $thirdPropertyItem) {
                            if (strpos($thirdPropertyItem, ':') === false && strpos($thirdPropertyItem, '=') === false) {
                                continue;
                            }
                            $thirdPropertyItem = trim($thirdPropertyItem, '""/');
                            list($thirdPropertyName, $thirdPropertyValue) = array_filter(preg_split('/\s|:|=/', $thirdPropertyItem), 'trim');
                            $thirdPropertyName = trim($thirdPropertyName, '"/"');
                            $thirdPropertyValue = trim($thirdPropertyValue, '"/"');
                            if ($thirdPropertyName == 'width' || $thirdPropertyName == 'zwidth') {
                                $tmpThirdInfo['w'] = $thirdPropertyValue;
                            }
                            if ($thirdPropertyName == 'height' || $thirdPropertyName == 'zheight') {
                                $tmpThirdInfo['h'] = $thirdPropertyValue;
                            }
                            if ($thirdPropertyName == 'src') {
                                $hPos = strpos($thirdPropertyValue, '//');
                                if ($hPos !== false) {
                                    if ($hPos == 4) {
                                        $newUrl = 'http:' . substr($thirdPropertyValue, $hPos);
                                    }else{
                                        $newUrl = 'https:' . substr($thirdPropertyValue, $hPos);
                                    }
                                    $imgFullPath = dirname($this->_file) . '/' . md5(time(). rand(1,100)) . '.jpg';
                                    $this->downloadPic($newUrl, $imgFullPath);
                                    $imgSize = getimagesize($imgFullPath);
                                    $tmpThirdInfo['w'] = $imgSize[0];
                                    $tmpThirdInfo['h'] = $imgSize[1];
                                    $thirdPropertyValue = $newUrl;
                                }
                                $tmpThirdInfo['src'] = $thirdPropertyValue;
                            }
                            if (in_array($thirdPropertyName, $this->_allowPropertys)) {
                                $thirdProperty[] = 'ext_' . $thirdPropertyName . '_' . $thirdPropertyValue;
                            }
                        }
                    }
                    $tmpThirdInfo['t'] = 'img';
                    if (!empty($thirdProperty)) {
                        $tmpThirdInfo['r'] = $thirdProperty;
                    }
                    $thirdItemInfos[] = $tmpThirdInfo;
                    continue;
                default:

                    echo '出现未知第三层标签?????' . $thirdTag . PHP_EOL;
                    return 5;
            }

        }

        return $thirdItemInfos;
    }

    /**
     * 专门用来解析数组的
     * @param $needThirdParse
     * @return array|int
     */
    private function structThirdStem($needThirdParseArr) {
        //如果需要第三层解析的还是一个string,那么第一，第二层必须为空， bdjson不允许越级, 如果第一，第二为空则不需要挂载

        $thirdInfos = array();
        foreach ($needThirdParseArr as $thirdStemStr) {
           $thirdInfos[] = $this->structThirdStemString($thirdStemStr);
        }

        return $thirdInfos;
    }

    private function structSpecialCase($needSecondParse) {
        //要记录一下各种特殊case的情况
        //case1： 如果只含有p标签，那就把p标签替换为br标签，然后给第三层去解析
//        preg_match('/'.self::SECOND_SEPARAOR.'p/', $needSecondParse, $matchP);
//        preg_match('/'.self::THIRD_SEPARAOR.'(br|img)/', $needSecondParse, $matchBI);
//        var_dump($matchP);
//        var_dump($matchBI);
//        echo 'begin:' . $needSecondParse . PHP_EOL;

        $needSecondParse = str_replace(self::SECOND_SEPARAOR.'p', self::THIRD_SEPARAOR.'br', $needSecondParse);
        $needSecondParse = str_replace(self::SECOND_SEPARAOR, self::THIRD_SEPARAOR, $needSecondParse);

//echo 'needparse:' . $needSecondParse . PHP_EOL; die();
//        echo 'end:' . $needSecondParse . PHP_EOL;
//        die();

        $structThirdInfos = $this->structThirdStemString($needSecondParse);

        return $structThirdInfos;
    }


    // $needSecondParse  String  第二层需要解析的应该是一个字符串
    // 1,不存在第二层节点  2,存在第二层节点
    private function structSecondStem($needSecondParse) {
        if (strpos($needSecondParse, self::SECOND_SEPARAOR) === false) {
            echo '不存在第二层节点' . PHP_EOL;
            return false;
        }
echo 'parse:' . $needSecondParse . PHP_EOL;
        $secondStemArr = array_filter(explode(self::SECOND_SEPARAOR, $needSecondParse), 'trim');
        $secondInfos = array();
        $needThirdParse = array();
        foreach ($secondStemArr as $secondStemItem) {
            if (empty(trim($secondStemItem))) {
                continue;
            }
            echo 'secondStemItem:' . $secondStemItem . PHP_EOL;
            $tmpSecondInfo = array();
            $secondTagPos = strpos($secondStemItem, self::T_END);
            if ($secondTagPos === false) {
                //没有匹配到第二层标签符
                echo '没有匹配到第二层标签符???? bdjson不允许文本节点或者第三层节点与第二层节点并列' . PHP_EOL;
                var_dump($secondStemArr);
                echo $this->_file . PHP_EOL;
                $structThirdInfos = $this->structSpecialCase($needSecondParse);
                if (!empty($structThirdInfos)) {
                    return array($structThirdInfos, true);
                }else{
                    return array(array(), false);
                }
                var_dump($structThirdInfos);
                die();
                return false;
            }

            $secondTag = substr($secondStemItem, 0, $secondTagPos);
            //把stem减去标签的长度
            $secondStemItem = substr($secondStemItem, $secondTagPos + strlen(self::T_END));

            $secondProperty = array();
            $secondPropertyPos = strpos($secondStemItem, self::R_END);

            if ($secondPropertyPos !== false) {
                //截取属性长度值
                $secondPropertyStr = substr($secondStemItem, 0, $secondPropertyPos);
                //把stem减去属性的长度
                $needThirdParseStr = substr($secondStemItem, $secondPropertyPos + strlen(self::R_END));
                $secondPropertyArr = array_filter(preg_split('/\s|;|=/', $secondPropertyStr), 'trim');
                foreach ($secondPropertyArr as $secondPropertyItem) {
                    if (strpos($secondPropertyItem, ':') === false) {
                        continue;
                    }
                    $secondPropertyItem = trim($secondPropertyItem, '""');
                    echo 'property:' . $secondPropertyItem . PHP_EOL;
                    list($secondPropertyName, $secondPropertyValue) = explode(':', $secondPropertyItem);
                    if (in_array($secondPropertyName, $this->_allowPropertys)) {
                        $secondProperty[] = 'ext_' . $secondPropertyName . '_' . $secondPropertyValue;
                    }
                }
            }

            $tmpSecondInfo['t'] = $secondTag;
            if (!empty($secondProperty)) {
                $tmpSecondInfo['r'] = $secondProperty;
            }
            $tmpSecondInfo['c'] = array();


            $secondInfos[] = $tmpSecondInfo;
            $needThirdParse[] = $needThirdParseStr;
        }

        return array($secondInfos, $needThirdParse);
    }

    // 1,不存在根节点 2,根节点为1  3,根节点大于1
    private function structFirstStem($stemHtml) {
        //第一层标签为根节点，最多一个
        $firstNodeNum = substr_count($stemHtml, self::FRIST_SEPARAOR);
        if ($firstNodeNum > 2) {
            $this->_stemFirstNodeStatus = 3;
            echo '根节点大于一个' . PHP_EOL;
            return array(array(), $stemHtml);
        }
        if ($firstNodeNum == 0) {
            echo '不存在根节点' . PHP_EOL;
            $this->_stemFirstNodeStatus = 1;
            return array(array(), $stemHtml);
        }

        $firstStemArr = array_filter(explode(self::FRIST_SEPARAOR, $stemHtml), 'trim');

        $firstStemInfo = array();

        $firstStemItem = $firstStemArr[1];  // |||||something||||| 分隔后为数组1

        $firstTagPos = strpos($firstStemItem, self::T_END);
        if ($firstTagPos === false) {
            echo '没有匹配到第一层标签符？？？？' . PHP_EOL;
            return array(1, array());
        }
        $firstTag = substr($firstStemItem, 0, $firstTagPos);
        $firstStemItem = substr($firstStemItem, $firstTagPos + strlen(self::T_END));

        $firstProperty = array();
        $firstPropertyPos = strpos($firstStemItem, self::R_END);
        $needSecondParse = substr($firstStemItem, $firstPropertyPos + strlen(self::R_END));
        if ($firstPropertyPos !== false) {
            $firstPropertyStr = substr($firstStemItem, 0, $firstPropertyPos);
            $firstPropertyArr = array_filter(preg_split('/\s|;/', $firstPropertyStr), 'trim');
            foreach ($firstPropertyArr as $firstPropertyItem) {
                if (strpos($firstPropertyItem, ':') === false) {
                    continue;
                }
                list($firstPropertyName, $firstPropertyValue) = explode(':', $firstPropertyItem);
                if (in_array($firstPropertyName, $this->_allowPropertys)) {
                    $firstProperty[] = 'ext_' . $firstPropertyName . '_' . $firstPropertyValue;
                }
            }
        }

        $firstStemInfo['t'] = $firstTag;
        $firstStemInfo['blockNum'] = 1;
        $firstStemInfo['style'] = $firstProperty;
        $firstStemInfo['c'] = array();
        $this->_stemFirstNodeStatus = 2;

        return array($firstStemInfo, $needSecondParse);
    }

    private function removeTag($html, $allowTags="") {
        if (empty($html)) {
            return $html;
        }
        return strip_tags($html, $allowTags);
    }

    private function downloadPic($url, $path) {
        $downloadFlag = false;
        if (empty($url) || substr($url, 0, 4) != 'http') {
            return $downloadFlag;
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
        return $downloadFlag;
    }

    private function parseImgTag($html) {
        $imgPattern = '/<img(.*?)src=\"(http:\/\/img\.zuoyebang\.cc\/zyb_[0-9a-z]+\.jpg)\"[\s\/]*>/';
        $matches = array();
        preg_match_all($imgPattern, $html, $matches);
        var_dump($matches);
        if (empty($matches[2])) {
            echo '匹配到的图片url为空' . PHP_EOL;
            return 3;
        }
        foreach ($matches[2] as $imgUrl) {
            $imgArr = explode('/', $imgUrl);
            $imgName = array_pop($imgArr);
            $imgPath = $this->_htmlDir . '/' . $imgName;
            $isDownload = $this->downloadPic($imgUrl, $imgPath);
            if ($isDownload == false) {
                echo '文件' . $imgPath . '下载失败,url:' . $imgUrl . PHP_EOL;
            }
        }
        //因为有的图片没有宽高属性，所以后面要打开图片获取
        $imgReplace = self::FRIST_SEPARAOR . '$2' . self::FRIST_SEPARAOR;
        $html = preg_replace($imgPattern, $imgReplace, $html);

        return $html;
    }

    private function parseOptionString($optionString) {

    }

    private function structOption($optionArr) {
        $optionInfos = array();
        foreach ($optionArr as $optionItem) {
            $optionHtml = $this->parseStem($optionItem);
            if (strpos($optionHtml, self::SECOND_SEPARAOR) !== false) {
                list($structSecondRet, $needThirdParseArr) = $this->structSecondStem($optionHtml);
                if ($needThirdParseArr === true) {
                    $finalStruct = $structSecondRet;
                }else {
                    $structThirdRet = $this->structThirdStem($needThirdParseArr);
                    if (count($structSecondRet) != count($structThirdRet)) {
                        echo '为什么二和三组的个数会不一样？？？？' . PHP_EOL;
                        return false;
                    }
                    foreach ($structSecondRet as $key => $structSecondItem) {
                        $structSecondRet[$key]['c'] = $structThirdRet[$key];
                    }
                    $finalStruct = $structSecondRet;
                }
            }
            if (strpos($optionHtml, self::THIRD_SEPARAOR) !== false) {
                $tmpStruct = $this->structThirdStemString($optionHtml);
                if (count($tmpStruct) > 1) {
                    $tmpStruct2['t'] = 'p';
                    $tmpStruct2['c'] = $tmpStruct;
                    $finalStruct[] = $tmpStruct2;
                }else{
                    $finalStruct[] = $tmpStruct;
                }
            }
            $optionInfos[] = $finalStruct;
        }
        $this->_bdJson['que_options'] = $optionInfos;
    }

    //解析选项
    private function parseOption($stemHtml) {

        $optionArr = array();
        $countA = max(substr_count($stemHtml, 'A.'), substr_count($stemHtml, 'A．'));
        $countB = max(substr_count($stemHtml, 'B.'), substr_count($stemHtml, 'B．'));
        $countC = max(substr_count($stemHtml, 'C.'), substr_count($stemHtml, 'C．'));

        if ($countA>0 && $countB>0 && $countC>0 && $countA == $countB && $countA == $countC && $countA == 1) {
            echo '是选择题' . PHP_EOL;
            //是选择题
            if (strpos($stemHtml, '<img') !== false) {
                $this->_bdJson['que_info']['meta']['img_que_options'] = true;
            }
            $this->_isChoice = true;
//            list($stemHtml, $option) = preg_split('/A(．|.)/', $stemHtml);
//            $option = str_replace(self::FRIST_SEPARAOR, '', $option);
//            $optionArr = preg_split('/(B(．|.)|C(．|.)|D(．|.))/', $option);
//            var_dump($optionArr);die();

//            $this->structOption($optionArr);


        }else {
            unset($this->_bdJson['que_options']);
        }
        unset($this->_bdJson['que_options']);
//        return [$stemHtml, $optionArr];
    }



    private function structBdJson() {
        $this->_bdJson['t'] = 'sdiv';
        $this->_bdJson['datatype'] = 'question';
        $this->_bdJson['c'] = '';
        $this->_bdJson['que_stem'] = array();
        $this->_bdJson['que_options'] = array();
        $this->_bdJson['que_answer'] = array();
        $this->_bdJson['que_info'] = array();
        $this->_bdJson['qid'] = '';
        $this->_bdJson['title'] = '';
        $this->_bdJson['errorno'] = 0;

        $this->_bdJson['que_info']['meta']['img_que_stem'] = false;
        $this->_bdJson['que_info']['meta']['img_que_options'] = false;

        if (strpos($this->_stem->innerHtml, '<img') !== false) {
            $this->_bdJson['que_info']['meta']['img_que_stem'] = true;
        }
    }

    private function structAnswer($parseAnswerHtml) {
        echo 'answerHtml:' . $parseAnswerHtml . PHP_EOL;
        if (strpos($parseAnswerHtml, self::SECOND_SEPARAOR) !== false) {
            list($structSecondRet, $needThirdParseArr) = $this->structSecondStem($parseAnswerHtml);
            if ($needThirdParseArr === true) {
                $finalStruct = $structSecondRet;
            }else {
                $structThirdRet = $this->structThirdStem($needThirdParseArr);
                if (count($structSecondRet) != count($structThirdRet)) {
                    echo '为什么二和三组的个数会不一样？？？？' . PHP_EOL;
                    return false;
                }
                foreach ($structSecondRet as $key => $structSecondItem) {
                    $structSecondRet[$key]['c'] = $structThirdRet[$key];
                }
                $finalStruct = $structSecondRet;
            }
        } else {
            $tmpStruct = $this->structThirdStemString($parseAnswerHtml);
            if (count($tmpStruct) > 1) {
                $tmpStruct2['t'] = 'p';
                $tmpStruct2['c'] = $tmpStruct;
                $finalStruct[] = $tmpStruct2;
            }else{
                $finalStruct = $tmpStruct;
            }
        }


        $this->_bdJson['que_answer'] = $finalStruct;
//        echo $this->_perfectSaprate;
//        var_dump(json_encode($this->_bdJson));

//        var_dump(json_encode($finalStruct));
//        die();
    }

}



