<?PHP
namespace FaigerSYS\superBAR;

use pocketmine\scheduler\PluginTask;
use FaigerSYS\superBAR\ConfigProvider;
use FaigerSYS\superBAR\hotBAR;
use pocketmine\utils\TextFormat as CLR;

class PL extends PluginTask {
	public $main, $id, $check;
	
	public function onRun($tick) {
		$main = $this->main;
		
		if (!$this->check) {
			$main->sendToConsole('Error when trying enable superBAR! Try restart server or post issue here: github.com/FaigerSYS/superBAR/issues', 2);
			$main->getServer()->getScheduler()->cancelTask($this->id);
			return $main->setEnabled(false);
		}
		
		$main->sendToConsole(CLR::GOLD . 'superBAR loading...');
		
		$mgr = $main->getServer()->getPluginManager();
		
		$this->check = false;
		
		@mkdir($main->getDataFolder());
		@mkdir($main->getDataFolder() . 'addons');
		@mkdir($main->getDataFolder() . 'addons_info');
		if (!file_exists($main->getDataFolder() . 'config.yml'))
			file_put_contents($main->getDataFolder() . 'config.yml', $main->getResource('config.yml'));
		if (!file_exists($main->getDataFolder() . 'addons_info/addons.txt'))
			file_put_contents($main->getDataFolder() . 'addons_info/addons.txt', $main->getResource('addons.txt'));
		if (!file_exists($main->getDataFolder() . 'addons_info/blank.php'))
			file_put_contents($main->getDataFolder() . 'addons_info/blank.php', $main->getResource('blank.php'));
		
		$main->getServer()->getPluginManager()->registerEvents($main, $main);
		
		$main->conf_provider = new ConfigProvider;
		$main->conf_provider->main = $main;
		
		$main->hotbar = new hotBAR($main);
		$main->hotbar->serv = $main->getServer();
		
		if ($main->hotbar->CASH = $main->getPlug('EconomyAPI')) {
			$main->hotbar->eT = 1;
			$main->sendToConsole(CLR::GREEN . 'EconomyAPI OK!');
		} elseif ($main->hotbar->CASH = $main->getPlug('PocketMoney')) {
			$main->hotbar->eT = 2;
			$main->sendToConsole(CLR::GREEN . 'PocketMoney OK!');
		}
		
		if ($main->hotbar->FACT = $main->getPlug('FactionsPro')) {
			$main->hotbar->FT_v = floatval(substr($main->hotbar->FACT->getDescription()->getVersion(), 0, 3));
			$main->sendToConsole(CLR::GREEN . 'FactionsPro OK!');
		}
			
		if ($main->hotbar->GP = $main->getPlug('GetPing'))
			$main->sendToConsole(CLR::GREEN . 'GetPing OK!');
		
		if ($main->hotbar->GT = $main->getPlug('GameTime'))
			$main->sendToConsole(CLR::GREEN . 'GameTime OK!');
		
		if ($main->hotbar->PP = $main->getPlug('PurePerms')) {
			$main->hotbar->PP_v = floatval(substr($main->hotbar->PP->getDescription()->getVersion(), 0, 3));
			$main->sendToConsole(CLR::GREEN . 'PurePerms OK!');
		}
		
		if ($main->hotbar->KD = $main->getPlug('KillChat'))
			$main->sendToConsole(CLR::GREEN . 'KillChat OK!');
		elseif ($main->hotbar->KD = $main->getPlug('ScorePvP'))
			$main->sendToConsole(CLR::GREEN . 'ScorePvP OK!');
		
		$main->dataLoader();
		
		$main->sendToConsole(CLR::GOLD . 'superBAR by FaigerSYS enabled!');
		
		$main->getServer()->getScheduler()->cancelTask($this->id);
	}
}
