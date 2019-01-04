<?php
/**
 * 抓取小程序大学答案军的脚本，（使用代理）
 * Created by PhpStorm.
 * User: luanjinyang
 * Date: 2018/12/15
 * Time: 下午1:37
 */

include 'vendor/autoload.php';
use GuzzleHttp\Client;

define('RECORD_FILE', '/tmp/daanjun.record');
define('LOG_FILE', '/tmp/daanjun.log');
define('DAANJUN_PATH', '/tmp/daanjun');

$getSubListUrl = 'https://chaojidaan.com/api/v1/categories?type=1&app_name=daanjun&ts='; //获取所有学科列表
$getSubBookListUrl = 'https://chaojidaan.com/api/v1/books?page=1&perPage=5000&app_name=daanjun&'; //获取某一学科书籍列表
$getBookInfoUrl = ''; //获取某本书的具体信息
$getBookDocsUrl = 'https://chaojidaan.com/api/v1/books/'; //获取某本书的docs列表
$getBookPicUrl = '/api/v1/docs/5a53700af4189512cd99e4c8?app_name=daanjun&ts='; //获取某本书图片url的列表

$subList = '[
    {
        "id": "5a3e8db7c32d22501b353f19",
        "name": "经济学",
        "icon": "",
        "children": [
            {
                "id": "5a3e8db7c32d22501b353f42",
                "name": "金融",
                "icon": "http://cjdaoss.oss-cn-beijing.aliyuncs.com/FHCfN_xgnYfHSMO-"
            },
            {
                "id": "5a3e8db7c32d22501b353f43",
                "name": "证券",
                "icon": "http://cjdaoss.oss-cn-beijing.aliyuncs.com/BYc7C_PSxWzEQdyl"
            },
            {
                "id": "5a3e8db7c32d22501b353f44",
                "name": "保险",
                "icon": "http://cjdaoss.oss-cn-beijing.aliyuncs.com/TQ6N7MGTHLYT6Faa"
            },
            {
                "id": "5a3e8db7c32d22501b353f45",
                "name": "外贸",
                "icon": "http://cjdaoss.oss-cn-beijing.aliyuncs.com/eaORNT3n9ZivGCqS"
            }
        ]
    },
    {
        "id": "5a3e8db7c32d22501b353f1a",
        "name": "管理学",
        "icon": "",
        "children": [
            {
                "id": "5a3e8db7c32d22501b353f58",
                "name": "公共管理",
                "icon": "http://cjdaoss.oss-cn-beijing.aliyuncs.com/e3SZpSr3JkVO1e6z"
            },
            {
                "id": "5a3e8db7c32d22501b353f59",
                "name": "管理",
                "icon": "http://cjdaoss.oss-cn-beijing.aliyuncs.com/cqqdaNKmLgfLc75a"
            },
            {
                "id": "5a3e8db7c32d22501b353f5a",
                "name": "财会",
                "icon": "http://cjdaoss.oss-cn-beijing.aliyuncs.com/wxEe9yJDx4I0T1fq"
            },
            {
                "id": "5a3e8db7c32d22501b353f5b",
                "name": "物流",
                "icon": "http://cjdaoss.oss-cn-beijing.aliyuncs.com/Kf0m-HQvJQt-sgn1"
            }
        ]
    },
    {
        "id": "5a3e8db7c32d22501b353f1b",
        "name": "法学|政治",
        "icon": "",
        "children": [
            {
                "id": "5a3e8db7c32d22501b353f55",
                "name": "法学",
                "icon": "http://cjdaoss.oss-cn-beijing.aliyuncs.com/XGA4c4iwIZmreNHp"
            },
            {
                "id": "5a3e8db7c32d22501b353f56",
                "name": "政治",
                "icon": "http://cjdaoss.oss-cn-beijing.aliyuncs.com/H36JyXgvhFDUba1M"
            }
        ]
    },
    {
        "id": "5a3e8db7c32d22501b353f1c",
        "name": "教育学",
        "icon": "",
        "children": [
            {
                "id": "5a3e8db7c32d22501b353f2f",
                "name": "教育",
                "icon": "http://cjdaoss.oss-cn-beijing.aliyuncs.com/NEvY_mhFD_9A-RSu"
            },
            {
                "id": "5a3e8db7c32d22501b353f30",
                "name": "心理",
                "icon": "http://cjdaoss.oss-cn-beijing.aliyuncs.com/1r9gh7_1YP5Vz-e0"
            },
            {
                "id": "5a3e8db7c32d22501b353f31",
                "name": "体育",
                "icon": "http://cjdaoss.oss-cn-beijing.aliyuncs.com/IZB1BGWIDwY-2Hlq"
            }
        ]
    },
    {
        "id": "5aa7d20db73c78521d918d6c",
        "name": "哲学 | 历史 | 艺术 | 军事",
        "icon": "",
        "children": [
            {
                "id": "5a3e8db7c32d22501b353f47",
                "name": "哲学",
                "icon": "https://cjdaoss.oss-cn-beijing.aliyuncs.com/w6krJ4B5QU7zsrk6.png"
            },
            {
                "id": "5a3e8db7c32d22501b353f5c",
                "name": "历史",
                "icon": "https://cjdaoss.oss-cn-beijing.aliyuncs.com/EsOn93tDsaiFF3GJ.png"
            },
            {
                "id": "5a3e8db7c32d22501b353f32",
                "name": "艺术",
                "icon": "https://cjdaoss.oss-cn-beijing.aliyuncs.com/xWZFmoQPbMc5tdZF.png"
            },
            {
                "id": "5a534ae247e16207bd43f5a5",
                "name": "军事",
                "icon": "https://cjdaoss.oss-cn-beijing.aliyuncs.com/HtwvQN_udpirW5it.png"
            }
        ]
    },
    {
        "id": "5aa7d224b73c78521d918d6d",
        "name": "农学 | 医学",
        "icon": "",
        "children": [
            {
                "id": "5a3e8db7c32d22501b353f33",
                "name": "农学",
                "icon": "https://cjdaoss.oss-cn-beijing.aliyuncs.com/J6iH2ZCqjCv90-55.png"
            },
            {
                "id": "5a3e8db7c32d22501b353f57",
                "name": "医学",
                "icon": "https://cjdaoss.oss-cn-beijing.aliyuncs.com/Ib3dYjYguSwg7WL5.png"
            }
        ]
    },
    {
        "id": "5ba455691d9b2f4170da94f1",
        "name": "英语计算机考级",
        "icon": "",
        "children": [
            {
                "id": "5ba72eebc3e4e8431114fc32",
                "name": "英语考级",
                "icon": "https://cjdaoss.oss-cn-beijing.aliyuncs.com/EM8IEydMAVe0Frcw.png"
            },
            {
                "id": "5ba72f0bc3e4e8431114fc33",
                "name": "计算机考级",
                "icon": "https://cjdaoss.oss-cn-beijing.aliyuncs.com/VtkcPfJCibzvc8_V.png"
            }
        ]
    },
    {
        "id": "5b93a90e2d5e8924571b07f0",
        "name": "考研公共课",
        "icon": "",
        "children": [
            {
                "id": "5ba7a9b6c3e4e84311150185",
                "name": "政治",
                "icon": "https://cjdaoss.oss-cn-beijing.aliyuncs.com/xUEtFuDS2a1Lc1JH.png"
            },
            {
                "id": "5ba7aa0ec3e4e84311150188",
                "name": "英语1",
                "icon": "https://cjdaoss.oss-cn-beijing.aliyuncs.com/RMFSpf-wbH1I1VdU.png"
            },
            {
                "id": "5ba7aa4bc3e4e8431115018c",
                "name": "英语2",
                "icon": "https://cjdaoss.oss-cn-beijing.aliyuncs.com/HrYOYxGWPuqYJuTU.png"
            },
            {
                "id": "5ba7aa8cc3e4e8431115018d",
                "name": "数学1",
                "icon": "https://cjdaoss.oss-cn-beijing.aliyuncs.com/s1S6sJx3sloM-hfT.JPG"
            },
            {
                "id": "5ba7aaf4c3e4e84311150193",
                "name": "数学2",
                "icon": "https://cjdaoss.oss-cn-beijing.aliyuncs.com/B-M7GpzXttMEqpiN.JPG"
            },
            {
                "id": "5ba7ab36c3e4e84311150196",
                "name": "数学3",
                "icon": "https://cjdaoss.oss-cn-beijing.aliyuncs.com/HTGAjys-ZFPYTQsI.JPG"
            }
        ]
    },
    {
        "id": "5bd5c87324f7ce2b227eb63f",
        "name": "期末",
        "icon": "",
        "children": []
    }
]';

