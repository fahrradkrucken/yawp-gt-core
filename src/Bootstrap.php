<?php


namespace FahrradKruken\YAWP\GTheme;


class Bootstrap
{
    /**
     * @param $callableOrFilePath
     *
     * @return $this
     */
    public function addActionInit($callableOrFilePath)
    {
        return $this->addAction('init', $callableOrFilePath);
    }

    /**
     * @param $callableOrFilePath
     *
     * @return $this
     */
    public function addActionAfterSetupTheme($callableOrFilePath)
    {
        return $this->addAction('after_setup_theme', $callableOrFilePath);
    }

    /**
     * @param $callableOrFilePath
     *
     * @return $this
     */
    public function addActionEnqueueScripts($callableOrFilePath)
    {
        return $this->addAction('wp_enqueue_scripts', $callableOrFilePath);
    }

    /**
     * @param $callableOrFilePath
     *
     * @return $this
     */
    public function addActionEnqueueScriptsLogin($callableOrFilePath)
    {
        return $this->addAction('login_head', $callableOrFilePath);
    }

    /**
     * @param $callableOrFilePath
     *
     * @return $this
     */
    public function addActionEnqueueScriptsAdmin($callableOrFilePath)
    {
        return $this->addAction('admin_enqueue_scripts', $callableOrFilePath);
    }

    /**
     * Add Default CSS & JS to frontend
     *
     * @param string $assetsDir - relative to theme's root directory with slashes, like '/static/'
     * @param array  $assetsList - [$id, $name, $deps] array. You can put here an absolute URL to skip $assetsDir prefix.
     *
     * @return $this
     */
    public function addAssetsOnFrontend($assetsDir = '/static/', $assetsList = [])
    {
        return $this->addAssets('wp_enqueue_scripts', $assetsDir, $assetsList);
    }

    /**
     * Add Default CSS & JS to WP dash area
     *
     * @param string $assetsDir - relative to theme's root directory with slashes, like '/static/'
     * @param array  $assetsList - [$id, $name, $deps] array. You can put here an absolute URL to skip $assetsDir prefix.
     *
     * @return $this
     */
    public function addAssetsOnBackend($assetsDir = '/static/', $assetsList = [])
    {
        return $this->addAssets('admin_enqueue_scripts', $assetsDir, $assetsList);
    }

    /**
     * Add Default CSS & JS to login pages
     *
     * @param string $assetsDir - relative to theme's root directory with slashes, like '/static/'
     * @param array  $assetsList - [$id, $name, $deps] array. You can put here an absolute URL to skip $assetsDir prefix.
     *
     * @return $this
     */
    public function addAssetsOnLogin($assetsDir = '/static/', $assetsList = [])
    {
        $assetsDir = Helper::normalizePath(get_template_directory() . $assetsDir);
        $assetsUrl = get_template_directory_uri() . $assetsDir;
        $assetsList = $this->normalizeAssetsList($assetsList, $assetsDir, $assetsUrl);

        add_action('login_head', function () use ($assetsList) {
            remove_action('login_head', 'wp_shake_js', 12);
            if (!empty($assetsList['css']))
                foreach ($assetsList['css'] as $item)
                    echo '<link rel="stylesheet" href="' . $item['url'] . '?ver=' . date('YMD-hi') . '">';
            if (!empty($assetsList['js']))
                foreach ($assetsList['js'] as $item)
                    echo '<script src="' . $item['url'] . '?ver=' . date('YMD-hi') . '"></script>';
        });

        return $this;
    }

    /**
     * This fix is mostly about wp_emoji
     *
     * @return $this
     */
    public function addAssetsDefaultFixes()
    {
        add_action('wp_enqueue_scripts', function() {
            // disable emoji
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('admin_print_scripts', 'print_emoji_detection_script');
            remove_action('wp_print_styles', 'print_emoji_styles');
            remove_action('admin_print_styles', 'print_emoji_styles');
            remove_filter('the_content_feed', 'wp_staticize_emoji');
        });
        return $this;
    }

    /**
     * @param $filePath string
     */
    public function addAssetOnEditor($filePath)
    {
        add_action('after_setup_theme', function () use ($filePath) {
            add_editor_style($filePath);
        });
    }

    /**
     * Add Default CSS & JS
     *
     * @param string $action
     * @param string $assetsDir - relative to theme's root directory with slashes, like '/static/'
     * @param array  $assetsList - [$id, $name, $deps] array. You can put here an absolute URL to skip $assetsDir prefix.
     *
     * @return $this
     */
    private function addAssets($action = '', $assetsDir = '/static/', $assetsList = [])
    {
        $assetsDir = Helper::normalizePath(get_template_directory() . $assetsDir);
        $assetsUrl = get_template_directory_uri() . $assetsDir;
        $assetsList = $this->normalizeAssetsList($assetsList, $assetsDir, $assetsUrl);

        add_action($action, function () use ($assetsList) {
            if (!empty($assetsList['css']))
                foreach ($assetsList['css'] as $item)
                    wp_enqueue_style($item['id'], $item['url'], $item['deps'], $item['ver']);
            if (!empty($assetsList['js']))
                foreach ($assetsList['js'] as $item)
                    wp_enqueue_script($item['id'], $item['url'], $item['deps'], $item['ver'], true);
        });

        return $this;
    }

    /**
     * @param array  $assetsListOriginal
     * @param string $assetsDir
     * @param string $assetsUrl
     *
     * @return array
     */
    private function normalizeAssetsList($assetsListOriginal = [], $assetsDir = '', $assetsUrl = '')
    {
        $assetsList = [
            'css' => [],
            'js'  => [],
        ];
        if (!empty($assetsListOriginal)) {
            foreach ($assetsList as $item) {
                $assetsListItem = [
                    'id'   => $item[0],
                    'url'  => strpos($item[1], 'http') !== false ? $item[1] : ($assetsUrl . $item[1]),
                    'deps' => isset($item[2]) ? $item[2] : [],
                    'ver'  => strpos($item[1], 'http') !== false ? null : Helper::getFileVersion($assetsDir . $item[1]),
                ];
                if (strpos($item[1], '.css') !== false) $assetsList['css'][] = $assetsListItem;
                else $assetsList['js'][] = $assetsListItem;
            }
        }
        return $assetsList;
    }

    /**
     * @param $actionName
     * @param $callableOrFilePath
     *
     * @return $this
     */
    public function addAction($actionName, $callableOrFilePath)
    {
        if (!is_callable($callableOrFilePath) && is_string($callableOrFilePath))
            return $this->addActionFile($actionName, $callableOrFilePath);
        elseif (is_callable($callableOrFilePath))
            add_action($actionName, $callableOrFilePath);
        return $this;
    }

    /**
     * @param string $actionName
     * @param string $filePath
     *
     * @return $this
     */
    public function addActionFile($actionName = '', $filePath = '')
    {
        $filePathNormalized = Helper::normalizePath($filePath);
        if (file_exists($filePathNormalized) && is_file($filePathNormalized))
            add_action($actionName, function () use ($filePathNormalized) {
                require($filePathNormalized);
            });
        return $this;
    }
}