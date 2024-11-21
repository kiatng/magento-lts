<?php

class Kiatng_Ham_SsController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $modules = array_keys(Mage::getConfig()->getNode('modules')->asArray()); // 88 elements
        $t0 = microtime(true);

        foreach ($modules as $moduleName) {
            $result[$moduleName] = (string) Mage::getConfig()->getNode('modules')->$moduleName->active !== 'true';
        }
        $dt = microtime(true) - $t0;
        $configCacheStatus = Mage::app()->useCache('config') ? 'cache enabled' : 'cache disabled';
        Mage::helper('shooter')->echo($result, $configCacheStatus, $dt);
    }
}