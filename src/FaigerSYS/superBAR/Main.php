<?PHP
namespace FaigerSYS\superBAR;

use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\TextFormat as CLR;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\Command;

use FaigerSYS\superBAR\PL;

class Main extends PluginBase implements Listener {
	public $hotbar, $conf_provider, $task, $prefix, $no_perm, $def_enabled;
	
	public function onEnable() {
		$this->getLogger()->info(CLR::GOLD . 'superBAR will be enabled after the complete server load...');
		
		$this->prefix = CLR::AQUA . '[' . CLR::YELLOW . 'super' . CLR::GOLD . 'BAR' . CLR::AQUA . '] ' . CLR::GRAY;
		$this->no_perm = CLR::RED . "You don't have permission to use this command...";
		
		$pl = new PL($this);
		$pl->main = $this;
		$pl->check = true;
		
		$task = $this->getServer()->getScheduler()->scheduleRepeatingTask($pl, 1);
		$pl->id = $task->getTaskId();
	}
	
	public function dataLoader($reload = false) {
		if ($reload)
			$this->getServer()->getScheduler()->cancelTask($this->task->getTaskId());
		$ticks = $this->conf_provider->loadData();
		$this->task = $this->getServer()->getScheduler()->scheduleRepeatingTask($this->hotbar, $ticks);
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, $lbl, array $args){
		if($cmd->getName() == 'superbar') {
			if (!isset($args[0])) {
				$sender->sendMessage(
					$this->prefix . "Version " . $this->getDescription()->getVersion() . "\n" . 
					CLR::GRAY . 'Commands list: ' . CLR::DARK_GREEN . '/sb help'
				);
			} elseif ($args[0] == 'help') {
				if ($sender->hasPermission('superbar.help')) {
					$sender->sendMessage(
						$this->prefix . "Commands:\n" .
						CLR::DARK_GREEN . '/sb enable' . CLR::GREEN . ' or ' . CLR::DARK_GREEN . '/sb on' . CLR::BLUE . ' - ' . CLR::DARK_AQUA . "enable HUD for you\n" .
						CLR::DARK_GREEN . '/sb disable' . CLR::GREEN . ' or ' . CLR::DARK_GREEN . '/sb off' . CLR::BLUE . ' - ' . CLR::DARK_AQUA . "disable HUD for you\n" .
						CLR::DARK_GREEN . '/sb change' . CLR::GREEN . ' or ' . CLR::DARK_GREEN . '/sb set' . CLR::BLUE . ' - ' . CLR::DARK_AQUA . "change HUD settings\n" .
						CLR::DARK_GREEN . '/sb reload' . CLR::BLUE . ' - ' . CLR::DARK_AQUA . "reload the superBAR settings\n"
					);
				} else
					$sender->sendMessage($this->prefix . $this->no_perm);
			} elseif ($args[0] == 'reload') {
				if ($sender->hasPermission('superbar.reload')) {
					$this->dataLoader(true);
					$sender->sendMessage($this->prefix . 'Successfully reloaded!');
				} else
					$sender->sendMessage($this->prefix . $this->no_perm);
			} elseif ($args[0] == 'enable' || $args[0] == 'on') {
				if ($sender instanceof ConsoleCommandSender)
					$add = ' But you still will not see it here xD';
				else
					$add = '';
				
				if ($sender->hasPermission('superbar.switch') && $sender->hasPermission('superbar.use')) {
					$this->hotbar->DISP[$sender->getName()] = true;
					$sender->sendMessage($this->prefix . 'Enabled!' . $add);
				} else
					$sender->sendMessage($this->prefix . $this->no_perm);
			} elseif ($args[0] == 'disable' || $args[0] == 'off') {
				if ($sender instanceof ConsoleCommandSender)
					$add = ' As well as always :P';
				else
					$add = '';
				
				if ($sender->hasPermission('superbar.switch') && $sender->hasPermission('superbar.use')) {
					$this->hotbar->DISP[$sender->getName()] = false;
					$sender->sendMessage($this->prefix . 'Disabled!' . $add);
				} else
					$sender->sendMessage($this->prefix . $this->no_perm);
			} elseif ($args[0] == 'set' || $args[0] == 'change') {
				if ($sender->hasPermission('superbar.change')) {
					$tmp = array('hot-format', 'text-offset-level', 'timer', 'time-format', 'no-faction', 'timezone', 'type', 'default-enabled');
					if (!isset($args[1])) {
						$l = CLR::GREEN . '|' . CLR::GRAY;
						$sender->sendMessage(
							$this->prefix . 'You can change (For changing:' . CLR::GOLD . ' str' . CLR::GRAY . "):\n" .
							CLR::GRAY . 'HUD-format: hot-format ' . $l . " Text offset level: text-offset-level\n" . 
							CLR::GRAY . 'Timer: timer ' . $l . ' Time format: time-format ' . $l . " No Faction: no-faction\n" . 
							CLR::GRAY . 'Timezone: timezone ' . $l . ' Type: type ' . $l . " Defaul enabled: default-enabled\n" .
							CLR::DARK_GREEN . '/sb set' . CLR::GOLD . ' <str>' . CLR::DARK_GREEN . ' <value>'
						);
					} elseif (in_array($args[1], $tmp)) {
						if (isset($args[2])) {
							$value = implode(' ', array_slice($args, 2));
							$this->conf_provider->set($args[1], $value);
							$this->dataLoader(true);
							$sender->sendMessage($this->prefix . 'Successfully changed ' . $args[1] . '!');
						} else {
							$sender->sendMessage($this->prefix . CLR::RED . 'Please provide value');
						}
					} else {
						$sender->sendMessage($this->prefix . CLR::RED . 'This setting is not exists');
					}
				} else
					$sender->sendMessage($this->prefix . $this->no_perm);
			} else {
				$sender->sendMessage($this->prefix . CLR::RED . 'Wrong command!' . CLR::DARK_GREEN . ' /sb help ' . CLR::RED . 'for a list of commands.');
			}
		}
	}
	
	public function onPreJoin(PlayerPreLoginEvent $e) {
		$this->hotbar->DISP[$e->getPlayer()->getName()] = false;
	}
	
	public function onJoin(PlayerJoinEvent $e) {
		if ($e->getPlayer()->hasPermission('superbar.use') && $this->def_enabled)
			$this->hotbar->DISP[$e->getPlayer()->getName()] = true;
		else
			$this->hotbar->DISP[$e->getPlayer()->getName()] = false;
	}
	
	public function getPlug($name) {
		if ($plug = $this->getServer()->getPluginManager()->getPlugin($name)) {
			if ($plug->isEnabled()) return $plug;
		}
		return false;
	}
	
	public function sendToConsole($text, $type = 1) {
		if ($type === 2)
			return $this->getServer()->getLogger()->error($this->prefix . $text);
		return $this->getServer()->getLogger()->info($this->prefix . $text);
	}
}
