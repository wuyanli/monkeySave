<?php
/**
 * Created by PhpStorm.
 * User: luanjinyang
 * Date: 2018/12/19
 * Time: 上午11:57
 */

/**
 * Class CheckAnswerFile
 * 给生产同学检测的主要检查脚本是否为utf-8
 */

include_once('vendor/autoload.php');

define('SEPARATOR', '/');

$outFile = 'C:\Users\Administrator\Desktop\checkInfoResult.xlsx';

$dataPath = isset($argv[1]) ? $argv[1] : null;
if ($dataPath == null) {
    echo <<<EOL
        usage:
            php    c:\shell\checkInfo.php   d:\test

EOL;

    die();
}

if (!is_dir($dataPath)) {
    echo $dataPath . '不是文件夹' . PHP_EOL;
}

if (substr($dataPath, strlen($dataPath)-1, 1) != '/') {
    $dataPath .= '/';
}

$floders = scandir($dataPath);
$errorArray = array();
foreach ($floders as $floder) {
    if (substr($floder, 0, 1) == '.') {
        continue;
    }
    if (!is_dir($dataPath)) {
        continue;
    }

    if (!file_exists($dataPath . $floder. '/1/info.txt')) {
        $error['path'] = $dataPath . $floder;
        $error['reason'] = $dataPath . $floder . '/1/info.txt 文件不存在';
        $errorArray[] = $error;
        continue;
    }
    $fh = fopen($dataPath . $floder. '/1/info.txt', 'r');
    while(!feof($fh)) {
        $line = trim(fgets($fh));//fgets()函数从文件指针中读取一行
        if(empty($line)){
            continue;
        }
        if(!preg_match('[:|：]',$line)){
            $error['path'] = $dataPath . $floder . '/1/info.txt';
            $error['reason'] = $line . "不是:|：分隔的数据";
            $errorArray[] = $error;
            continue;
        }

        $tmp = preg_split('[:|：]',$line);
        //进行映射处理
        if(!empty($tmp[0]) && isset($tmp[1])){
            switch ($tmp[0]){
                case '标题':
                case '出版社':
                case '页数':
                case '年份':
                case '出版时间':
                case '印刷时间':
                case 'ISBN':
                case '科目':
                case '年级':
                case '上下册':
                case '版本':
                case '版序':
                case '类别':
                case '其他':
                case '上传者':
                    break;
                default:
                    $error['path'] = $dataPath . $floder . '/1/info.txt';
                    $error['reason'] = $line . "不是:|：分隔的数据";
                    $errorArray[] = $error;
            }
        }

    }
    fclose($fh);
}
$header = array(
    '文件路径' => 'string',//text
    '错误原因' => 'string',
);

$writer = new XLSXWriter();
$writer->writeSheetHeader('Sheet1', $header);

foreach ($errorArray as $row) {

    $row['reason'] = iconv('GBK', 'UTF-8', $row['reason']);
    var_dump($row);
    $writer->writeSheetRow('Sheet1', $row);
}

$writer->writeToFile($outFile);

class CheckAnswerFile {
    const AD_DIR_SEP_JOIN = '/data/ad/';
    const AD_INFO_DIR_NAME = '1';
    const AD_PIC_DIR_NAME = '2';
    const OUTPUT_WATER_PIC_DIR = '3';
    const DIR_SEP = '/';
    const PIC_TYPE = 'png';
    const OUT_PIC_TYPE = 'jpg';
    const OUT_ZIP_TYPE = 'zip';
    const FILENAME_SEP = '.';
    const COVER_NAME = 'fm.png';
    const COVER_SCRAPING_NAME = 'fm.jpg';
    const INFO_NAME ='info.txt';
    const ERROR_FILENAME = 'ad_error.txt';
    const UPDATE_LIMIT =  1000;
    const FILE_UPLOAD_FAILED =  1000;

    private $_scriptAdDirectory;//数据目录
    private $_answerDataPath; //数据list
    private $_answerDataPathFilter;

    private $_conf;

    /**
     * 检查失败的答案数据
     * @var array
     */
    private $arrCheckFailedAnswer = array();

    /**
     * Service_Data_Answer_CheckAnswerFile constructor.
     */
    public function __construct() {
        $this->_conf = Bd_Conf::getAppConf('/answer_grade');
        if(empty($this->_conf['default'])){
            throw new Wenku_Error(Wenku_ErrorCodes::DATA_FILE_NOT_EXISTS);
        }
        $this->_conf = $this->_conf['default'];
    }

