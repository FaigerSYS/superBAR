<?PHP
namespace FaigerSYS\superBAR;

use FaigerSYS\superBAR;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as CLR;

class ConfigProvider {
	public $main, $config, $pp_config;
	
	/*-----SOON-----
	public function set($k = false, $v = true, $type = 'default', $group = false) {
		if ($type == 'default' && $k) {
			$this->def_provider($k, $v);
		} elseif ($type == 'pp') {
			$this->pp_provider($group, $k, $v);
		}
	}
	*/
	
	public function loadData() {
		$this->config = new Config($this->main->getDataFolder() . 'config.yml', Config::YAML);
		$this->update();
		
		$data = $this->config->getAll();
		
		$this->main->hotbar->FRMT = array();
		$this->main->hotbar->TIME_FRMT = array($data['time-format']);
		$this->main->hotbar->noF = array($data['no-faction']);
		
		if ($data['type'] !== 'popup')
			$this->main->hotbar->ppup = array(false);
		else
			$this->main->hotbar->ppup = array(true);
		
		$this->main->hotbar->ddts($data['timezone']);
		
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
	
	public function update() {
		$ver = $this->config->get('ver');
		if ($ver != 6) {
			$this->main->getLogger()->info(CLR::RED . "UPDATING CONFIG [ $ver->6 ]...");
			$this->def_provider();
			$this->main->getLogger()->info(CLR::RED . 'UPDATED!!!');
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
		
		if ($k)
			$all[$k] = $v;
		
		if (!isset($all['text-offset-level']))
			$all['text-offset-level'] = 0;
		
		$conf = file($this->main->getDataFolder() . 'config.yml');
		$conf[6] = 'hot-format: "' . str_replace("\n", '\n', $all['hot-format']) . "\"\n";
		$conf[30] = 'text-offset-level: ' . $all['text-offset-level'] . "\n";
		$conf[37] = 'type: "' . $all['type'] . "\"\n";
		$conf[44] = 'timer: ' . $all['timer'] . "\n";
		$conf[51] = 'time-format: "' . $all['time-format'] . "\"\n";
		$conf[60] = 'no-faction: "' . $all['no-faction'] . "\"\n";
		file_put_contents($this->main->getDataFolder() . 'config.yml', join('', $conf));
		
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
		file_put_contents($this->main->getDataFolder() . 'groups.yml', join('', $conf));
		$this->pp_config->reload();
	}
}
