<?PHP
namespace FaigerSYS\superBAR;

use FaigerSYS\superBAR;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as CLR;

class ConfigProvider {
	public $main, $config, $pp_config;
	
	const CONFIG_VER = 8;
	
	public function set($k = false, $v = true) {
		if (!$k)
			return false;
		$this->def_provider($k, $v);
		return true;
	}
	
	public function loadData() {
		$this->config = new Config($this->main->getDataFolder() . 'config.yml', Config::YAML);
		$this->update();
		
		$data = $this->config->getAll();
		
		$this->main->hotbar->TIME_FRMT = array($data['time-format']);
		$this->main->hotbar->noF = array($data['no-faction']);
		
		$this->main->def_enabled = $data['default-enabled'];
		
		if ($data['type'] !== 'popup')
			$this->main->hotbar->ppup = array(false);
		else
			$this->main->hotbar->ppup = array(true);
		
		$this->main->hotbar->init($data['timezone']);
		
		$addonFiles = scandir($this->main->getDataFolder() . 'addons');
		$n = 0;
		foreach ($addonFiles as $fileName) {
			if (preg_match('/\.(php)/', $fileName)) {
				$this->createVariable = '';
				require($this->main->getDataFolder() . 'addons/' . $fileName);
				
				$str = $onStart();
				$n++;
				while (in_array('%ADDON' . $n . '%', $this->main->hotbar->RPLC))
					$n++;
				if (empty($str)) {
					$str = '%ADDON' . $n . '%';
				}
				
				array_push($this->main->hotbar->RPLC, $str);
				$this->main->hotbar->ADNS[$fileName] = $onExecute;
				$this->main->hotbar->VR[$fileName] = $this->createVariable;
				
				$this->main->sendToConsole(CLR::WHITE . 'Loaded addon \'' . CLR::AQUA . $fileName . CLR::WHITE . '\'! ( ' . $str . ' )');
			}
		}
		
		if ($this->main->hotbar->PP) {
			$this->pp_provider();
			$all = $this->pp_config->getAll();
			foreach ($this->main->hotbar->PP->getGroups() as $group) {
				$name = $group->getName();
				
				$all[$name]['hot-format'] = str_replace('%DEFAULT%', $data['hot-format'], $all[$name]['hot-format']);
				$this->main->hotbar->TIME_FRMT[$name] = str_replace('%DEFAULT%', $data['time-format'], $all[$name]['time-format']);
				$this->main->hotbar->noF[$name] = str_replace('%DEFAULT%', $data['no-faction'], $all[$name]['no-faction']);
				
				if (str_replace('%DEFAULT%', $data['type'], $all[$name]['type']) !== 'popup')
					$this->main->hotbar->ppup[$name] = false;
				else
					$this->main->hotbar->ppup[$name] = true;
				
				$lvl = intval(str_replace('%DEFAULT%', $data['text-offset-level'], $all[$name]['text-offset-level']));
				if ($lvl < 0) {
					$n1 = str_pad('', -$lvl, '  ');
					$n2 = $n1 . "\n";
					$all[$name]['hot-format'] = $all[$name]['hot-format'] . $n1;
					$this->main->hotbar->FRMT[$name] = str_replace("\n", $n2, $all[$name]['hot-format']);
				} elseif ($lvl > 0) {
					$n1 = str_pad('', $lvl, '  ');
					$n2 = "\n" . $n1;
					$all[$name]['hot-format'] = $n1 . $all[$name]['hot-format'];
					$this->main->hotbar->FRMT[$name] = str_replace("\n", $n2, $all[$name]['hot-format']);
				} else
					$this->main->hotbar->FRMT[$name] = $all[$name]['hot-format'];
			}
		}
		$lvl = intval($data['text-offset-level']);
		if ($lvl < 0) {
			$n1 = str_pad('', -$lvl, '  ');
			$n2 = $n1 . "\n";
			$data['hot-format'] = $data['hot-format'] . $n1;
			$this->main->hotbar->FRMT[0] = str_replace("\n", $n2, $data['hot-format']);
		} elseif ($lvl > 0) {
			$n1 = str_pad('', $lvl, '  ');
			$n2 = "\n" . $n1;
			$data['hot-format'] = $n1 . $data['hot-format'];
			$this->main->hotbar->FRMT[0] = str_replace("\n", $n2, $data['hot-format']);
		} else
			$this->main->hotbar->FRMT[0] = $data['hot-format'];
		
		return intval($data['timer']);
	}
	
