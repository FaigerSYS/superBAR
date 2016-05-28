<?PHP
namespace FaigerSYS\superBAR;

use pocketmine\scheduler\PluginTask;
use FaigerSYS\superBAR\ConfigProvider;
use FaigerSYS\superBAR\hotBAR;
use pocketmine\utils\TextFormat as CLR;

class PL extends PluginTask {
	public $main, $id;
	
	public function onRun($tick) {
		$main = $this->main;
		
		$main->getLogger()->info(CLR::GOLD . 'superBAR loading...');
		
		$mgr = $main->getServer()->getPluginManager();
		$main->prefix = CLR::AQUA . '[' . CLR::YELLOW . 'super' . CLR::GOLD . 'BAR' . CLR::AQUA . '] ' . CLR::GRAY;
		$main->no_perm = CLR::RED . "You don't have permission to use this command...";
		
		@mkdir($main->getDataFolder());
		if (!file_exists($main->getDataFolder() . 'config.yml'))
			file_put_contents($main->getDataFolder() . 'config.yml', $main->getResource('config.yml'));
		
		$main->conf_provider = new ConfigProvider;
		$main->conf_provider->main = $main;
		
		$main->hotbar = new hotBAR($main);
		$main->hotbar->serv = $main->getServer();
		
		if ($main->hotbar->CASH = $main->getPlug('EconomyAPI')) {
			$main->hotbar->eT = 1;
			$main->getLogger()->info(CLR::GREEN . 'EconomyAPI OK!');
		} elseif ($main->hotbar->CASH = $main->getPlug('PocketMoney')) {
			$main->hotbar->eT = 2;
			$main->getLogger()->info(CLR::GREEN . 'PocketMoney OK!');
		}
		
		if ($main->hotbar->FACT = $main->getPlug('FactionsPro'))
			$main->getLogger()->info(CLR::GREEN . 'FactionsPro OK!');
		
		if ($main->hotbar->PP = $main->getPlug('PurePerms')) {
			$main->hotbar->PP_v = substr($main->hotbar->PP->getDescription()->getVersion(), 0, 3);
			$main->getLogger()->info(CLR::GREEN . 'PurePerms OK!');
		}
		
		if ($main->hotbar->KD = $main->getPlug('KillChat'))
			$main->getLogger()->info(CLR::GREEN . 'KillChat OK!');
		elseif ($main->hotbar->KD = $main->getPlug('ScorePvP'))
			$main->getLogger()->info(CLR::GREEN . 'ScorePvP OK!');
		
		$main->dataLoader();
		
		$main->getLogger()->info(CLR::GOLD . 'superBAR by FaigerSYS enabled!');
		
		$main->getServer()->getScheduler()->cancelTask($this->id);
	}
}
