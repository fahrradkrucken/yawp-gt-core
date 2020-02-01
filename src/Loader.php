<?php


namespace FahrradKruken\YAWP\GTheme;

class Loader
{
    private $templateDir = '';
    private $modulesDir = '';
    private $blocksDir = '';

    public function __construct($templateDir = '', $modulesDir = '', $blocksDir = '')
    {
        $this->templateDir = !empty($templateDir) ?
            Helper::normalizePath($templateDir) :
            Helper::normalizePath(get_template_directory() . '/tpl/');
        $this->modulesDir = !empty($modulesDir) ?
            Helper::normalizePath($modulesDir) :
            Helper::normalizePath(get_template_directory() . '/app/modules/');
        $this->blocksDir = !empty($blocksDir) ?
            Helper::normalizePath($blocksDir) :
            Helper::normalizePath(get_template_directory() . '/app/blocks/');
    }

    public function pageTemplate($templateName, $vars = [], $optional = true)
    {
        $this->template($templateName, $vars, $optional, $this->templateDir);
    }

    public function module($templateName, $vars = [], $optional = true)
    {
        $this->template($templateName, $vars, $optional, $this->modulesDir);
    }

    public function block($blockName, $optional = true)
    {
        $dir = $this->blocksDir;
        $path = Helper::normalizePath($dir . $blockName . '.php');
        if (file_exists($path)) require($path);
        else if (!$optional) wp_die(new \WP_Error('ERR_AUTOLOAD_BLOCK', 'ERR_AUTOLOAD_BLOCK'));
    }

    private function template($templateName, $vars = [], $optional = true, $dir = '')
    {
        $dir = !empty($dir) ? $dir : $this->modulesDir;
        $templatePath = Helper::normalizePath($dir . $templateName . '.php');
        if (file_exists($templatePath)) {
            if (!empty($vars))
                extract($vars, EXTR_OVERWRITE);
            include($templatePath);
        } else
            if (!$optional)
                wp_die(new \WP_Error('ERR_AUTOLOAD_TEMPLATE', 'ERR_AUTOLOAD_TEMPLATE'));
    }
}