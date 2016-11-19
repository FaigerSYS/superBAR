<?php
namespace FaigerSYS\superBAR\provider;

use pocketmine\utils\TextFormat as CLR;

use FaigerSYS\superBAR\BaseModule;

class ConfigProvider extends BaseModule {
	
	private $PATH;
	private $PP_PATH;
	
	private $DEFAULT_CONFIG_DATA;
	private $DEFAULT_CONFIG_TEXT;
	
	private $DEFAULT_PP_CONFIG_DATA;
	private $DEFAULT_PP_CONFIG_TEXT;
	
	private $CONFIG_VERSION;
	private $CONFIG_TEXTURE;
	private $CONFIG_FIXES;
	
	private $SETTINGS_DESRIPTION;
	
	private $data;
	private $pp_data;
	
	private $PurePerms;
	
	public function __construct($PurePerms = null) {
		$this->PATH = $this->getPlugin()->getDataFolder() . 'config.yml';
		
		$defaultConfig = stream_get_contents($this->getPlugin()->getResource('config.yml'));
		$addotional = json_decode(stream_get_contents($this->getPlugin()->getResource('addotional')), true);
		
		if (!file_exists($this->PATH))
			file_put_contents($this->PATH, $defaultConfig);
		
		$this->DEFAULT_CONFIG_DATA = @yaml_parse($defaultConfig);
		$this->DEFAULT_CONFIG_TEXT = explode("\n", $defaultConfig);
		
		$this->CONFIG_VERSION = $this->DEFAULT_CONFIG_DATA['ver'];
		$this->CONFIG_TEXTURE = $addotional['texture'];
		$this->CONFIG_FIXES = $addotional['fixes'];
		
		$this->SETTINGS_DESRIPTION = $addotional['description'];
		
		if ($PurePerms) {
			$this->PP_PATH = $this->getPlugin()->getDataFolder() . 'groups.yml';
			$pp_defaultConfig = stream_get_contents($this->getPlugin()->getResource('groups.yml'));
			
			if (!file_exists($this->PP_PATH))
				file_put_contents($this->PP_PATH, $pp_defaultConfig);
			
			$this->DEFAULT_PP_CONFIG_DATA = $addotional['pp_settings'];
			$this->DEFAULT_PP_CONFIG_TEXT = explode("\n", $pp_defaultConfig);
			
			$this->PurePerms = $PurePerms;
		}
		
		$this->reloadData();
		$this->generateDefaultConfig();
		$PurePerms ? $this->generatePurePermsConfig() : false;
	}
	
	public function reloadData() {
		$data = @yaml_parse(file_get_contents($this->PATH));
		
		if (!isset($data['ver']) || ($ver = $data['ver']) < $this->CONFIG_VERSION) {
			$this->sendUpdateMessage($ver);
		}
		$data = $this->getFixedData($data, $this->DEFAULT_CONFIG_DATA);
		
		$this->data = $data;
		
		if ($this->PurePerms) {
			$pp_data = @yaml_parse(file_get_contents($this->PP_PATH));
			foreach ($this->PurePerms->getGroups() as $group) {
				$name = $group->getName();
				if (!isset($pp_data[$name])) $pp_data[$name] = [];
				$pp_data[$name] = $this->getFixedData($pp_data[$name], $this->DEFAULT_PP_CONFIG_DATA);
			}
			
			$this->pp_data = $pp_data;
		}
	}
	
	private function getFixedData(array $data, $defaultData) {
		$keys = array_keys($defaultData);
		$fixes = $this->CONFIG_FIXES;
		
		foreach ($keys as $key) {
			if (!isset($data[$key])) {
				if (isset($fixes[$key])) {
					foreach ($fixes[$key] as $oldKey) {
						if (isset($data[$oldKey])) {
							$defaultData[$key] = $data[$oldKey];
							continue;
						}
					}
				}
			} else {
				$defaultData[$key] = $data[$key];
			}
		}
		
		return $defaultData;
	}
	
	private function sendUpdateMessage($verFrom) {
		$this->getPlugin()->sendLog(CLR::GREEN . 'Updating config... [' . $verFrom . ' -> ' . $this->CONFIG_VERSION . ']');
	}
	
