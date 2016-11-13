<?php
namespace FaigerSYS\superBAR\controller;

use FaigerSYS\superBAR\core\HUD;
use FaigerSYS\superBAR\provider\ConfigProvider;
use FaigerSYS\superBAR\provider\AddonProvider;

class DataController {
	
	/** @var \FaigerSYS\superBAR\core\HUD */
	private $HUD = null;
	
	/** @var \FaigerSYS\superBAR\provider\ConfigProvider */
	private $configProvider = null;
	
	/** @var \FaigerSYS\superBAR\provider\AddonProvider */
	private $addonProvider = null;
	
	/** @var \FaigerSYS\superBAR\controller\EventController */
	private $eventController = null;
	
	/** @var bool */
	private $defaultEnabled = true;
	
	public function setHUD(HUD $HUD) {
		$this->HUD = $HUD;
	}
	
	public function getHUD() {
		return $this->HUD;
	}
	
	public function setConfigProvider(ConfigProvider $provider) {
		$this->configProvider = $provider;
	}
	
	public function getConfigProvider() {
		return $this->configProvider;
	}
	
	public function setAddonProvider(AddonProvider $provider) {
		$this->addonProvider = $provider;
	}
	
	public function getAddonProvider() {
		return $this->addonProvider;
	}
	
	public function setEventController(EventController $controller) {
		$this->eventController = $controller;
	}
	
	public function getEventController() {
		return $this->eventController;
	}
	
	public function setDefaultEnabled(bool $state) {
		$this->defaultEnabled = $state;
	}
	
	public function isDefaultEnabled() {
		return $this->defaultEnabled;
	}
	
}