$tstk = [
    'subList' => [
        'ts' => '1544855836432',
        'tk' => '217a011fea775f9dde54450a40f1f7ad',
    ],
    'subBookList' => [
        'ts' => '1545018938668', //1544966679710 , 1544865697161
        'tk' => '20e9bbc00824c7c8a3785cfda223c603',  // 1a373b325fc64e610f4e0838cb78a44c,  ba09ead18f0f8e662ea0325f0d706b77
    ],
    'bookDocs' => [
        'ts' => '1544865728321',
        'tk' => '2e48f570a289e1509f03333e7ef03b19',
    ],
    'BookPic' => [
        'ts' => '1544865748885',
        'tk' => 'c962e0ffad4616be39996da3f6c56495',
    ]
];

global $proxy;
$proxy = [
//    '51.38.71.101:8080',
//    '88.99.149.188:31288',
    '221.7.255.167:8080',
    '125.46.0.62:53281',
    '170.239.46.50:60769',

    '221.7.255.167:8080',
    '125.46.0.62:53281',
    '170.239.46.50:60769',
];

global $header;
$header = [
    'charset' => 'utf-8',
    'Accept-Encoding' => 'gzip',
    'x-token' =>'cd17a149-3c78-4402-82f3-3ffeec32b514',//d6acbcf5-af75-4ded-a782-43d17ab7af0b
    //2ac67a2b-c92a-4b01-ae7d-6acbf043b3d1
    'referer' => 'https://servicewechat.com/wx0d02b34ae4078c11/24/page-frame.html',
    'tk' => '217a011fea775f9dde54450a40f1f7ad',
    'content-type' => 'application/json',
    'User-Agent' => 'Mozilla/5.0 (Linux; Android 6.0.1; ATH-AL00 Build/HONORATH-AL00; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/55.0.2883.91 Mobile Safari/537.36 MicroMessenger/6.7.3.1360(0x2607033B) NetType/WIFI Language/zh_CN Process/appbrand0',
    'Host' => 'chaojidaan.com',
    'Connection' => 'Keep-Alive',
];

