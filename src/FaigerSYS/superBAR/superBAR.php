<?php
namespace FaigerSYS\superBAR;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\TextFormat as CLR;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use FaigerSYS\superBAR\controller\CommandController;

class superBAR extends PluginBase {
	
	const PREFIX = CLR::AQUA . '[' . CLR::YELLOW . 'super' . CLR::GOLD . 'BAR' . CLR::AQUA . '] ' . CLR::GRAY;
	const NO_PERM = CLR::RED . 'You don\'t have permission to use this command';
	
	/** @var \FaigerSYS\superBAR\Loader */
	private static $loader = null;
	
	public function onEnable() {
		$this->getLogger()->info(CLR::GOLD . 'superBAR will be enabled after the complete server load');
		
		BaseModule::setPlugin($this);
		!(superBAR::$loader instanceof Loader) ? superBAR::$loader = new Loader() : false;
		$this->getServer()->getScheduler()->scheduleDelayedTask(new LoaderTask($this, superBAR::$loader), 0);
	}
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		CommandController::executeCommand($this, $sender, $args);
	}
	
	/**
	 * Reload settings
	 */
	public function reloadSettings() {
		superBAR::$loader->loadAll(true);
	}
	
	/**
	 * Get config provider
	 * @return \FaigerSYS\superBAR\provider\ConfigProvider
	 */
	public function getConfigProvider() {
		return superBAR::$loader->getData()->getConfigProvider();
	}
	
	/**
	 * Get settings descriprion
	 * @return array
	 */
	public function getSettingsDescription() {
		return $this->getConfigProvider()->getSettingsDescription();
	}
	
	/**
	 * Get HUD core
	 * @return \FaigerSYS\superBAR\core\HUD
	 */
	public function getHUD() {
		return superBAR::$loader->getData()->getHUD();
	}
	
	/**
	 * Change timezone
	 * @return bool
	 */
	public function setTimezone($timezone = false) {
		return ($timezone ? @date_default_timezone_set($timezone) : false);
	}
	
	/**
	 * Enable HUD for default
	 * @param bool $state
	 */
	public function setDefaultEnabled(bool $state) {
		superBAR::$loader->getData()->setDefaultEnabled($state);
	}
	
	/**
	 * Is HUD enabled for default
	 * @return bool
	 */
	public function isDefaultEnabled() {
		return superBAR::$loader->getData()->isDefaultEnabled();
	}
	
	public function hasPermission(CommandSender $object, string $permission) {
		return ($object->hasPermission('superbar') || $object->hasPermission('superbar.' . $permission));
	}
	
	public function sendLog(string $text) {
		return $this->getServer()->getLogger()->info(superBAR::PREFIX . $text);
	}
	
	public function onDisable() {
		superBAR::$loader->onDisable();
	}
	
}
