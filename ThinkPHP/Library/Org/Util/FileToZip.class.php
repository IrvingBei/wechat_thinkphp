<?php
namespace Org\Util;

/**
 * zip下载类文件
 */
class FileToZip
{
    private $pathArr;
    private $savepath = "./Public/uploads/";

    public function __construct($pathArr)
    {
        $this->pathArr = $pathArr;
    }
    /**
     * 压缩文件(zip格式)
     */
    public function tozip()
    {
        $zip = new ZipArchive();
        $zipname = date('YmdHis', time());
        if (!file_exists($zipname)) {

            $zip->open($this->savepath . $zipname . '.zip', ZipArchive::OVERWRITE);//创建一个空的zip文件
            array_reduce($this->pathArr, create_function('$v,$w',"$zip->addFile($this->savepath.$zipname.'.zip', $w);"));
            $zip->close();
            $result = download($this->savepath . $zipname . '.zip');
            unlink($savepath . $zipname . '.zip'); //下载完成后要进行删除
        }
    }
}
