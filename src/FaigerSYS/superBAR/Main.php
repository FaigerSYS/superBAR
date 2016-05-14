<?PHP
namespace FaigerSYS\superBAR;

use FaigerSYS\superBAR\ConfigUpdate;
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
	public $PP;
	public $KD;
	
	public $FORMAT;
	public $TIME_FORMAT;
	
	public function onEnable() {
		$this->getLogger()->info(CLR::GOLD . "superBAR loading...");
		
		@mkdir($this->getDataFolder());
		if (!file_exists($this->getDataFolder() . "config.yml"))
			file_put_contents($this->getDataFolder() . "config.yml", $this->getResource("config.yml"));
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$tmp = new ConfigUpdate;
		$tmp->update($this);
		
		$this->FORMAT = $this->config->get("hot-format");
		$this->TIME_FORMAT = $this->config->get("time-format");
		$this->noF = $this->config->get("no-faction");
		
		if ($this->config->get("type") !== "popup")
			$this->popup = false;
		else
			$this->popup = true;
		
		$ticks = preg_replace("/[^0-9]/", '', $this->config->get("timer"));
		
		$lvl = intval($this->config->get("text-offset-level"));
		if ($lvl < 0) {
			$n1 = str_pad("", -$lvl, "  ");
			$n2 = $n1 . "\n";
			$this->FORMAT = $this->FORMAT . $n1;
			$this->FORMAT = str_replace("\n", $n2, $this->FORMAT);
		} elseif ($lvl > 0) {
			$n1 = str_pad("", $lvl, "  ");
			$n2 = "\n" . $n1;
			$this->FORMAT = $n1 . $this->FORMAT;
			$this->FORMAT = str_replace("\n", $n2, $this->FORMAT);
		}
		
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
		
		if ($this->getServer()->getPluginManager()->getPlugin("PurePerms")) {
			$this->PP = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
			$this->getLogger()->info(CLR::GREEN . "PurePerms OK!");
		}
		
		if ($this->getServer()->getPluginManager()->getPlugin("KillChat")) {
			$this->KD = $this->getServer()->getPluginManager()->getPlugin("KillChat");
			$this->getLogger()->info(CLR::GREEN . "KillChat OK!");
		} elseif ($this->getServer()->getPluginManager()->getPlugin("ScorePvP")) {
			$this->KD = $this->getServer()->getPluginManager()->getPlugin("ScorePvP");
			$this->getLogger()->info(CLR::GREEN . "ScorePvP OK!");
		}
		
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new hotBAR($this), $ticks);
		$this->getLogger()->info(CLR::GOLD . "superBAR by FaigerSYS enabled!");
	}
}

class hotBAR extends PluginTask {
	public function onRun($tick) {
		foreach ($this->getOwner()->getServer()->getOnlinePlayers() as $player) {
			$name = $player->getName();
			$ip = $player->getAddress();
			$tag = $player->getNameTag();
			
			if ($this->getOwner()->PP)
				$ppg = $this->PP->getUserDataMgr()->getGroup($this->PP->getPlayer($name));
			else
				$ppg = "§c" . "NoPPplug";
			
			if ($this->getOwner()->KD) {
				$kills = $this->getOwner()->KD->getKills($name);
				$deaths = $this->getOwner()->KD->getDeaths($name);
			} else {
				$kills = $deaths =  "§c" . "NoPlug";
			}
			
			$money = $this->getMoney(strtolower($name));
			$faction = $this->getFaction($name);
			
			$x = intval($player->x);
			$y = intval($player->y);
			$z = intval($player->z);
			$level = $player->getLevel()->getName();
			
			$item_id = $player->getItemInHand()->getId();
			$item_meta = $player->getItemInHand()->getDamage();
			
			$time = date($this->getOwner()->TIME_FORMAT);
			$load = $this->getOwner()->getServer()->getTickUsage();
			$tps = $this->getOwner()->getServer()->getTicksPerSecond();
			
			$online = count($this->getOwner()->getServer()->getOnlinePlayers());
			$max_online = $this->getOwner()->getServer()->getMaxPlayers();
			
			$text = str_replace(array("%NICK%", "%MONEY%", "%FACTION%", "%ITEM_ID%", "%ITEM_META%", "%TIME%", "%ONLINE%", "%MAX_ONLINE%", "%X%", "%Y%", "%Z%", "%IP%", "%PP_GROUP%", "%TAG%", "%LOAD%", "%TPS%", "%KILLS%", "%DEATHS%", "%LEVEL%"), array($name, $money, $faction, $item_id, $item_meta, $time, $online, $max_online, $x, $y, $z, $ip, $ppg, $tag, $load, $tps, $kills, $deaths, $level), $this->getOwner()->FORMAT);
			
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
	
	public function getFaction($player) {
		if ($this->getOwner()->FACTION) {
			$f = $this->getOwner()->FACTION->getPlayerFaction($player);
			if (count($f) == 0)
				$f = $this->getOwner()->noF;
			return $f;
		} else {
			return "§c" . "NoFactPlug";
		}
	}
}
