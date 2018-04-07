<?php

namespace Assets\Controller;

use Core\Controller\AbstractController;
use Core\Helper\Format;

class DefaultController extends AbstractController {

    public function jsAction() {
        $url = Format::removeUrlParameters($this->getRequest()->getRequestUri());
        $module = $this->getModuleFromUrl($url);
        $module_path = Format::getModulePath($module);
        if (is_file($module_path . $url)) {
            header("Content-Type: application/javascript");
            echo file_get_contents($module_path . $url);
        }
        exit;
    }

    public function configAction() {
        exit;
    }

    public function imgAction() {
        $url = Format::removeUrlParameters($this->getRequest()->getRequestUri());
        $module = $this->getModuleFromUrl($url);
        $module_path = Format::getModulePath($module);
        if (is_file($module_path . $url)) {
            header("Content-Type: " . image_type_to_mime_type(exif_imagetype($module_path . $url)));
            echo file_get_contents($module_path . $url);
        }
        exit;
    }

    public function cssAction() {

        $url = Format::removeUrlParameters($this->getRequest()->getRequestUri());
        $module = $this->getModuleFromUrl($url);
        $module_path = Format::getModulePath($module);
        if (is_file($module_path . $url)) {
            header("Content-Type: text/css");
            header("X-Content-Type-Options: nosniff");
            echo file_get_contents($module_path . $url);
        }
        exit;
    }

    public function getModuleFromUrl($url) {
        return pathinfo(explode('/', $url)[3], PATHINFO_FILENAME);
    }

}
