<?php
namespace Org\Util;

class Ueditor
{
    private $actionArray = array(
        'config',
        'uploadimage',
        'uploadscrawl',
        'uploadvideo',
        'uploadfile',
        'listimage',
        'listfile',
        'catchimage',
        'upfile',
    );

    /**
     * 构建ueditor编辑器
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-03-29T14:01:42+0800
     * @param    [type]                   $name      [description]
     * @param    [type]                   $content   [description]
     * @param    [type]                   $serverUrl [description]
     * @return   [type]                              [description]
     */
    public static function init($name, $content = '', $homeRrl = "/Public/adminTpl/js/ueditor/", $serverUrl = "/Admin/Ueditor/index")
    {
        $ueditorContainer = 'ueditor_' . time();
        echo <<<EOT
<!-- 加载编辑器的容器 -->
<script id="{$ueditorContainer}" name="{$name}" type="text/plain">{$content}</script>
<!-- 配置文件 -->
<script type="text/javascript" src="{$homeRrl}ueditor.config.js"></script>
<!-- 编辑器源码文件 -->
<script type="text/javascript" src="{$homeRrl}ueditor.all.js"></script>
<!-- 实例化编辑器 -->
<script type="text/javascript">
    var ue = UE.getEditor('{$ueditorContainer}',{
        serverUrl:'{$serverUrl}',
        allowDivTransToP:false
    });
</script>
EOT;

    }

    public function __construct($config = array())
    {
        # code...
    }
    /**
     * [doAction description]
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-03-29T11:31:57+0800
     * @param    [type]                   $action [description]
     * @return   [type]                           [description]
     */
    public function doAction($action)
    {
        if (!in_array($action, $this->actionArray)) {
            return array(
                'state' => '请求地址出错',
            );
        }

        $methode = '_' . $action;
        if (method_exists($this, $methode)) {
            return $this->$methode();
        } else {
            return array(
                'state' => '请求地址出错',
            );
        }
    }

    /**
     * 上传图片
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-03-28T17:56:44+0800
     * @return   [type]                   [description]
     */
    private function _uploadimage()
    {
        $CONFIG = $this->_config();

        $config = array(
            'maxSize'  => $CONFIG['imageMaxSize'],
            'rootPath' => '.',
            'savePath' => $CONFIG['imagePathFormat'],
            'saveName' => array('uniqid', ''),
            'exts'     => array('jpg', 'gif', 'png', 'jpeg', 'bmp'),
            'autoSub'  => true,
            'subName'  => array('date', 'Y-m-d'),
        );

        $upload = new \Think\Upload($config); // 实例化上传类

        $info = $upload->uploadOne($_FILES[$CONFIG['imageFieldName']]);
        if (!$info) {
            return ['state' => $upload->getError()];
        } else {
            return [
                "state"    => 'SUCCESS',
                "url"      => $info['savepath'] . $info['savename'],
                "title"    => $info['name'],
                "original" => $info['name'],
                "type"     => $info['ext'],
                "size"     => $info['size'],
            ];
        }

    }

