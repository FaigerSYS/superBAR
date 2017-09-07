<?php
namespace FaigerSYS\superBAR\controller;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;

use FaigerSYS\superBAR\BaseModule;

class EventController extends BaseModule implements Listener {
	
	/**
	 * @param PlayerJoinEvent $e
	 * @priority MONITOR
	 */
	public function onJoin(PlayerJoinEvent $e) {
		$display = ($this->getPlugin()->isDefaultEnabled() && $this->getPlugin()->hasPermission($player = $e->getPlayer(), 'use'));
		$this->getPlugin()->getHUD()->setDisplay($player->getName(), $display);
	}
	
	/**
	 * @param PlayerRespawnEvent $e
	 * @priority MONITOR
	 */
	public function onRespawn(PlayerRespawnEvent $e) {
		$display = ($this->getPlugin()->isDefaultEnabled() && $this->getPlugin()->hasPermission($player = $e->getPlayer(), 'use'));
		$this->getPlugin()->getHUD()->setDisplay($player->getName(), $display);
	}
	
	/**
	 * @param PlayerQuitEvent $e
	 * @priority MONITOR
	 */
	public function onLogout(PlayerQuitEvent $e) {
		$this->getPlugin()->getHUD()->setDisplay($e->getPlayer()->getName(), false);
	}
	
}