$getBooksTs = '1544859832293';
$getBooksTk = 'e0107a2b646b8af1f2c26b9c74308798';
$getDocTs = '1544869663611';
$getDocTk = '84bd2aff65a7189e8e6089f630a68651';
$getPicTs = '1544864328625';
$getPicTk = 'daa0a2796ad56e389b3c6dfb061263a4';

$categoryArray = json_decode($subList, true);
foreach ($categoryArray as $category) {
    $categoryPath = DAANJUN_PATH . '/' . $category['name'];
    if (strpos($categoryPath, ' ') !== false) {
        $categoryPath = str_replace(' ', '', $categoryPath);
    }
    if (!is_dir($categoryPath)) {
        mkdir($categoryPath, 0777, true);
    }
    try {
        foreach ($category['children'] as $sub) {
            $subPath = $categoryPath . '/' . $sub['name'];
            if (!is_dir($subPath)) {
                mkdir($subPath, 0777, true);
            }
            echo '开始获取bookid' . PHP_EOL;
            list($code, $subCont) = getBooks($sub['name'], $sub['id'], $getBooksTs, $getBooksTk);
            if ($code != 200) {
                echo '获取科目' . $sub['name'] . '失败' . PHP_EOL;
                recordLog(json_encode([
                    '科目' => $sub['name'],
                    '科目id' => $sub['id'],
                ]));
                continue;
            }
            echo '获取科目' . $sub['name'] . '成功' . PHP_EOL;
            $booksArray = json_decode($subCont, true);
            foreach ($booksArray as $book) {
                $bookPath = $subPath . '/' . $book['name']; //因为空格的原因，后面写入读取要注意
                if (strpos($bookPath, ' ') !== false) {
                    $bookPath = str_replace(' ', '', $bookPath);
                }
                if(!is_dir($bookPath)) {
                    mkdir($bookPath, 0777, true);
                }
                $bookJson = $bookPath . '/' . 'book.json';
                if (file_exists($bookJson)) {
                    $bookJsonArray = json_decode(file_get_contents($bookJson), true);
                    if (isset($bookJsonArray['finish'])) {
                        echo $book['name'] . '文件已经成功下载，跳过' . PHP_EOL;
                        continue;
                    }
                }
                list($code, $DocCont) = getDocs($book['name'], $book['id'], $getDocTs, $getDocTk);
                $docsArray = json_decode($DocCont, true);
                if ($code != 200 || empty($docsArray)) {
                    echo '获取书本' . $book['name'] . '失败' . PHP_EOL;
                    recordLog(json_encode([
                        '书名' => $book['name'],
                        '书id' => $book['id'],
                    ]));
                    continue;
                }
                $isShared = shareBook($book['name'], $book['id']);
                if (!$isShared) {
                    echo '分享书本' . $book['name'] . '失败' . PHP_EOL;
                    recordLog(json_encode([
                        '书名' => $book['name'],
                        '书id' => $book['id'],
                    ]));
                    continue;
                }
                $docNum = 0;
                foreach ($docsArray['docs'] as $doc) {
                    list($code, $picCont) = getPics($doc['name'], $doc['id'], $getPicTs, $getPicTk);
                    if ($code != 200 || empty($picCont)) {
                        echo '获取doc图片' . $doc['name'] . '失败' . PHP_EOL;
                        recordLog(json_encode([
                            'doc名' => $doc['name'],
                            'docid' => $doc['id'],
                        ]));
                        continue;
                    }
                    $docFile = $bookPath . '/' . $doc['name'];
                    if (strpos($docFile, ' ') !== false) {
                        $docFile = str_replace(' ', '', $docFile);
                    }
                    file_put_contents($docFile, $picCont);
                    $docNum += 1;
                }
                if ($docNum == count($docsArray['docs'])) {
                    $docsArray['finish'] = true;
                }
//echo __LINE__ . '输出个数：' . PHP_EOL;
//                echo 'docnum:' . $docNum . ' count:' . count($docsArray['docs']) . PHP_EOL;
                file_put_contents($bookJson, json_encode($docsArray));
//                var_dump($book);
//                die();
            }
        }
    }catch (Exception $e) {
        echo '鸭儿， 又发生了异常:' . $e->getMessage() . PHP_EOL;
    }
}