    /**
     * ueditor配置文件
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-03-29T10:57:52+0800
     * @return   [type]                   [description]
     */
    private function _config()
    {
        return [
            /* 上传图片配置项 */
            'imageActionName'         => 'uploadimage',
            'imageFieldName'          => 'upfile',
            'imageMaxSize'            => '1048576',
            'imageAllowFiles'         => [
                '0' => '.png',
                '1' => '.jpg',
                '2' => '.jpeg',
                '3' => '.gif',
                '4' => '.bmp',
            ],
            'imageCompressEnable'     => '1',
            'imageCompressBorder'     => '1600',
            'imageInsertAlign'        => 'none',
            'imageUrlPrefix'          => '',
            'imagePathFormat'         => '/Public/uploads/article/',
            ///* 涂鸦图片上传配置项 */
            'scrawlActionName'        => 'uploadscrawl',
            'scrawlFieldName'         => 'upfile',
            'scrawlPathFormat'        => '/Public/uploads/article/',
            'scrawlMaxSize'           => '1048576',
            'scrawlUrlPrefix'         => '',
            'scrawlInsertAlign'       => 'none',
            /* 截图工具上传 */
            'snapscreenActionName'    => 'uploadimage',
            'snapscreenPathFormat'    => '/Public/uploads/article/',
            'snapscreenUrlPrefix'     => '',
            'snapscreenInsertAlign'   => 'none',
            'catcherLocalDomain'      => [
                '0' => '127.0.0.1',
                '1' => 'localhost',
                '2' => 'img.baidu.com',
            ],
            /* 抓取远程图片配置 */
            'catcherActionName'       => 'catchimage',
            'catcherFieldName'        => 'source',
            'catcherPathFormat'       => '/Public/uploads/article/',
            'catcherUrlPrefix'        => '',
            'catcherMaxSize'          => '1048576',
            'catcherAllowFiles'       => [
                '0' => '.png',
                '1' => '.jpg',
                '2' => '.jpeg',
                '3' => '.gif',
                '4' => '.bmp',
            ],
            /* 上传视频配置 */
            'videoActionName'         => 'uploadvideo',
            'videoFieldName'          => 'upfile',
            'videoPathFormat'         => '/Public/uploads/video/',
            'videoUrlPrefix'          => '',
            'videoMaxSize'            => '1048576',
            'videoAllowFiles'         => [
                '0'  => '.flv',
                '1'  => '.swf',
                '2'  => '.mkv',
                '3'  => '.avi',
                '4'  => '.rm',
                '5'  => '.rmvb',
                '6'  => '.mpeg',
                '7'  => '.mpg',
                '8'  => '.ogg',
                '9'  => '.ogv',
                '10' => '.mov',
                '11' => '.wmv',
                '12' => '.mp4',
                '13' => '.webm',
                '14' => '.mp3',
                '15' => '.wav',
                '16' => '.mid',
            ],
            /* 上传文件配置 */
            'fileActionName'          => 'uploadfile',
            'fileFieldName'           => 'upfile',
            'filePathFormat'          => '/Public/uploads/video/file',
            'fileUrlPrefix'           => '',
            'fileMaxSize'             => '1048576',
            'fileAllowFiles'          => [
                '0'  => '.png',
                '1'  => '.jpg',
                '2'  => '.jpeg',
                '3'  => '.gif',
                '4'  => '.bmp',
                '5'  => '.flv',
                '6'  => '.swf',
                '7'  => '.mkv',
                '8'  => '.avi',
                '9'  => '.rm',
                '10' => '.rmvb',
                '11' => '.mpeg',
                '12' => '.mpg',
                '13' => '.ogg',
                '14' => '.ogv',
                '15' => '.mov',
                '16' => '.wmv',
                '17' => '.mp4',
                '18' => '.webm',
                '19' => '.mp3',
                '20' => '.wav',
                '21' => '.mid',
                '22' => '.rar',
                '23' => '.zip',
                '24' => '.tar',
                '25' => '.gz',
                '26' => '.7z',
                '27' => '.bz2',
                '28' => '.cab',
                '29' => '.iso',
                '30' => '.doc',
                '31' => '.docx',
                '32' => '.xls',
                '33' => '.xlsx',
                '34' => '.ppt',
                '35' => '.pptx',
                '36' => '.pdf',
                '37' => '.txt',
                '38' => '.md',
                '39' => '.xml',
            ],
            /* 列出指定目录下的图片 */
            'imageManagerActionName'  => 'listimage',
            'imageManagerListPath'    => '/Public/uploads/',
            'imageManagerListSize'    => '20',
            'imageManagerUrlPrefix'   => '',
            'imageManagerInsertAlign' => 'none',
            'imageManagerAllowFiles'  => [
                '0' => '.png',
                '1' => '.jpg',
                '2' => '.jpeg',
                '3' => '.gif',
                '4' => '.bmp',
            ],
            /* 列出指定目录下的文件 */
            'fileManagerActionName'   => 'listfile',
            'fileManagerListPath'     => '/Public/uploads/',
            'fileManagerUrlPrefix'    => '',
            'fileManagerListSize'     => '20',
            'fileManagerAllowFiles'   => [
                '0'  => '.png',
                '1'  => '.jpg',
                '2'  => '.jpeg',
                '3'  => '.gif',
                '4'  => '.bmp',
                '5'  => '.flv',
                '6'  => '.swf',
                '7'  => '.mkv',
                '8'  => '.avi',
                '9'  => '.rm',
                '10' => '.rmvb',
                '11' => '.mpeg',
                '12' => '.mpg',
                '13' => '.ogg',
                '14' => '.ogv',
                '15' => '.mov',
                '16' => '.wmv',
                '17' => '.mp4',
                '18' => '.webm',
                '19' => '.mp3',
                '20' => '.wav',
                '21' => '.mid',
                '22' => '.rar',
                '23' => '.zip',
                '24' => '.tar',
                '25' => '.gz',
                '26' => '.7z',
                '27' => '.bz2',
                '28' => '.cab',
                '29' => '.iso',
                '30' => '.doc',
                '31' => '.docx',
                '32' => '.xls',
                '33' => '.xlsx',
                '34' => '.ppt',
                '35' => '.pptx',
                '36' => '.pdf',
                '37' => '.txt',
                '38' => '.md',
                '39' => '.xml',
            ],
        ];
    }
}
