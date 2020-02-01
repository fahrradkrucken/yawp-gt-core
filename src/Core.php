<?php


namespace FahrradKruken\YAWP\GTheme;


class Core
{
    /**
     * @var self
     */
    private static $instance;
    private static $isInitialized = false;

    /**
     * @var DynamicContent
     */
    private $dynamicContent;
    /**
     * @var Thumbnail
     */
    private $thumbnail;
    /**
     * @var Loader
     */
    private $loader;
    /**
     * @var array
     */
    private $config = [];
    /**
     * @var Bootstrap
     */
    private $bootstrap;

    /**
     * Core constructor.
     *
     * @param array $config
     */
    private function __construct($config = [])
    {
        /**
         * Init thumbnail
         */
        $this->thumbnail = new Thumbnail();

        /**
         * Init Dynamic content
         */
        $this->dynamicContent = new DynamicContent();
        add_action('wp_footer', function () {
            ob_start();
            ?>
            <div id="page-dynamic-html-parts">
                <?= Core::dc()->getHtml(); ?>
            </div>
            <style type="text/css" id="page-dynamic-styles">
                <?= Core::dc()->getStyles(); ?>
            </style>
            <?php
            $dynamicContentInFooter = ob_get_clean();
            echo $dynamicContentInFooter;
        });

        /**
         * Init Loader
         */
        $loaderTemplatePath = isset($config['path']['page_templates']) ? $config['path']['page_templates'] : '';
        $loaderModulesPath = isset($config['path']['modules']) ? $config['path']['modules'] : '';
        $loaderBlocksPath = isset($config['path']['blocks']) ? $config['path']['blocks'] : '';
        $this->loader = new Loader($loaderTemplatePath, $loaderModulesPath, $loaderBlocksPath);

        /**
         * Init Bootstrap
         */
        $this->bootstrap = new Bootstrap();
    }

    /**
     * Call this only once in your functions.php
     *
     * @param array $config
     *
     * [
     *      'path' => [ // loader constants
     *          'page_templates' => // default: get_template_directory() . '/tpl/'
     *          'modules' =>     // default: get_template_directory() . '/app/modules/'
     *          'blocks' =>     // default: get_template_directory() . '/app/blocks/'
     *      ],
     * ]
     *
     */
    public static function init($config = [])
    {
        if (!self::$isInitialized) {
            self::$isInitialized = true;
            self::$instance = new self($config);
        }
    }

    public static function getInstance()
    {
        if (!self::$isInitialized) {
            wp_die(new \WP_Error('YAWPT_CORE_NOT_INITIALIZED',
                'You should initialize core before start using it - Core::init($config = []) '));
        }
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Use this to load image thumbnails depending on wp_is_mobile()
     *
     * @return Thumbnail
     */
    public static function tmb()
    {
        return self::getInstance()->thumbnail;
    }

    /**
     * Use this to add CSS styles dynamically, so they'll appear in footer of the page.
     * You can do the same with HTML, ex: add HTML of modals/popups that'll appear in footer of the page.
     *
     * @return DynamicContent
     */
    public static function dc()
    {
        return self::getInstance()->dynamicContent;
    }

    /**
     * Use this to load your page components
     *
     * @return Loader
     */
    public static function load()
    {
        return self::getInstance()->loader;
    }

    /**
     * Put here some params to get them later - all of them or by name
     *
     * @param string     $name
     * @param null|mixed $value
     *
     * @return null|mixed|void
     */
    public static function config($name = '', $value = null)
    {
        if (!empty($name) && !empty($value)) {
            self::getInstance()->config[$name] = $value;
        } elseif (!empty($name)) {
            return isset(self::getInstance()->config[$name]) ?
                self::getInstance()->config[$name] :
                null;
        }
        return self::getInstance()->config;
    }

    /**
     * Use this to add default actions, like enqueue scripts or after_theme_setup functions
     *
     * @return Bootstrap
     */
    public static function bootstrap()
    {
        return self::getInstance()->bootstrap;
    }
}