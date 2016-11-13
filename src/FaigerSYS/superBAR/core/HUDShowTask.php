<?php
namespace FaigerSYS\superBAR\core;

use pocketmine\scheduler\PluginTask;

class HUDShowTask extends PluginTask {
	
	private $HUD;
	
	public function __construct($plugin, $HUD) {
		$this->HUD = $HUD;
		parent::__construct($plugin);
	}
	
	public function onRun($tick) {
		$this->HUD->processHUD($this->getOwner()->getServer());
	}
	
}
