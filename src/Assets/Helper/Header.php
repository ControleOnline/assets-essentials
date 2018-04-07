<?php

namespace Assets\Helper;

use Core\DiscoveryRoute;
use Core\Helper\Format;

class Header {

    /**
     * @var \Zend\View\Renderer\RendererInterface
     */
    protected static $renderer;
    protected static $routes;
    protected static $basepath;
    protected static $vendorBasepath;
    protected static $publicVendorBasepath = '/vendor/';
    protected static $requireJsFiles = [];
    protected static $requireJsLibs = [];
    protected static $requireJsDependencies = [];
    protected static $jsLibsCacheFile;
    protected static $default_route;
    protected static $uri;
    protected static $public_path = DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR;

    public static function init(\Zend\View\Renderer\RendererInterface $renderer, $default_route, $uri) {
        if (!self::$basepath) {
            self::$renderer = $renderer;
            self::$default_route = $default_route;
            self::$uri = $uri;
            $DiscoveryRoute = new DiscoveryRoute(self::$default_route);
            $DiscoveryRoute->setUrl(self::$uri);
            $routes = $DiscoveryRoute->getRoute();
            $c = explode('\\', $routes['controller']);
            $controller = strtolower(end($c));
            self::$routes['module'] = strtolower($routes['module']);
            self::$routes['controller'] = substr($controller, -10) == 'controller' ? substr($controller, 0, -10) : $controller;
            self::$routes['action'] = Format::camelCaseDecode($routes['action']);
            self::$vendorBasepath = Format::getModulePath(self::$routes['module']);
            self::$basepath = getcwd() . DIRECTORY_SEPARATOR . 'public';
            self::$jsLibsCacheFile = './data/cache/' . self::getSystemVersion() . '/js.cache.json';
            is_dir(dirname(self::$jsLibsCacheFile)) ? : @mkdir(dirname(self::$jsLibsCacheFile), 0777, true);
        }
    }

    protected static function addJs($src, $type = 'text/javascript', $attrs = array()) {
        self::$renderer->headScript()->setAllowArbitraryAttributes(true)->appendFile(self::$renderer->basePath($src . '?v=' . self::getSystemVersion()), $type, $attrs);
    }

    protected static function addJsLib($src, $type = 'text/javascript', $attrs = array()) {
        $attrs['data-type'] = 'lib';
        self::$renderer->headScript()->setAllowArbitraryAttributes(true)->appendFile(self::$renderer->basePath($src . '?v=' . self::getSystemVersion()), $type, $attrs);
    }

    public static function addCssLib($href, $media = 'screen', $conditionalStylesheet = '', $extras = array()) {
        $extras['data-type'] = 'lib';
        self::$renderer->headLink()->appendStylesheet(self::$renderer->basePath($href . '?v=' . self::getSystemVersion()), $media, $conditionalStylesheet, $extras);
    }

    protected static function addCss($href, $media = 'screen', $conditionalStylesheet = '', $extras = array()) {
        self::$renderer->headLink()->appendStylesheet(self::$renderer->basePath($href . '?v=' . self::getSystemVersion()), $media, $conditionalStylesheet, $extras);
    }

    protected static function addDefaultLibs() {
        self::addJsLib(self::$publicVendorBasepath . 'requirejs/require.js', 'text/javascript', array(
            'data-main' => '/assets/js/Core.js?v=' . self::getSystemVersion(),
            'system-version' => self::getSystemVersion()
        ));
        self::addJsLibs('dataTables-bootstrap', 'datatables/media/js/dataTables.bootstrap.min.js');
        self::addJsLibs('datatables.net', 'datatables/media/js/jquery.dataTables.min.js');
        self::addCssLib('/vendor/bootstrap/dist/css/bootstrap.min.css');
        self::addCssLib('/assets/css/Core.css');
        self::addCssLib('/vendor/fontawesome/css/font-awesome.min.css');
        self::addCssLib('/assets/css/core/Application.css');
    }

    protected static function writeJsLibCache() {
        if (!is_file(self::$jsLibsCacheFile)) {
            $bower_config = self::getBowerConfig();
            if ($bower_config->dependencies) {
                $bower_dependencies = array_keys((array) $bower_config->dependencies);
                self::addJsDependency($bower_dependencies);
            }
            file_put_contents(self::$jsLibsCacheFile, json_encode(self::$requireJsDependencies));
        }
    }