    /**
     * @param $dataRootPath string
     * @return array
     */
    public function check($dataRootPath){
        $this->_scriptAdDirectory = $dataRootPath.self::AD_DIR_SEP_JOIN;
        //读取当前目录下所有的上传答案名
        $this->_answerDataPath = Naapi_AnswerUtil::getSubPathNameList($this->_scriptAdDirectory);
        //判断是否有不符合规则的adid文件名,如果有就过滤掉，进行合法文件名下文件的内容检测
        $this->_answerDataPathFilter = $this->checkDataAdFileName($this->_answerDataPath);
        //进行基础信息目录下info.txt 和 fm.png文件名检查
        $this->_answerDataPathFilter = $this->checkInfoTxtAndFmPngFileName($this->_answerDataPathFilter);
        //进行图片目录下内容文件名和文件格式检测
        $this->_answerDataPathFilter = $this->checkPicContentFileName($this->_answerDataPathFilter);

        return $this->arrCheckFailedAnswer;
    }

    /**
     * 检查课本目录是否符合规范要求
     * @param $arrPath array
     * @return array
     */
    private function checkDataAdFileName(array $arrPath){
        if(empty($arrPath)){
            return array();
        }
        $arrAdidList = array();
        foreach ($arrPath as $key => &$onePath){
            $splitPoint = strpos($onePath,'_');
            if(false === $splitPoint){
                //输出错误文件木日志
                $this->arrCheckFailedAnswer[] = Naapi_AnswerUtil::buildAnswerErrorInfoArray('-',
                    Naapi_AnswerUtil::ANSWER_FILE_STATUS_ERROR,$onePath,'*',
                    Naapi_AnswerUtil::ANSWER_FILE_FILE_STATUS_ERROR,"课本目录名称没有下划线");
                unset($arrPath[$key]);
                continue;
            }
            $curAdId = substr($onePath,0,$splitPoint);
            if(!is_numeric($curAdId)){
                //输出错误文件木日志
                $this->arrCheckFailedAnswer[] = Naapi_AnswerUtil::buildAnswerErrorInfoArray($curAdId,
                    Naapi_AnswerUtil::ANSWER_FILE_STATUS_ERROR,$onePath,'*',
                    Naapi_AnswerUtil::ANSWER_FILE_FILE_STATUS_ERROR,"课本目录名称下划线前字符串{$curAdId}非纯数字");
                unset($arrPath[$key]);
                continue;
            }
            if(in_array($curAdId,$arrAdidList)) {
                $this->arrCheckFailedAnswer[] = Naapi_AnswerUtil::buildAnswerErrorInfoArray($curAdId,
                    Naapi_AnswerUtil::ANSWER_FILE_STATUS_ERROR,$onePath,'*',
                    Naapi_AnswerUtil::ANSWER_FILE_FILE_STATUS_ERROR,"课本目录名称下划线前字符串{$curAdId}重复");
                unset($arrPath[$key]);
                continue;
            }
            $arrAdidList[] = $curAdId;
            $onePath = $this->_scriptAdDirectory . $onePath;
        }
        return $arrPath;
    }