	private function update() {
		$ver = $this->config->get('ver');
		if ($ver != self::CONFIG_VER) {
			$this->main->sendToConsole(CLR::RED . 'UPDATING CONFIG [ ' . $ver . '->' . self::CONFIG_VER . ' ]...');
			$this->def_provider(); 
			$this->main->sendToConsole(CLR::RED . 'UPDATED!!!');
			return true;
		} else
			return false;
	}
	
	private function def_provider($k = false, $v = true) {
		$all = $this->config->getAll();
		
		if (isset($all['format']))
			$all['hot-format'] = $all['format'];
		
		$all['hot-format'] = str_replace('%ITEM%', '%ITEM_ID%:%ITEM_META%', $all['hot-format']);
		file_put_contents($this->main->getDataFolder() . 'config.yml', $this->main->getResource('config.yml'));
		
		if (!isset($all['text-offset-level']))
			$all['text-offset-level'] = '0';
		if (!isset($all['default-enabled']))
			$all['default-enabled'] = 'true';
		echo $all['default-enabled'];
		if (!isset($all['type']))
			$all['type'] = 'tip';
		if (!isset($all['timezone']))
			$all['timezone'] = 'false';
		else {
			if (!$all['timezone'])
				$all['timezone'] = 'false';
			else
				$all['timezone'] = '"' . $all['timezone'] . '"';
		}
		
		if ($k)
			$all[$k] = $v;
		
		$conf = file($this->main->getDataFolder() . 'config.yml');
		$conf[5] = 'hot-format: "' . str_replace("\n", '\n', $all['hot-format']) . "\"\n";
		$conf[36] = 'default-enabled: ' . $all['default-enabled'] . "\n";
		$conf[41] = 'text-offset-level: ' . $all['text-offset-level'] . "\n";
		$conf[47] = 'type: "' . $all['type'] . "\"\n";
		$conf[53] = 'timer: ' . $all['timer'] . "\n";
		$conf[59] = 'time-format: "' . $all['time-format'] . "\"\n";
		$conf[67] = 'no-faction: "' . $all['no-faction'] . "\"\n";
		$conf[70] = 'timezone: ' . $all['timezone'] . "\n";
		file_put_contents($this->main->getDataFolder() . 'config.yml', implode('', $conf));
		
		$this->config->reload();
	}
	
	private function pp_provider($g = false, $k = false, $v = true) {
		$this->pp_config = new Config($this->main->getDataFolder() . 'groups.yml', Config::YAML);
		$all = $this->pp_config->getAll();
		file_put_contents($this->main->getDataFolder() . 'groups.yml', $this->main->getResource('groups.yml'));
		
		if ($g)
			$all[$g][$k] = $v;
		
		$n = 0;
		$conf = file($this->main->getDataFolder() . 'groups.yml');
		$def = '%DEFAULT%';
		foreach ($this->main->hotbar->PP->getGroups() as $group) {
			$name = $group->getName();
			if (isset($all[$name])) {
				$conf[$n * 6 + 6] = $name . ":\n";
				$conf[$n * 6 + 7] = '  hot-format: "' . $all[$name]['hot-format'] . "\"\n";
				$conf[$n * 6 + 8] = '  text-offset-level: "' . $all[$name]['text-offset-level'] . "\"\n";
				$conf[$n * 6 + 9] = '  type: "' . $all[$name]['type'] . "\"\n";
				$conf[$n * 6 + 10] = '  time-format: "' . $all[$name]['time-format'] . "\"\n";
				$conf[$n * 6 + 11] = '  no-faction: "' . $all[$name]['no-faction'] . "\"\n";
			} else {
				$conf[$n * 6 + 6] = $name . ":\n";
				$conf[$n * 6 + 7] = '  hot-format: "' . $def . "\"\n";
				$conf[$n * 6 + 8] = '  text-offset-level: "' . $def . "\"\n";
				$conf[$n * 6 + 9] = '  type: "' . $def . "\"\n";
				$conf[$n * 6 + 10] = '  time-format: "' . $def . "\"\n";
				$conf[$n * 6 + 11] = '  no-faction: "' . $def . "\"\n";
			}
			$n++;
		}
		file_put_contents($this->main->getDataFolder() . 'groups.yml', implode('', $conf));
		$this->pp_config->reload();
	}
}
