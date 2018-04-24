<?php
namespace Org\Util;

/**
 *
 */
class FileUpdateASDataURL
{

    private $path       = './Public/uploads/';
    private $pathSuffix = "Y-m-d";

    private $_error = '';

    /**
     * 判断 是否 为 base64 图像
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-03-01T16:48:57+0800
     * @param    [type]                   $data [description]
     * @return   boolean                        [description]
     */
    public static function isDataURL($data)
    {
        $rest = substr($data, 0, 5);
        return $rest == 'data:';
    }

    /**
     * 上传
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-03-01T16:46:11+0800
     * @param    [type]                   $data [description]
     * @param    string                   $path [description]
     * @param    string                   $name [description]
     * @return   [type]                         [description]
     */
    public function update($data, $name = '')
    {
        if (!self::isDataURL($data)) {
            $this->_error = '上传失败,文件格式不正确！';
            return false;
        }

        if (($suffix = $this->fileSuffix($data)) === false) {
            return false;
        }

        if (($content = $this->fileContents($data)) === false) {
            return false;
        }

        if (($path = $this->buildPath()) === false) {
            return false;
        }

        if ($name == '') {
            $name = $this->fileName();
        }

        $fileName = $path . $name . $suffix;

        if (file_put_contents($fileName, $content) === false) {
            $this->_error = '上传失败,未知错误';
            return false;
        }

        return $fileName;
    }

    /**
     * [getError description]
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-03-01T17:34:52+0800
     * @return   [type]                   [description]
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * 生成加检查目录
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-03-01T16:53:25+0800
     * @return   [type]                   [description]
     */
    private function buildPath()
    {
        $fileDirUtil = new FileDirUtil();
        $path        = $this->path . date($this->pathSuffix) . '/';

        if (!file_exists($path)) {
            $fileDirUtil->createDir($path);
        }

        if (is_writable($path)) {
            return $path;
        }

        $this->_error = '上传失败,目录不可写！';
        return false;
    }

    /**
     * [fileContents description]
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-03-01T17:29:52+0800
     * @param    [type]                   $data [description]
     * @return   [type]                         [description]
     */
    private function fileContents($data)
    {
        list($format, $contentAndFilename) = explode('base64,', $data);
        unset($data);
        list($content, $filename) = explode('#', $contentAndFilename);
        return base64_decode($content);
    }

    /**
     * 获取文件后缀
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-03-01T17:06:53+0800
     * @param    string                   $value [description]
     * @return   [type]                          [description]
     */
    private function fileSuffix($data)
    {
        list($content, $filename) = explode('#', $data);
        unset($content);
        $suffix = '.' . pathinfo($filename, PATHINFO_EXTENSION);
        return $suffix;
    }

    /**
     * [fileName description]
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-03-01T17:27:05+0800
     * @return   [type]                          [description]
     */
    private function fileName()
    {
        return date('Y-m-d') . '.' . time() . rand(1000, 99999);
    }

}