function request($method, $url, $tk=null, $body=null) {
    global $header;
    global $proxy;
    $httpClient = new GuzzleHttp\Client();
    for($i=0; $i<5; $i++) {
        echo '开始尝试第' . ($i+1) . '次' . PHP_EOL;
        try{
            $option = [
                'connect_timeout' => 20,
                'headers' => $header,
                'proxy' => [
                    'https' => $proxy[rand(0,7)],
                ],
            ];
            if (!empty($tk)) {
                $option['headers']['tk'] = $tk;
            }
            if ($body != null) {
                $option['body'] = $body;
            }
            $resp = $httpClient->request($method, $url, $option);
            $code = $resp->getStatusCode();
            $cont = $resp->getBody()->getContents();
            if ($code == 200) {
                echo __LINE__ . ': 请求成功' . PHP_EOL;
                break;
            }else{
                echo __LINE__ . ': 获取第' . ($i+1) .'次失败' . PHP_EOL;
            }
        }catch (Exception $e) {
            $code = 500;
            $cont = '';
        }
    }

    return [$code, $cont];
}

function getBooks($subName, $subId, $ts, $tk) {
    $url = 'https://chaojidaan.com/api/v1/books?categoryId='.$subId.'&page=1&perPage=1000&app_name=daanjun&ts='.$ts;
    list($code, $cont) = request('GET', $url, $tk);
    if ($code != 200) {
        warnLog('获取科目' . $subName . '列表失败');
        echo __LINE__ . ': 获取科目' . $subName . '列表失败' . PHP_EOL;
    }else{
        echo __LINE__ . ': 获取科目' . $subName . '列表成功' . PHP_EOL;
    }

    return [$code, $cont];
}

function getDocs($bookName, $bookId, $ts, $tk) {
    $url = 'https://chaojidaan.com/api/v1/books/'.$bookId.'?app_name=daanjun&ts='.$ts;
    list($code, $cont) = request('GET', $url, $tk);
    if ($code != 200) {
        warnLog('获取书本' . $bookName . '下的doc信息失败');
        echo __LINE__ . ': 获取书本' . $bookName . '下的doc信息失败' . PHP_EOL;
    }else{
        echo __LINE__ . ': 获取书本' . $bookName . '下的doc信息成功' . PHP_EOL;
    }
//var_dump(json_decode($cont));
//    die();
    return [$code, $cont];
}

function getPics($docName, $docId, $ts, $tk) {
    $url = 'https://chaojidaan.com/api/v1/docs/'.$docId.'?app_name=daanjun&ts='.$ts;
    list($code, $cont) = request('GET', $url, $tk);
    if ($code != 200) {
        warnLog('获取doc' . $docName . '列表中的图片信息失败');
    }else{
        echo __LINE__ . ': 获取doc' . $docName . '列表中的图片信息成功';
    }
    echo __LINE__ . '输出请求图片列表的内容：' . PHP_EOL;
    var_dump($cont);

    return [$code, $cont];
}

