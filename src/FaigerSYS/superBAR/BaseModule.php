<?php
namespace FaigerSYS\superBAR;

abstract class BaseModule {
	
	private static $plugin = null;
	
	protected function getPlugin() {
		return BaseModule::$plugin;
	}
	
	public static function setPlugin(superBAR $plugin) {
		BaseModule::$plugin = $plugin;
	}
	
}