    /**
     * 检查目前1合法性
     * @param $arrPath array
     * @return null
     */
    private function checkInfoTxtAndFmPngFileName($arrPath){
        //检查封面文件名和基础信息文件名是否存在
        //如果不在记录path,并且去除
        foreach ($arrPath as $key => &$onePath){
            $dirOnePath = $onePath.self::DIR_SEP.self::AD_INFO_DIR_NAME.self::DIR_SEP;
            $curInfoPath = $dirOnePath .self::INFO_NAME;
            if(!file_exists($curInfoPath)) {
                $this->arrCheckFailedAnswer[] = Naapi_AnswerUtil::buildAnswerErrorInfoArray(Naapi_AnswerUtil::getAdId($onePath),
                    Naapi_AnswerUtil::ANSWER_FILE_STATUS_ERROR,$dirOnePath,self::INFO_NAME,
                    Naapi_AnswerUtil::ANSWER_FILE_FILE_STATUS_ERROR,self::INFO_NAME . "不存在");
                unset($arrPath[$key]);
                continue;
            }

            if(!$this->checkInfoTxtContent($curInfoPath,$onePath,$dirOnePath)){
                unset($arrPath[$key]);
            }

            $curFmPath = $dirOnePath.self::COVER_NAME;
            if(!file_exists($curFmPath) ) {
                $curFmPath = $dirOnePath.self::COVER_SCRAPING_NAME;
                if(file_exists($curFmPath)) {
                    //15代表改封面大小超过限制
                    if(abs(filesize($curFmPath)) > 102400){
                        $this->arrCheckFailedAnswer[] = Naapi_AnswerUtil::buildAnswerErrorInfoArray(Naapi_AnswerUtil::getAdId($onePath),
                            Naapi_AnswerUtil::ANSWER_FILE_STATUS_ERROR,$dirOnePath, self::COVER_SCRAPING_NAME,
                            Naapi_AnswerUtil::ANSWER_FILE_FILE_STATUS_ERROR,"封面jpg大小超过限制");
                        unset($arrPath[$key]);
                    }
                } else {
                    $this->arrCheckFailedAnswer[] = Naapi_AnswerUtil::buildAnswerErrorInfoArray(Naapi_AnswerUtil::getAdId($onePath),
                        Naapi_AnswerUtil::ANSWER_FILE_STATUS_ERROR,$dirOnePath,self::COVER_NAME ." | " . self::COVER_SCRAPING_NAME,
                        Naapi_AnswerUtil::ANSWER_FILE_FILE_STATUS_ERROR,"封面不存在");
                    unset($arrPath[$key]);
                }
            }
        }
        return $arrPath;
    }

