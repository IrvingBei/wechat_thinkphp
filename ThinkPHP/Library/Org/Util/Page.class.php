<?php

namespace Org\Util;

use \Think\Page as PageParent;

/**
 *
 */
class Page extends PageParent
{
    public function __construct($totalRows, $listRows = 20, $parameter = array())
    {
        parent::__construct($totalRows, $listRows, $parameter);
    }

    public function show()
    {
        $pages = array(
            "totalRows"  => 0,
            "totalPages" => 0,
            "listRows"   => 0,
            "now"        => 0,
        );
        if (0 == $this->totalRows) {
            return $pages;
        }

        /* 生成URL */
        $this->parameter[$this->p] = '[PAGE]';
        $this->url                 = U(ACTION_NAME, $this->parameter);
        /* 计算分页信息 */
        $this->totalPages = ceil($this->totalRows / $this->listRows); //总页数
        if (!empty($this->totalPages) && $this->nowPage > $this->totalPages) {
            $this->nowPage = $this->totalPages;
        }

        /* 计算分页临时变量 */
        $now_cool_page                             = $this->rollPage / 2;
        $now_cool_page_ceil                        = ceil($now_cool_page);
        $this->lastSuffix && $this->config['last'] = $this->totalPages;

        //总记录
        $pages['totalRows'] = $this->totalRows;

        // 总页码
        $pages['totalPages'] = $this->totalPages;

        // 每页数量
        $pages['listRows'] = $this->listRows;

        //上一页
        $up_row        = $this->nowPage - 1;
        $pages['prev'] = $up_row > 0 ? $this->url($up_row) : '';

        //下一页
        $down_row      = $this->nowPage + 1;
        $pages['next'] = ($down_row <= $this->totalPages) ? $this->url($down_row) : '';

        //第一页
        $the_first = '';
        if ($this->totalPages > $this->rollPage && ($this->nowPage - $now_cool_page) >= 1) {
            $pages['first'] = $this->url(1);
        }

        //最后一页
        $the_end = '';
        if ($this->totalPages > $this->rollPage && ($this->nowPage + $now_cool_page) < $this->totalPages) {
            $pages['end'] = $this->url($this->totalPages);
        }

        // 当前页
        $pages['now'] = $this->nowPage;

        // 数字连接
        $pages['url'] = array();
        for ($i = 1; $i <= $this->rollPage; $i++) {
            if (($this->nowPage - $now_cool_page) <= 0) {
                $page = $i;
            } elseif (($this->nowPage + $now_cool_page - 1) >= $this->totalPages) {
                $page = $this->totalPages - $this->rollPage + $i;
            } else {
                $page = $this->nowPage - $now_cool_page_ceil + $i;
            }
            if ($page > 0 && $page != $this->nowPage) {

                if ($page <= $this->totalPages) {
                    $pages['url'][$page] = $this->url($page);
                } else {
                    break;
                }
            } else {
                if ($page > 0 && $this->totalPages != 1) {
                    $pages['url'][$page] = '';
                }
            }
        }

        return $pages;

    }
}
