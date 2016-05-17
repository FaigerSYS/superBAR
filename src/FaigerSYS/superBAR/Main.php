<?PHP
namespace FaigerSYS\superBAR;

use FaigerSYS\superBAR\ConfigUpdate;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat as CLR;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

class Main extends PluginBase {
	
	public $config, $ecoType, $noF, $popup;
	public $MONEY, $FACTION, $PP, $KD;
	public $FORMAT, $TIME_FORMAT;
	
	public function onEnable() {
		$this->getLogger()->info(CLR::GOLD . 'superBAR загружается...');
		
		@mkdir($this->getDataFolder());
		if (!file_exists($this->getDataFolder() . 'config.yml'))
			file_put_contents($this->getDataFolder() . 'config.yml', $this->getResource('config.yml'));
		$this->config = new Config($this->getDataFolder() . 'config.yml', Config::YAML);
		$tmp = new ConfigUpdate;
		$tmp->update($this);
		
		$this->FORMAT = $this->config->get('hot-format');
		$this->TIME_FORMAT = $this->config->get('time-format');
		$this->noF = $this->config->get('no-faction');
		
		if ($this->config->get('type') !== 'popup')
			$this->popup = false;
		else
			$this->popup = true;
		
		$lvl = intval($this->config->get('text-offset-level'));
		if ($lvl < 0) {
			$n1 = str_pad('', -$lvl, '  ');
			$n2 = $n1 . "\n";
			$this->FORMAT = $this->FORMAT . $n1;
			$this->FORMAT = str_replace("\n", $n2, $this->FORMAT);
		} elseif ($lvl > 0) {
			$n1 = str_pad('', $lvl, '  ');
			$n2 = "\n" . $n1;
			$this->FORMAT = $n1 . $this->FORMAT;
			$this->FORMAT = str_replace("\n", $n2, $this->FORMAT);
		}
		
		if ($this->MONEY = $this->getServer()->getPluginManager()->getPlugin('EconomyAPI')) {
			$this->ecoType = 1;
			$this->getLogger()->info(CLR::GREEN . 'EconomyAPI OK!');
		} elseif ($this->MONEY = $this->getServer()->getPluginManager()->getPlugin('PocketMoney')) {
			$this->ecoType = 2;
			$this->getLogger()->info(CLR::GREEN . 'PocketMoney OK!');
		}
		
		if ($this->FACTION = $this->getServer()->getPluginManager()->getPlugin('FactionsPro'))
			$this->getLogger()->info(CLR::GREEN . 'FactionsPro OK!');
		
		if ($this->PP = $this->getServer()->getPluginManager()->getPlugin('PurePerms'))
			$this->getLogger()->info(CLR::GREEN . 'PurePerms OK!');
		
		if ($this->KD = $this->getServer()->getPluginManager()->getPlugin('KillChat'))
			$this->getLogger()->info(CLR::GREEN . 'KillChat OK!');
		elseif ($this->KD = $this->getServer()->getPluginManager()->getPlugin('ScorePvP'))
			$this->getLogger()->info(CLR::GREEN . 'ScorePvP OK!');
		
		$ticks = $this->config->get('timer');
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new hotBAR($this), $ticks);
		$this->getLogger()->info(CLR::GOLD . 'superBAR успешно включен!');
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, $lbl, array $args){
		if($cmd->getName() == 'superbar') {
			$sender->sendMessage("§b[§esuper§6BAR§b] §aСкоро будет...))");
		}
	}
}

class hotBAR extends PluginTask {
	public function onRun($tick) {
		$time = date($this->getOwner()->TIME_FORMAT);
		$load = $this->getOwner()->getServer()->getTickUsage();
		$tps = $this->getOwner()->getServer()->getTicksPerSecond();
		$online = count($this->getOwner()->getServer()->getOnlinePlayers());
		$max_online = $this->getOwner()->getServer()->getMaxPlayers();
		
		foreach ($this->getOwner()->getServer()->getOnlinePlayers() as $player) {
			$name = $player->getName();
			
			if ($this->getOwner()->PP)
				$ppg = $this->getOwner()->PP->getUserDataMgr()->getData($player)['group'];
			else
				$ppg = '§c' . 'NoPPplug';
			
			if ($this->getOwner()->KD) {
				$kills = $this->getOwner()->KD->getKills($name);
				$deaths = $this->getOwner()->KD->getDeaths($name);
			} else {
				$kills = $deaths =  '§c' . 'NoPlug';
			}
			
			$text = str_replace(array('%NICK%', '%MONEY%', '%FACTION%', '%ITEM_ID%', '%ITEM_META%', '%TIME%', '%ONLINE%', '%MAX_ONLINE%', '%X%', '%Y%', '%Z%', '%IP%', '%PP_GROUP%', '%TAG%', '%LOAD%', '%TPS%', '%KILLS%', '%DEATHS%', '%LEVEL%'), array($name, $this->getMoney(strtolower($name)), $this->getFaction($name), $player->getInventory()->getItemInHand()->getId(), $player->getInventory()->getItemInHand()->getDamage(), $time, $online, $max_online, intval($player->x), intval($player->y), intval($player->z), $player->getAddress(), $ppg, $player->getNameTag(), $load, $tps, $kills, $deaths, $player->getLevel()->getName()), $this->getOwner()->FORMAT);
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
			return '§c' . 'NoEcoPlug';
	}
	
	public function getFaction($player) {
		if ($this->getOwner()->FACTION) {
			$f = $this->getOwner()->FACTION->getPlayerFaction($player);
			if (count($f) == 0)
				$f = $this->getOwner()->noF;
			return $f;
		} else {
			return '§c' . 'NoFactPlug';
		}
	}
}