	public function getFormatedData() {
		$data = $this->data;
		$settings = [];
		
		$default = [];
		$pp = null;
		
		$default['format'] = ConfigProvider::getShifted($data['hud-format'], intval($data['hud-shift-level']));
		$default['time-format'] = $data['time-format'];
		$default['tip'] = ($data['type'] === 'tip');
		
		if ($this->PurePerms) {
			$pp = [];
			$this->generatePurePermsConfig();
			$pp_data = $this->pp_data;
			foreach ($this->PurePerms->getGroups() as $group) {
				$name = $group->getName();
				if (!isset($pp_data[$name]))
					continue;
				
				$pp[$name] = [];
				
				foreach ($pp_data[$name] as $key => $value) {
					if (isset($data[$key])) {
						$pp_data[$name][$key] = str_replace('%DEFAULT%', $data[$key], $pp_data[$name][$key]);
					}
				}
				
				$pp[$name]['format'] = ConfigProvider::getShifted($pp_data[$name]['hud-format'], intval($pp_data[$name]['hud-shift-level']));
				$pp[$name]['time-format'] = $pp_data[$name]['time-format'];
				$pp[$name]['tip'] = ($pp_data[$name]['type'] === 'tip');
			}
		}
		
		if (!is_numeric($data['timer']) || ($timer = floor($data['timer'])) <= 0)
			$timer = $this->DEFAULT_CONFIG_DATA['timer'];
		
		$settings['default-enabled'] = (bool) $data['default-enabled'];
		$settings['timezone'] = $data['timezone'];
		$settings['timer'] = (int) $timer;
		$settings['no-faction'] = $data['no-faction'];
		
		$settings['default'] = $default;
		$settings['pp'] = $pp;
		
		return $settings;
	}
	
	/**
	 * Set parameter to value
	 * @param string $key
	 * @param mixed $value
	 */
	public function setValue($key = false, $value = true) {
		if ($key !== false && isset($this->DEFAULT_CONFIG_DATA[$key])) {
			$this->data[$key] = ConfigProvider::getRealVar($value);
			$this->generateDefaultConfig();
			return true;
		} else {
			return false;
		}
	}
	
	private function generateDefaultConfig() {
		$data = $this->data;
		
		$config = $this->DEFAULT_CONFIG_TEXT;
		foreach ($this->CONFIG_TEXTURE as $name => $line) {
			$config[$line - 1] = $name . ': ' . ConfigProvider::getString($data[$name]);
		}
		
		file_put_contents($this->PATH, implode("\n", $config));
	}
	
	private function generatePurePermsConfig() {
		$pp_data = $this->pp_data;
		
		$config = $this->DEFAULT_PP_CONFIG_TEXT;
		$add = '  ';
		foreach ($this->PurePerms->getGroups() as $group) {
			$name = $group->getName();
			!isset($pp_data[$name]) ? $data = [] : $data = $pp_data[$name];
			$data = $this->getFixedData($data, $this->DEFAULT_PP_CONFIG_DATA);
			
			$config[] = $name . ':';
			foreach ($data as $key => $value) {
				$config[] = $add . $key . ': ' . ConfigProvider::getString($value);
			}
			$config[] = '';
		}
		
		file_put_contents($this->PP_PATH, implode("\n", $config));
	}
	
	public function getSettingsDescription() {
		return $this->SETTINGS_DESRIPTION;
	}
	
	private static function getShifted($text, $level = 0) {
		if ($level < 0) {
			$n1 = str_pad('', -$level, ' ');
			$n2 = $n1 . "\n";
			$text = $text . $n1;
			return str_replace("\n", $n2, $text);
		} elseif ($level > 0) {
			$n1 = str_pad('', $level, ' ');
			$n2 = "\n" . $n1;
			$text = $n1 . $text;
			return str_replace("\n", $n2, $text);
		} else {
			return $text;
		}
	}
	
	private static function getString($data) {
		if (is_numeric($data)) {
			return "$data";
		} elseif (is_bool($data)) {
			return ($data ? 'true' : 'false');
		} else {
			return '"' . str_replace(["\n", '"'], ['\n', '\\"'], $data) . '"';
		}
	}
	
	private static function getRealVar($string) {
		if (is_numeric($string)) {
			return $string + 0;
		} elseif ($string === 'true') {
			return true;
		} elseif ($string === 'false') {
			return false;
		} else {
			return $string;
		}
	}
	
}
