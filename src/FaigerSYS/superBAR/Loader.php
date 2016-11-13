<?php
namespace FaigerSYS\superBAR;

use pocketmine\utils\TextFormat as CLR;

use FaigerSYS\superBAR\core\HUD;
use FaigerSYS\superBAR\core\HUDShowTask;

use FaigerSYS\superBAR\controller\DataController;
use FaigerSYS\superBAR\controller\EventController;

use FaigerSYS\superBAR\provider\ConfigProvider;
use FaigerSYS\superBAR\provider\AddonProvider;

class Loader extends BaseModule {
	
	const PLUGINS = [['EconomyAPI', 'PocketMoney'], 'FactionsPro', 'GameTime', 'PurePerms', ['KillChat', 'ScorePvP']];
	
	/** @var \FaigerSYS\superBAR\controller\DataController */
	private $data = null;
	
	/** @var int */
	private $taskCache = null;
	
	public function onEnable() {
		$plugin = $this->getPlugin();
		$plugin->sendLog(CLR::GOLD . 'superBAR loading...');
		
		@mkdir($plugin->getDataFolder());
		
		if (!($this->data instanceof DataController))
			$this->data = new DataController();
		
		if (!(($events = $this->getData()->getEventController()) instanceof EventController))
			$this->getData()->setEventController($events = new EventController());
		$plugin->getServer()->getPluginManager()->registerEvents($events, $plugin);
		
		$this->loadAll();
		
		$plugin->sendLog(CLR::GOLD . 'superBAR by FaigerSYS enabled!');
	}
	
	private function getSettings() {
		if (!(($config = $this->getData()->getConfigProvider()) instanceof ConfigProvider))
			$this->getData()->setConfigProvider($config = new ConfigProvider($this->getAnotherPlugin('PurePerms')));
		else
			$config->reloadData();
		
		$settings = $config->getFormatedData();
		return $settings;
	}
	
	private function getPlugins($quiet = false) {
		$plugin = $this->getPlugin();
		$plugins = [];
		
		foreach (Loader::PLUGINS as $names) {
			if (is_array($names)) {
				foreach ($names as $name) {
					if ($plugins[$name] = $this->getAnotherPlugin($name)) {
						!$quiet ? $plugin->sendLog(CLR::GREEN . $name . ' OK!') : false;
						break;
					}
				}
			} else {
				if ($plugins[$names] = $this->getAnotherPlugin($names))
					!$quiet ? $plugin->sendLog(CLR::GREEN . $names . ' OK!') : false;
			}
		}
		
		return $plugins;
	}
	
	private function getAddons() {
		if (!(($addons = $this->getData()->getAddonProvider()) instanceof AddonProvider))
			$this->getData()->setAddonProvider($addons = new AddonProvider());
		else
			$addons->reloadAddons();
		
		return $addons;
	}
	
	public function loadAll($reload = false) {
		$settings = $this->getSettings();
		
		$this->getPlugin()->setTimezone($settings['timezone']);
		$this->getData()->setDefaultEnabled($settings['default-enabled']);
		$timer = $settings['timer'];
		
		unset($settings['timezone']);
		unset($settings['default-enabled']);
		unset($settings['timer']);
		
		$plugins = $this->getPlugins($reload);
		$addons = $this->getAddons();
		
		if (!(($HUD = $this->getData()->getHUD()) instanceof HUD))
			$this->getData()->setHUD($HUD = new HUD($settings, $plugins, $addons));
		else
			$HUD->setData($settings, $plugins, $addons);
		
		$reload ? $this->getPlugin()->getServer()->getScheduler()->cancelTask($this->taskCache) : false;
		$this->taskCache = $this->getPlugin()->getServer()->getScheduler()->scheduleRepeatingTask(new HUDShowTask($this->getPlugin(), $HUD), $timer)->getTaskId();
	}
	
	public function getData() {
		return $this->data;
	}
	
	private function getAnotherPlugin($name) {
		if ($plugin = $this->getPlugin()->getServer()->getPluginManager()->getPlugin($name)) {
			if ($plugin->isEnabled()) return $plugin;
		}
		return false;
	}
	
	public function onDisable() {
		$this->getPlugin()->getServer()->getScheduler()->cancelTask($this->taskCache);
	}
	
}