function shareBook($bookName, $bookId) {
    $tk = '418a539f49d7717ed6ae14d05cddf7332';
    $url = 'https://chaojidaan.com/api/v1/books/'.$bookId.'/share';
    $body = '{"app_name":"daanjun","ts":"' . $bookId . '"}';
    list($code, $cont) = request('POST', $url, $tk, $body);
    if ($code != 200) {
        echo __LINE__ . ': 分享book' . $bookName . '失败' . PHP_EOL;
        warnLog('分享book' . $bookName . '失败');
    }else{
        echo __LINE__ . ': 分享book' . $bookName . '成功' . PHP_EOL;
    }
    echo __LINE__. ': 分享的cont:' . PHP_EOL;

    var_dump($cont);

    return $code;
}

die();
$httpClient = new GuzzleHttp\Client();
$option = [
    'connect_timeout' => 40,
    'headers' => $subHeader,
    'proxy' => [
        'https' => $proxy[rand(0,5)],
    ],
];
try{
    $option['headers']['tk'] = $tstk['subList']['tk'];
    echo 'proxy:' . $option['proxy']['https'] . PHP_EOL;
    var_dump($option);
    echo 'url:' . $getSubListUrl . $tstk['subList']['ts'] . PHP_EOL;
    $subListResp = $httpClient->request('GET', trim($getSubListUrl . $tstk['subList']['ts']) , $option);
    if ($subListResp->getStatusCode() != 200) {
        $message = $subListResp->getBody()->getContents();
        warnLog($message);
        echo '获取科目列表失败，请检查重新启动' . PHP_EOL;
        exit(-1);
    }
    $subListCont = $subListResp->getBody()->getContents();
    $subListArray = json_decode($subListCont, true);
    foreach ($subListArray as $key => $subList) {
        $subPath = DAANJUN_PATH . (isset($subList['name']) ? $subList['name'] : 'random' . $key);
        if (!is_dir($subPath)) {
            mkdir($subPath, 0777, true);
        }
        if (!isset($subList['children'])) {
            $message = $subPath . '下面的children为空';
            warnLog($message);
            continue;
        }
        foreach ($subList['children'] as $key1 => $sub) {
            $subChildrenPath = $subPath . '/' . (isset($sub['name']) ? $sub['name'] : 'subChildren' . $key1);
            if (!is_dir($subChildrenPath)) {
                mkdir($subChildrenPath, 0777, true);
            }
            $subId = $sub['id'];
            $getSubBookListUrl = $getSubBookListUrl . 'ts=' . $tstk['subBookList']['ts'] . '&';
            $getSubBookListUrl = $getSubBookListUrl . 'categoryId=' . $subId;
            $option['headers']['tk'] = $tstk['subBookList']['tk'];
            $subBookListResp = $httpClient->request('GET', $getSubBookListUrl, $option);
            $code = $subBookListResp->getStatusCode();
            if ($code != 200) {
                $message = $subChildrenPath . '为空';
                warnLog($message);
                continue;
            }
            $subChildrenContent = $subBookListResp->getBody()->getContents();
            $subChildrenArray = json_decode($subChildrenContent, true);
            if (empty($subChildrenArray)) {
                warnLog('subChildrenContent 为空：' . $subChildrenContent);
                continue;
            }
            foreach ($subChildrenArray as $key2 => $book) {
                warnLog('开始书本：' . $book['name']);
                $bookPath = $subChildrenPath . '/' . (isset($book['id']) ? $book['id'] : 'bookId' . $key2);
                if (is_dir($bookPath)) {
                    mkdir($bookPath, 0777, true);
                }
                $bookJsonFile = $bookPath . 'book.json';
                if (is_file($bookJsonFile)) {
                    $bookJson = file_get_contents($bookJsonFile);
                    $bookArray = json_decode($bookJson, true);
                    if (isset($bookArray['finish'])) {
                        continue;
                    }
                }
                $shareRet = share($httpClient, $book['id']);
                if (!$shareRet) {
                    warnLog('分享book失败， book名称是：' . $book['name'] . 'bookid是：' . $book['id']);
                    $shareRet = share($httpClient, $book['id']);
                }

                //获取书的docs列表
                $getBookDocsUrl = $getBookDocsUrl . $book['id'] . '?app_name=daanjun&ts=' . $tstk['bookDocs']['ts'];
                $option['headers']['tk'] = $tstk['bookDocs']['tk'];
                echo 'url:' . $getBookDocsUrl . PHP_EOL;
                var_dump($option);
                $docsResp = $httpClient->request('GET', $getBookDocsUrl, $option);
                $docsCode = $docsResp->getStatusCode();
                if ($docsCode != 200) {
                    warnLog('获取book docs 失败' . 'book 名称是：' . $book['name'], 'bookid是：'. $book['id']);
                }
                $docsCont = $docsResp->getBody()->getContents();
                var_dump($docsCont);
                $docsArray = json_decode($docsCont, true);
                if (isset($docsArray['docs'])) {
                    warnLog('book' . $book['name'] . 'docs为空');
                    continue;
                }
                $docFlag = false;
                $docNum = 0;
                foreach ($docsArray['docs'] as $doc) {
                    $docPath = $bookPath .'/' . $doc['id'];
                    if (file_exists($docPath)) {
                        continue;
                    }
                    $docId = $doc['id'];
                    $option['headers']['tk'] = $tstk['BookPic']['tk'];
                    $getBookPicUrl = $getBookPicUrl . $tstk['BookPic'].['ts'];
                    $picResp = $httpClient->request('GET', $getBookPicUrl, $option);
                    $picCode = $picResp->getStatusCode();
                    if ($picCode != 200) {
                        warnLog('获取图书'. $book['name'] .'id:' . $book['id']. 'docid:' . $doc['id']. '失败');
                        continue;
                    }
                    $docJson = json_encode($picResp->getBody()->getContents());
                    file_put_contents($docPath, $docJson);
                    $docNum += 1;
                }
                if ($docNum == count($docsArray)) {
                    $book['finish'] = true;
                }
                file_put_contents($bookJsonFile, json_encode($book));
            }
//            var_dump($subChildrenContent);die();

        }
//        var_dump($subList);die();
        die();
    }

}catch (Exception $e){
    $message =  $e->getMessage() . ', stack:' . $e->getTraceAsString();
    warnLog($message);
    echo '获取文件列表出错, 请检查';
}


