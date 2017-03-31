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
            echo file_get_contents($module_path . $url);
        }
        exit;
    }
    
    public function configAction() {        

        
        exit;
    }

    public function cssAction() {
        $url = Format::removeUrlParameters($this->getRequest()->getRequestUri());
        $module = $this->getModuleFromUrl($url);
        $module_path = Format::getModulePath($module);
        if (is_file($module_path . $url)) {
            echo file_get_contents($module_path . $url);
        }
        exit;
    }

    public function getModuleFromUrl($url) {
        return explode('/', $url)[3];
    }

}