    /**
     * @param $infoTxtPath string
     * @param $onePath string
     * @param $dirOnePath string
     * @return bool
     */
    private function checkInfoTxtContent($infoTxtPath,$onePath,$dirOnePath){
        $handle = fopen($infoTxtPath, "r");
        $flag = true;
        $adid = Naapi_AnswerUtil::getAdId($onePath . self::DIR_SEP);
        while(! feof($handle)) {
            $line = trim(fgets($handle));//fgets()函数从文件指针中读取一行
            if(empty($line)){
                continue;
            }
            if(!preg_match('[:|：]',$line)){
                $this->arrCheckFailedAnswer[] = Naapi_AnswerUtil::buildAnswerErrorInfoArray($adid,
                    Naapi_AnswerUtil::ANSWER_FILE_STATUS_ERROR,$dirOnePath,self::INFO_NAME,
                    Naapi_AnswerUtil::ANSWER_FILE_FILE_STATUS_ERROR,"{$line} 不是:|：分隔的数据");
                $flag = false;
                continue;
            }
            $tmp = preg_split('[:|：]',$line);
            //进行映射处理
            if(!empty($tmp[0]) && isset($tmp[1])){
                switch ($tmp[0]){
                    case '标题':
                    case '出版社':
                        if(!is_string($tmp[1])){
                            $this->arrCheckFailedAnswer[] = Naapi_AnswerUtil::buildAnswerErrorInfoArray($adid,
                                Naapi_AnswerUtil::ANSWER_FILE_STATUS_ERROR,$dirOnePath,self::INFO_NAME,
                                Naapi_AnswerUtil::ANSWER_FILE_FILE_STATUS_ERROR,"--{$line} --{$tmp[1]}-- 标题|出版社非字符串");
                            $flag = false;
                        }
                        break;
                    case '页数':
                    case '年份':
                    case '出版时间':
                    case '印刷时间':
                    case 'ISBN':
                        if(!is_numeric($tmp[1])){
                            $this->arrCheckFailedAnswer[] = Naapi_AnswerUtil::buildAnswerErrorInfoArray($adid,
                                Naapi_AnswerUtil::ANSWER_FILE_STATUS_ERROR,$dirOnePath,self::INFO_NAME,
                                Naapi_AnswerUtil::ANSWER_FILE_FILE_STATUS_ERROR,"--{$line} --{$tmp[1]}-- 页数|年份|初版时间|印刷时间|ISBN非数字");
                            $flag = false;
                        }

                        break;
                    case '科目':
                    case '年级':
                    case '上下册':
                    case '版本':
                    case '版序':
                    case '类别':
                    case '其他':
                    case '上传者':
                        if(strpos($tmp[1],'_') === false){
                            $this->arrCheckFailedAnswer[] = Naapi_AnswerUtil::buildAnswerErrorInfoArray($adid,
                                Naapi_AnswerUtil::ANSWER_FILE_STATUS_ERROR,$dirOnePath,self::INFO_NAME,
                                Naapi_AnswerUtil::ANSWER_FILE_FILE_STATUS_ERROR,"--{$line} --{$tmp[1]}-- 科目|年级|上下册|版本|版序|类别|其他|上传者数据中无下划线");
                            $flag = false;
                        }
                        //进行科目->其他的强校验
                        if($tmp[0] == '科目'){
                            list($tname,$tid) = explode('_',$tmp[1]);
                            //映射匹配
                            $curConf = $this->_conf['course'];
                            if(strcmp($curConf[$tid]['name'],$tname)){
                                $this->arrCheckFailedAnswer[] = Naapi_AnswerUtil::buildAnswerErrorInfoArray($adid,
                                    Naapi_AnswerUtil::ANSWER_FILE_STATUS_ERROR,$dirOnePath,self::INFO_NAME,
                                    Naapi_AnswerUtil::ANSWER_FILE_FILE_STATUS_ERROR,"{$line} 科目中{$tid}--{$curConf[$tid]['name']} 对应的{$tname}不支持");
                                $flag = false;
                            }
                        }

                        if($tmp[0] == '年级'){
                            list($tname,$tid) = explode('_',$tmp[1]);
                            //映射匹配
                            $curConf = $this->_conf['grade'];
                            if(strcmp($curConf[$tid]['name'] , $tname)){
                                $this->arrCheckFailedAnswer[] = Naapi_AnswerUtil::buildAnswerErrorInfoArray($adid,
                                    Naapi_AnswerUtil::ANSWER_FILE_STATUS_ERROR,$dirOnePath,self::INFO_NAME,
                                    Naapi_AnswerUtil::ANSWER_FILE_FILE_STATUS_ERROR,"{$line} 年级中{$tid}--{$curConf[$tid]['name']} 对应的{$tname}不支持");
                                $flag = false;
                            }
                        }

                        if($tmp[0] == '上下册'){
                            list($tname,$tid) = explode('_',$tmp[1]);
                            //映射匹配
                            $curConf = $this->_conf['volume'];
                            if(strcmp($curConf[$tid]['name'] , $tname)){
                                $this->arrCheckFailedAnswer[] = Naapi_AnswerUtil::buildAnswerErrorInfoArray($adid,
                                    Naapi_AnswerUtil::ANSWER_FILE_STATUS_ERROR,$dirOnePath,self::INFO_NAME,
                                    Naapi_AnswerUtil::ANSWER_FILE_FILE_STATUS_ERROR,"{$line} 上下册中{$tid}--{$curConf[$tid]['name']} 对应的{$tname}不支持");
                                $flag = false;
                            }
                        }
                        if($tmp[0] == '版本'){
                            list($tname,$tid) = explode('_',$tmp[1]);
                            //映射匹配
                            $curConf = $this->_conf['version'];
                            if(strcmp($curConf[$tid]['name'] , $tname)){
                                $this->arrCheckFailedAnswer[] = Naapi_AnswerUtil::buildAnswerErrorInfoArray($adid,
                                    Naapi_AnswerUtil::ANSWER_FILE_STATUS_ERROR,$dirOnePath,self::INFO_NAME,
                                    Naapi_AnswerUtil::ANSWER_FILE_FILE_STATUS_ERROR,"{$line} 版本中{$tid}--{$curConf[$tid]['name']} 对应的{$tname}不支持");
                                $flag = false;
                            }
                        }

                        if($tmp[0] == '版序'){
                            list($tname,$tid) = explode('_',$tmp[1]);
                            //映射匹配
                            $curConf = $this->_conf['version_pr'];
                            if(strcmp($curConf[$tid]['name'] , $tname)){
                                $this->arrCheckFailedAnswer[] = Naapi_AnswerUtil::buildAnswerErrorInfoArray($adid,
                                    Naapi_AnswerUtil::ANSWER_FILE_STATUS_ERROR,$dirOnePath,self::INFO_NAME,
                                    Naapi_AnswerUtil::ANSWER_FILE_FILE_STATUS_ERROR,"{$line} 版序中{$tid}--{$curConf[$tid]['name']} 对应的{$tname}不支持");
                                $flag = false;
                            }
                        }


                        if($tmp[0] == '其他'){
                            list($tname,$tid) = explode('_',$tmp[1]);
                            //映射匹配
                            $curConf = $this->_conf['else'];
                            if(strcmp($curConf[$tid]['name'] , $tname)){
                                $this->arrCheckFailedAnswer[] = Naapi_AnswerUtil::buildAnswerErrorInfoArray($adid,
                                    Naapi_AnswerUtil::ANSWER_FILE_STATUS_ERROR,$dirOnePath,self::INFO_NAME,
                                    Naapi_AnswerUtil::ANSWER_FILE_FILE_STATUS_ERROR,"{$line} 其他中{$tid}--{$curConf[$tid]['name']} 对应的{$tname}不支持");
                                $flag = false;
                            }
                        }

                        if($tmp[0] == '类别'){
                            list($tname,$tid) = explode('_',$tmp[1]);
                            //映射匹配
                            $curConf = $this->_conf['category'];
                            if(strcmp($curConf[$tid]['name'] , $tname)){
                                $this->arrCheckFailedAnswer[] = Naapi_AnswerUtil::buildAnswerErrorInfoArray($adid,
                                    Naapi_AnswerUtil::ANSWER_FILE_STATUS_ERROR,$dirOnePath,self::INFO_NAME,
                                    Naapi_AnswerUtil::ANSWER_FILE_FILE_STATUS_ERROR,"{$line} 类别中{$tid}--{$curConf[$tid]['name']} 对应的{$tname}不支持");
                                $flag = false;
                            }
                        }

                        break;
                    default:
                        $this->arrCheckFailedAnswer[] = Naapi_AnswerUtil::buildAnswerErrorInfoArray($adid,
                            Naapi_AnswerUtil::ANSWER_FILE_STATUS_ERROR,$dirOnePath,self::INFO_NAME,
                            Naapi_AnswerUtil::ANSWER_FILE_FILE_STATUS_ERROR,"{$line} 中{$tmp[0]}不支持");
                        $flag = false;
                }
            }
        }
        fclose($handle);
        return $flag;
    }

