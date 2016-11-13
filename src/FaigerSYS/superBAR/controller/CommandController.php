<?php
namespace FaigerSYS\superBAR\controller;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\TextFormat as CLR;

abstract class CommandController {
	
	public static function executeCommand($plugin, CommandSender $sender, array $args) {
		$NL = ($sender instanceof ConsoleCommandSender) ? PHP_EOL : "\n";
		
		$command = array_shift($args);
		if ($command === null) {
			$sender->sendMessage(
				$plugin::PREFIX . 'Version ' . $plugin->getDescription()->getVersion() . $NL .
				CLR::GRAY . 'Commands list: ' . CLR::DARK_GREEN . '/sb help'
			);
			
		} elseif ($command === 'help') {
			if (!$plugin->hasPermission($sender, 'help'))
				return $sender->sendMessage($plugin::PREFIX . $plugin::NO_PERM);
			
			$sender->sendMessage(
				$plugin::PREFIX . 'Commands:' . $NL .
				CLR::DARK_GREEN . '/sb enable' . CLR::GREEN . ' or ' . CLR::DARK_GREEN . '/sb on' . CLR::BLUE . ' - ' . CLR::DARK_AQUA . 'enable HUD for you' . $NL .
				CLR::DARK_GREEN . '/sb disable' . CLR::GREEN . ' or ' . CLR::DARK_GREEN . '/sb off' . CLR::BLUE . ' - ' . CLR::DARK_AQUA . 'disable HUD for you' . $NL .
				CLR::DARK_GREEN . '/sb change' . CLR::GREEN . ' or ' . CLR::DARK_GREEN . '/sb set' . CLR::BLUE . ' - ' . CLR::DARK_AQUA . 'change HUD settings' . $NL .
				CLR::DARK_GREEN . '/sb reload' . CLR::BLUE . ' - ' . CLR::DARK_AQUA . 'reload the superBAR settings'
			);
			
		} elseif ($command === 'reload') {
			if (!$plugin->hasPermission($sender, 'reload'))
				return $sender->sendMessage($plugin::PREFIX . $plugin::NO_PERM);
			
			$plugin->reloadSettings();
			$sender->sendMessage($plugin::PREFIX . 'Successfully reloaded!');
			
		} elseif ($command === 'enable' || $command === 'on') {
			if (!$plugin->hasPermission($sender, 'switch') || !$plugin->hasPermission($sender, 'use'))
				return $sender->sendMessage($plugin::PREFIX . $plugin::NO_PERM);
			
			$add = '';
			if ($sender instanceof ConsoleCommandSender)
				$add = ' But you still will not see it here xD';
			
			$plugin->getHUD()->setDisplay($sender->getName(), true);
			$sender->sendMessage($plugin::PREFIX . 'Enabled!' . $add);
				
		} elseif ($command === 'disable' || $command === 'off') {
			if (!$plugin->hasPermission($sender, 'switch') || !$plugin->hasPermission($sender, 'use'))
				return $sender->sendMessage($plugin::PREFIX . $plugin::NO_PERM);
			
			$add = '';
			if ($sender instanceof ConsoleCommandSender)
				$add = ' As well as always :P';
			
			$plugin->getHUD()->setDisplay($sender->getName(), false);
			$sender->sendMessage($plugin::PREFIX . 'Disabled!' . $add);
			
		} elseif ($command === 'set' || $command === 'change') {
			if (!$plugin->hasPermission($sender, 'change'))
				return $sender->sendMessage($plugin::PREFIX . $plugin::NO_PERM);
			
			$key = array_shift($args);
			$array = array_flip($plugin->getSettingsDescription());
			
			if (empty($key)) {
				$message = $plugin::PREFIX . 'You can change (Description:' . CLR::GOLD . ' parameter' . CLR::GRAY . '):' . $NL;
				$array = array_chunk($array, 3, true);
				foreach ($array as $description) {
					$message .= CLR::GRAY . str_replace('=', ': ', urldecode(http_build_query($description, '', CLR::GREEN . ' | ' . CLR::GRAY))) . $NL;
				}
				$message .= CLR::DARK_GREEN . '/sb set' . CLR::GOLD . ' <parameter> ' . CLR::DARK_GREEN . '<value>';
				$sender->sendMessage($message);
			} elseif (in_array($key, $array)) {
				$value = implode(' ', $args);
				if (!empty($value)) {
					$plugin->getConfigProvider()->setValue($key, $value);
					$plugin->reloadSettings();
					$sender->sendMessage($plugin::PREFIX . 'Successfully changed ' . $key . '!');
				} else {
					$sender->sendMessage($plugin::PREFIX . CLR::RED . 'Please provide value');
				}
			} else {
				$sender->sendMessage($plugin::PREFIX . CLR::RED . 'This setting does not exists');
			}
			
		} else {
			$sender->sendMessage($plugin::PREFIX . CLR::RED . 'Wrong command! ' . CLR::DARK_GREEN . '/sb help ' . CLR::RED . 'for a list of commands');
		}
	}
	
}