function warnLog($message) {
    $message = '[' . date('Y-m-d H:i:s', time()) . '] message:' . $message . PHP_EOL;
    file_put_contents(LOG_FILE, $message . PHP_EOL, FILE_APPEND);
}

function recordLog($message) {
    file_put_contents(RECORD_FILE, $message . PHP_EOL, FILE_APPEND);
}
function share($guzzleClient, $bookId) {
    $postShareBookUrl = 'https://chaojidaan.com/api/v1/books/' . $bookId . '/share'; //post请求方式，分享某本书，只有分享某本书后才可以获取书的所有列表信息
    $shareHeader = [
        'charset' => 'utf-8',
        'Accept-Encoding' => 'gzip',
        'x-token' => '2ac67a2b-c92a-4b01-ae7d-6acbf043b3d1',
        'referer' => 'https://servicewechat.com/wx0d02b34ae4078c11/24/page-frame.html',
        'tk' => '418a539f49d7717ed6ae14d05cddf733',
        'Content-Type' => 'application/json',
        'User-Agent' => 'Mozilla/5.0 (Linux; Android 6.0.1; ATH-AL00 Build/HONORATH-AL00; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/55.0.2883.91 Mobile Safari/537.36 MicroMessenger/6.7.3.1360(0x2607033B) NetType/WIFI Language/zh_CN Process/appbrand0',
        'Content-Length' => '43',
        'Host' => 'chaojidaan.com',
        'Connection' => 'Keep-Alive',
    ];
    $proxy = [
        '221.7.255.167:8080',
        '125.46.0.62:53281',
        '170.239.46.50:60769',
    ];
    $option = [
        'connect_timeout' => 20,
        'headers' => $shareHeader,
        'proxy' => [
            'https' => $proxy[rand(0,2)],
        ],
        'body' => '{"app_name":"daanjun","ts":"' . $bookId . '"}',
    ];
    $shareResp = $guzzleClient->request('POST', $postShareBookUrl, $option);
    $shareCont = $shareResp->getBody()->getContents();
    $shareArray = json_decode($shareCont, true);
    if (isset($shareArray['success']) && $shareArray['success']) {
        return true;
    }
    return false;
}



