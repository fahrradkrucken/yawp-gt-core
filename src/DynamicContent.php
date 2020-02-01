<?php


namespace FahrradKruken\YAWP\GTheme;


class DynamicContent
{
    /**
     * CSS pseudo elements names
     */
    static $PSEUDO_BEFORE = '::before';
    static $PSEUDO_AFTER = '::after';
    static $PSEUDO_HOVER = ':hover';
    static $PSEUDO_ACTIVE = ':active';
    static $PSEUDO_FOCUS = ':focus';

    /**
     * Section styles represented as assoc array [...section_class => [...property => value...]...]
     *
     * @var array
     */
    private $styles = [];

    /**
     * Key-value storage for some styling data that we'll need globally for the page
     *
     * @var array
     */
    private static $stylesHtml = [];

    /**
     * How to prefix block with custom styles
     *
     * @var string
     */
    private $stylesBlockPrefix = 'yawp';


    /**
     * Adds styles and returns unique ID for this section
     *
     * @param array  $styles         [...property => value...]
     * @param string $pseudo_element Select from class Constants starts with "PSEUDO"
     *
     * @return string Style ID (class or ID of an element)
     */
    public function setStyle($styles = [], $pseudo_element = '')
    {
        $style_id = $this->getStyleID($pseudo_element);
        $this->styles[$style_id] = $styles;
        return $style_id;
    }

    /**
     * Returns css for all registered styles
     *
     * @param bool $as_classes (instead of IDs)
     *
     * @return string Generated CSS
     */
    public function getStyles($as_classes = true)
    {
        $prefix = $as_classes ? '.' : '#';
        $styles_css = '';
        foreach ($this->styles as $class_name => $props) {
            $section_css = '';
            foreach ($props as $name => $value)
                if (!$this->isEmptyStyle($name, $value))
                    $section_css .= "{$name}:{$value};";
            $styles_css .= "{$prefix}{$class_name}{{$section_css}}";
        }
        return $styles_css;
    }


    /**
     * @param string $key
     * @param string $value
     */
    public function setHtml($key = '', $value = '')
    {
        self::$stylesHtml[$key] = (string)$value;
    }

    /**
     * @param $key
     * @return string
     */
    public function getHtml($key = null)
    {
        if (isset(self::$stylesHtml[$key]) && !empty($key)) return self::$stylesHtml[$key];
        if (!isset(self::$stylesHtml[$key]) && !empty($key)) return '';
        return implode('', self::$stylesHtml);
    }

    /**
     * @param string $pseudo_suffix
     *
     * @return string Unique Style ID
     */
    private function getStyleID($pseudo_suffix = '')
    {
        $n = count($this->styles);
        return $this->stylesBlockPrefix
            . str_pad($n, 3, "0", STR_PAD_LEFT)
            . ($pseudo_suffix ? $pseudo_suffix : '');
    }

    /**
     * Dummy check if style property is empty
     *
     * @param string $style_name
     * @param string $style_value
     *
     * @return bool
     */
    private function isEmptyStyle($style_name = '', $style_value = '')
    {
        if (empty($style_name) || empty($style_value)) return true;
        $style_value = str_replace(['px', '%', 'vh', 'vw', 'em', 'pt', 'calc', '(', ')', '"', 'url'], '', $style_value);
        $style_value = trim($style_value);
        return empty($style_value);
    }
}