    /**
     * 检查目录2文件合法性
     * @param $arrPath array
     * @return array
     */
    private function checkPicContentFileName($arrPath){
        //检查文件内容图片命名是否合法
        if(empty($arrPath)){
            return array();
        }
        //检测adid目录
        foreach ($arrPath as $key => &$onePath){
            $adid = Naapi_AnswerUtil::getAdId($onePath);
            $curPicContentPath = $onePath.self::DIR_SEP.self::AD_PIC_DIR_NAME.self::DIR_SEP;
            if(!is_dir($curPicContentPath)){
                //判断文件内容目录是否存在
                $this->arrCheckFailedAnswer[] = Naapi_AnswerUtil::buildAnswerErrorInfoArray($adid,
                    Naapi_AnswerUtil::ANSWER_FILE_STATUS_ERROR,$curPicContentPath,'*',
                    Naapi_AnswerUtil::ANSWER_FILE_FILE_STATUS_ERROR,"没有答案资料目录2");
                unset($arrPath[$key]);
                continue;
            }
            $curPicListName = Naapi_AnswerUtil::getSubPathNameList($curPicContentPath);
            if(empty($curPicListName)){
                //判断文件内容目录是否存在
                $this->arrCheckFailedAnswer[] = Naapi_AnswerUtil::buildAnswerErrorInfoArray($adid,
                    Naapi_AnswerUtil::ANSWER_FILE_STATUS_ERROR,$curPicContentPath,'*',
                    Naapi_AnswerUtil::ANSWER_FILE_FILE_STATUS_ERROR,"答案资料目录2为空");
                unset($arrPath[$key]);
                continue;
            }

            $bHasPicErr = false;
            foreach ($curPicListName as $picNameValue){
                if(!preg_match("/^[0-9.]+png$/",$picNameValue)){
                    //6代表改adid下2目录存在不符合规则的图片
                    $bHasPicErr = true;
                    $this->arrCheckFailedAnswer[] = Naapi_AnswerUtil::buildAnswerErrorInfoArray($adid,
                        Naapi_AnswerUtil::ANSWER_FILE_STATUS_ERROR,$curPicContentPath,$picNameValue,
                        Naapi_AnswerUtil::ANSWER_FILE_FILE_STATUS_ERROR,"答案资料目录2下文件名称不符合规则");
                }
            }
            if($bHasPicErr) {
                unset($arrPath[$key]);
            }
        }
        return $arrPath;
    }
}