<?PHP
namespace FaigerSYS\superBAR;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as CLR;

class ConfigUpdate extends PluginBase {

	public function update($main) {
		$ver = $main->config->get('ver');
		if ($ver == 1) {
			$main->getLogger()->info(CLR::RED . 'UPDATING CONFIG [ 1->4 ]...');
			$this->u1($main);
			$this->u2($main);
			$main->getLogger()->info(CLR::RED . 'UPDATED!!!');
		} elseif ($ver == 2 || $ver == 3) {
			$main->getLogger()->info(CLR::RED . "UPDATING CONFIG [ $ver->4 ]...");
			$this->u2($main);
			$main->getLogger()->info(CLR::RED . 'UPDATED!!!');
		}
	}
	
	public function u1($main) {
		$all = $main->config->getAll();
		$all['format'] = str_replace('%ITEM%', '%ITEM_ID%:%ITEM_META%', $all['format']);
		file_put_contents($main->getDataFolder() . 'config.yml', $main->getResource('config.yml'));
		
		$conf = file($main->getDataFolder() . 'config.yml');
		$conf[6] = 'hot-format: "' . str_replace("\n", '\n', $all['format']) . "\"\n";
		$conf[38] = 'type: "' . $all['type'] . "\"\n";
		$conf[44] = 'timer: ' . $all['timer'] . "\n";
		$conf[51] = 'time-format: "' . $all['time-format'] . "\"\n";
		$conf[60] = 'no-faction: "' . $all['no-faction'] . "\"\n";
		file_put_contents($main->getDataFolder() . 'config.yml', join('', $conf));
	}
	
	public function u2($main) {
		$all = $main->config->getAll();
		file_put_contents($main->getDataFolder() . 'config.yml', $main->getResource('config.yml'));
		
		$conf = file($main->getDataFolder() . 'config.yml');
		$conf[6] = 'hot-format: "' . str_replace("\n", '\n', $all['hot-format']) . "\"\n";
		$conf[31] = 'text-offset-level: ' . $all['text-offset-level'] . "\n";
		$conf[38] = 'type: "' . $all['type'] . "\"\n";
		$conf[44] = 'timer: ' . $all['timer'] . "\n";
		$conf[51] = 'time-format: "' . $all['time-format'] . "\"\n";
		$conf[60] = 'no-faction: "' . $all['no-faction'] . "\"\n";
		file_put_contents($main->getDataFolder() . 'config.yml', join('', $conf));
	}
	
}
