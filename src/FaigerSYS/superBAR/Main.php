<?PHP
namespace FaigerSYS\superBAR;

use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat as CLR;

class Main extends PluginBase {
	
	public $config;
	public $ecoType;
	public $noF;
	public $popup;
	
	public $MONEY;
	public $FACTION;
	
	public $FORMAT;
	public $TIME_FORMAT;
	
	public function onEnable() {
		$this->getLogger()->info(CLR::GOLD . "superBAR loading...");
		
		@mkdir($this->getDataFolder());
		if (!file_exists($this->getDataFolder() . "config.yml"))
			file_put_contents($this->getDataFolder() . "config.yml", $this->getResource("config.yml"));
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		
		$this->FORMAT = $this->config->get("format");
		$this->TIME_FORMAT = $this->config->get("time-format");
		$this->noF = $this->config->get("no-faction");
		if ($this->config->get("type") == "popup" || $this->config->get("type") == null)
			$this->popup = true;
		
		$ticks = preg_replace("/[^0-9]/", '', $this->config->get("timer"));
		
		if ($this->getServer()->getPluginManager()->getPlugin("EconomyAPI")) {
			$this->MONEY = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
			$this->ecoType = 1;
			$this->getLogger()->info(CLR::GREEN . "EconomyAPI OK!");
		} elseif ($this->getServer()->getPluginManager()->getPlugin("PocketMoney")) {
			$this->MONEY = $this->getServer()->getPluginManager()->getPlugin("PocketMoney");
			$this->ecoType = 2;
			$this->getLogger()->info(CLR::GREEN . "PocketMoney OK!");
		}
		
		if ($this->getServer()->getPluginManager()->getPlugin("FactionsPro")) {
			$this->FACTION = $this->getServer()->getPluginManager()->getPlugin("FactionsPro");
			$this->getLogger()->info(CLR::GREEN . "FactionsPro OK!");
		}
		
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new hotBAR($this), $ticks);
		$this->getLogger()->info(CLR::GOLD . "superBAR by FaigerSYS enabled!");
	}
}

class hotBAR extends PluginTask {
	public function onRun($tick) {
		foreach ($this->getOwner()->getServer()->getOnlinePlayers() as $player) {
			$name = $player->getName();
			$faction = "§c" . "NoFactPlug";
			
			$x = intval($player->x);
			$y = intval($player->y);
			$z = intval($player->z);
			
			if ($this->getOwner()->MONEY)
				$money = $this->getMoney(strtolower($name));
			if ($this->getOwner()->FACTION) {
				$faction = $this->getOwner()->FACTION->getPlayerFaction($name);
				if (count($faction) == 0)
					$faction = $this->getOwner()->noF;
			}
			
			$item = $player->getItemInHand()->getId() . ":" . $player->getItemInHand()->getDamage();
			$time = date($this->getOwner()->TIME_FORMAT);
			
			$online = count($this->getOwner()->getServer()->getOnlinePlayers());
			$max_online = $this->getOwner()->getServer()->getMaxPlayers();
			
			$text = str_replace(array("%NICK%", "%MONEY%", "%FACTION%", "%ITEM%", "%TIME%", "%ONLINE%", "%MAX_ONLINE%", "%X%", "%Y%", "%Z%"), array($name, @$money, @$faction, $item, $time, $online, $max_online, $x, $y, $z), $this->getOwner()->FORMAT);
			
			if ($this->getOwner()->popup)
				$player->sendPopup($text);
			else
				$player->sendTip($text);
		}
	}
	
	public function getMoney($player) {
		if ($this->getOwner()->ecoType == 1)
			return $this->getOwner()->MONEY->myMoney($player);
		elseif ($this->getOwner()->ecoType == 2)
			return $this->getOwner()->MONEY->getMoney($player);
		else
			return "§c" . "NoEcoPlug";
	}
}
