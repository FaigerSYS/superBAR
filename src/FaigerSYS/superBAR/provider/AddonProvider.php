<?php
namespace FaigerSYS\superBAR\provider;

use pocketmine\utils\TextFormat as CLR;

use FaigerSYS\superBAR\BaseModule;

class AddonProvider extends BaseModule {
	
	const CURRENT_API_VERSION = 2;
	const SUPPORTED_API_VERSIONS = [1, 2];
	
	/** @var array */
	private $addons = [];
	
	/** @var mixed */
	private $createVariable = null;
	private $setVariable = null;
	
	public function __construct() {
		$path = $this->getPlugin()->getDataFolder();
		
		@mkdir($path . 'addons');
		file_put_contents($path . 'addons/Read_Me.txt', $this->getPlugin()->getResource('addons_info.txt'));
		
		$this->reloadAddons();
	}
	
	public function reloadAddons() {
		$files = $this->getFilesList();
		$addons = [];
		$strInUse = [];
		
		foreach ($files as $name => $path) {
			$this->createVariable = null;
			$this->setVariable = null;
			$api = $onStart = $onExecute = null;
			
			@require($path);
			
			$API = $API ?? 1;
			if (is_array($API)) {
				$API = array_intersect(AddonProvider::SUPPORTED_API_VERSIONS, array_unique($API));
				if (count($API) > 0) $API = max($API);
			} else {
				$API = (int) $API;
			}
			
			if ((!isset($onStart) && $missing = '$onStart') || (!isset($onExecute) && $missing = '$onExexute')) {
				$this->getPlugin()->sendLog('Unable to load ' . $name . '! Missing "' . $missing . '"');
				continue;
			}
			
			if ($API === 1) {
				list($str, $canLoad, $errorMsg) = [$onStart(), true, null];
			} elseif ($API === 2) {
				list($str, $canLoad, $errorMsg) = $onStart(AddonProvider::CURRENT_API_VERSION);
			} else {
				$errorMsg = 'Incompatible API version (' . (is_array($API) ? implode(', ', $API) : $API) . ')';
				$canLoad = false;
			}
			
			if (!$canLoad) {
				$this->getPlugin()->sendLog('Could not load "' . $name . '" addon. Reason: ' . ($errorMsg ? '"' . $errorMsg . '"' : 'none'));
				continue;
			}
			
			$toBegin = false;
			if (empty($str) || !$str) {
				$str = false;
			} elseif (in_array($str, $strInUse)) {
				$add = '_';
				while (in_array($add . $str, $strInUse)) $add .= '_';
				$str = $add . $str;
				$toBegin = true;
			}
			$str ? $strInUse[] = $str : false;
			
			$addon = [$API, $onExecute, ($this->setVariable === null ? $this->createVariable : $this->setVariable), $str];
			
			if ($toBegin)
				array_unshift($addons, $addon);
			else
				$addons[] = $addon;
			
			$this->getPlugin()->sendLog(CLR::WHITE . 'Loaded "' . $name . '" addon' . ($str !== false ? ' (' . $str . ')' : null));
		}
		
		$this->addons = $addons;
	}
	
	public function getFormatedAddons($player) {
		$inputs = [];
		$outputs = [];
		
		foreach ($this->addons as $key => $addon) {
			$this->setVariable = null;
			list($API, $onExecute, $var, $input) = $addon;
			
			if ($API === 1) {
				$output = $onExecute($player, $var);
			} elseif ($API === 2) {
				list($output, $showHUD) = $onExecute($player, $var, $API);
				if (isset($this->setVariable)) $this->addons[$key][2] = $this->setVariable;
				if ($showHUD === false) return [$showHUD, $output, $showHUD];
			}
			
			if ($input) {
				$inputs[] = $input;
				$outputs[] = $output;
			}
		}
		
		return [true, $inputs, $outputs];
	}
	
	private function setVariable($var = null) {
		$this->setVariable = $var;
	}
	
	private function getFilesList() {
		$path = $this->getPlugin()->getDataFolder() . 'addons/';
		$files = scandir($path);
		$addons = [];
		
		foreach ($files as $fileName) {
			if (preg_match('/\.(php)/', $fileName) && is_file($filePath = $path . $fileName)) {
				$addons[$fileName] = $filePath;
			}
		}
		
		return $addons;
	}
	
}
