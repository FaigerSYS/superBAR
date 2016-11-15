<?php
namespace FaigerSYS\superBAR\core;

use pocketmine\Server;
use pocketmine\utils\TextFormat as CLR;

use FaigerSYS\superBAR\provider\AddonProvider;

class HUD {
	
	const NO_PLUG = CLR::RED . 'NoPlugin' . CLR::RESET;
	const FORMAT_TREE = ['%NICK%', '%MONEY%', '%FACTION%', '%ITEM_ID%', '%ITEM_META%', '%TIME%', '%ONLINE%', '%MAX_ONLINE%', '%X%', '%Y%', '%Z%', '%IP%', '%PP_GROUP%', '%TAG%', '%LOAD%', '%TPS%', '%KILLS%', '%DEATHS%', '%LEVEL%', '%GT%', '%AGT%'];
	
	/** @var \pocketmine\plugin\Plugin[] */
	private $plugins = [];
	
	/** @var array */
	private $settings = [];
	
	/** @var \FaigerSYS\superBAR\provider\AddonProvider */
	private $addons = null;
	
	/** @var array */
	private $displayTo = [];
	
	public function __construct($settings, $plugins, $addons) {
		$this->setData($settings, $plugins, $addons);
	}
	
	public function processHUD(Server $server) {
		$faction = $pp_group = $money = $kills = $deaths = $session_time = $all_time = HUD::NO_PLUG;
		$load = $server->getTickUsage();
		$tps = $server->getTicksPerSecond();
		$playersOnline = count($players = $server->getOnlinePlayers());
		$maxOnline = $server->getMaxPlayers();
		
		$displayTo = $this->displayTo;
		$plugins = $this->plugins;
		$settings = $this->settings;
		$addons = $this->addons;
		
		$ftree = HUD::FORMAT_TREE;
		$data = $settings['default'];
		
		foreach ($players as $p) {
			$name = $p->getName();
			if (isset($displayTo[$name])) {
				if ($plugins['PurePerms']) {
					if (((float) $plugins['PurePerms']->getDescription()->getVersion()) < 1.2)
						$pp_group = $plugins['PurePerms']->getUser($p)->getGroup()->getName();
					else
						$pp_group = $plugins['PurePerms']->getUserDataMgr()->getData($p)['group'];
					
					if (isset($settings['pp'][$pp_group]))
						$data = $settings['pp'][$pp_group];
					else
						$data = $settings['default'];
				}
				
				$format = $data['format'];
				list($showHUD, $input, $output) = $addons->getFormatedAddons($p);
				if (!$showHUD) {
					$data['tip'] ? $p->sendTip($input) : $p->sendPopup($input);
					continue;
				} else {
					$format = str_replace($input, $output, $format);
				}
				
				if ($plugins['FactionsPro']) {
					if (((float) $plugins['FactionsPro']->getDescription()->getVersion()) < 1.5)
						$faction = $plugins['FactionsPro']->getPlayerFaction($name);
					else
						$faction = $plugins['FactionsPro']->getSessionFromName($name)->getFactionName();
					if (strlen($fact) <= 0)
						$faction = $settings['no-faction'];
				}
				
				if ($plugins['EconomyAPI'])
					$money = $plugins['EconomyAPI']->myMoney($name);
				elseif ($plugins['PocketMoney'])
					$money = $plugins['PocketMoney']->getMoney($name);
				
				if (($tmp = $plugins['KillChat']) || ($tmp = $plugins['ScorePvP'])) {
					$kills = $tmp->getKills($name);
					$deaths = $tmp->getDeaths($name);
				}
				
				if ($plugins['GameTime']) {
					$session_time = $plugins['GameTime']->getSessionTime($name, '%i%:%s%');
					$all_time = $plugins['GameTime']->getAllTime($name, '%H%:%i%:%s%');
				}
				
				if (($inv = $p->getInventory()) !== null) {
					$item = $inv->getItemInHand();
					$id = $item->getId();
					$meta = $item->getDamage();
				} else {
					$id = $meta = 0;
				}
				
				$date = date($data['time-format']);
				
				$x = floor($p->getX());
				$y = floor($p->getY());
				$z = floor($p->getZ());
				
				$ip = $p->getAddress();
				$tag = $p->getNameTag();
				$level = $p->getLevel()->getName();
				
				$replace = [$name, $money, $faction, $id, $meta, $date, $playersOnline, $maxOnline, $x, $y, $z, $ip, $pp_group, $tag, $load, $tps, $kills, $deaths, $level, $session_time, $all_time];
				
				$text = str_replace($ftree, $replace, $format);
				
				$data['tip'] ? $p->sendTip($text) : $p->sendPopup($text);
			}
		}
	}
	
	public function optimizeHUD() {
		$plugins = $this->plugins;
		$data = $this->settings['default'];
		$format = $data['format'];
		
		if (strpos($format, '%MONEY%') === false)
			$plugins['EconomyAPI'] = $plugins['PocketMoney'] = false;
		
		if (strpos($format, '%FACTION%') === false)
			$plugins['FactionsPro'] = false;
		
		if (strpos($format, '%KILLS%') === false || strpos($format, '%DEATHS%') === false)
			$plugins['ScorePvP'] = $plugins['KillChat'] = false;
		
		if (strpos($format, '%GT%') === false || strpos($format, '%AGT%') === false)
			$plugins['GameTime'] = false;
		
		$pp_data = $this->settings['pp'];
		$ppInUse = false;
		if (is_array($pp_data)) {
			foreach ($pp_data as $group) {
				foreach ($group as $key => $value) {
					if ($value !== $data[$key]) {
						$ppInUse = true;
						break;
					}
				}
			}
		}
		if (!$ppInUse && strpos($format, '%PP_GROUP%') === false)
			$plugins['PurePerms'] = false;
		
		$this->plugins = $plugins;
	}
	
	public function setDisplay($id, $display = true) {
		if ($display)
			$this->displayTo[$id] = true;
		else
			unset($this->displayTo[$id]);
	}
	
	public function setData($settings = false, $plugins = false, $addons = false) {
		is_array($settings) ? $this->settings = $settings : false;
		is_array($plugins) ? $this->plugins = $plugins : false;
		($addons instanceof AddonProvider) ? $this->addons = $addons : false;
		
		$this->optimizeHUD();
	}
}