    protected static function addJsDependency($bower_dependencies) {
        foreach ($bower_dependencies AS $dependency) {
            $b_c = self::getBowerConfig('./public' . self::$publicVendorBasepath . $dependency);
            if (isset($b_c->main) && $b_c->main) {
                $filter = array_values(array_filter(is_array($b_c->main) ? $b_c->main : array($b_c->main), function ($var) use ($b_c) {
                            $file = './public' . self::$publicVendorBasepath . $b_c->name . DIRECTORY_SEPARATOR . $var;
                            if (is_file($file) && stripos($var, '.js') !== false) {
                                return true;
                            } elseif (is_file($file . '.js') && stripos($var, '.js') === false) {
                                return true;
                            }
                            return false;
                        }));
            }
            if ($filter) {
                self::$requireJsDependencies[$b_c->name] = $b_c->name . DIRECTORY_SEPARATOR . $filter[0];
            }

            $bd = array_keys(isset($b_c->dependencies) ? (array) $b_c->dependencies : array());
            self::addJsDependency($bd);
        }
    }

    protected static function getBowerConfig($path = '.') {
        return json_decode(file_get_contents($path . '/bower.json'));
    }

    public static function addJsLibs($key, $src) {
        self::$requireJsDependencies[$key] = $src;
    }

    protected static function addDefaultHeaderFiles() {
        self::addRequireJsFile(DIRECTORY_SEPARATOR . 'core/application', 'application');
        self::addRequireJsFile(DIRECTORY_SEPARATOR . self::$routes['module'], self::$routes['module']);
        self::addRequireJsFile(DIRECTORY_SEPARATOR . self::$routes['module'] . DIRECTORY_SEPARATOR . self::$routes['controller'], self::$routes['module'] . '-' . self::$routes['controller']);
        self::addRequireJsFile(DIRECTORY_SEPARATOR . self::$routes['module'] . DIRECTORY_SEPARATOR . self::$routes['controller'] . DIRECTORY_SEPARATOR . self::$routes['action'], self::$routes['module'] . '-' . self::$routes['controller'] . '-' . self::$routes['action']);
        self::addCssFile(DIRECTORY_SEPARATOR . self::$routes['module'] . '.css');
        self::addCssFile(DIRECTORY_SEPARATOR . self::$routes['module'] . DIRECTORY_SEPARATOR . self::$routes['controller'] . '.css');
        self::addCssFile(DIRECTORY_SEPARATOR . self::$routes['module'] . DIRECTORY_SEPARATOR . self::$routes['controller'] . DIRECTORY_SEPARATOR . self::$routes['action'] . '.css');
    }

    protected static function addJsFile($src) {
        $path = DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'modules';
        if (is_file(self::$vendorBasepath . $path . $src)) {
            self::addJs(self::$renderer, $path . $src . '?v=' . self::getSystemVersion());
        }
    }

    public static function addArbitraryRequireJsFile($src, $name) {
        self::$requireJsFiles[$name] = self::$public_path . 'js' . $src;
    }

    protected static function addRequireJsFile($src, $name) {

        $vendorFile = self::$vendorBasepath . self::$public_path . 'js' . $src;
        $file = self::$basepath . self::$public_path . 'js' . $src;
                
        if (is_file($vendorFile . '.js') || (is_file($file . '.js'))) {            
            self::$requireJsFiles[$name] = self::$public_path . 'js' . $src;
        }
    }

    public static function requireJsLibs() {
        self::writeJsLibCache();
        self::$requireJsLibs = json_decode(file_get_contents(self::$jsLibsCacheFile));
        return self::$requireJsLibs;
    }

    public static function getRequireJsFiles() {
        self::addDefaultLibs();
        self::addDefaultHeaderFiles();
        return self::$requireJsFiles;
    }

    protected static function addCssFile($href) {
        $vendorFile = self::$vendorBasepath . self::$public_path . 'css' . $href;
        $file = self::$basepath . self::$public_path . 'css' . $href;
        if (is_file($vendorFile) || (is_file($file))) {
            self::addCss(self::$public_path . 'css' . $href);
        }
    }

    public static function getSystemVersion() {
        if (is_file(getcwd() . '.version')) {
            $contents = file_get_contents(getcwd() . '.version');
            $version = $contents ? trim(array_shift(array_values(preg_split('/\r\n|\r|\n/', $contents, 2)))) : false;
        }
        return isset($version) && $version ? $version : date('Y-m-d-H-m-i');
    }

}
