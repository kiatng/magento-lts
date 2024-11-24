<?php

class Kiatng_Ham_SsController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $t0 = microtime(true);
        $result = Mage::helper('adminhtml/dashboard_data')->countStores();
        $dt = microtime(true) - $t0;
        $configCacheStatus = Mage::app()->useCache('config') ? 'cache enabled' : 'cache disabled';
        Mage::helper('shooter')->echo($result, $configCacheStatus, $dt);
    }
}