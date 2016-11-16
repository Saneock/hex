<?php
namespace Hex\Base;

class Section extends Object
{
    /**
     * Уникальное имя раздела, которое используется в URL
     *
     * @var string
     */
    public $name;

    /**
     * Название раздела
     *
     * @var string
     */
    public $title;

    /**
     * Имя директории раздела
     *
     * @var string
     */
    public $path;

    /**
     * @var Access
     */
    public $access = [];
}
