<?php

namespace FaigerSYS\superBAR;

use pocketmine\scheduler\PluginTask;

class hotBAR extends PluginTask {
	public $serv, $eT, $noF, $ppup, $CASH, $FACT, $PP, $PP_v, $KD, $FRMT, $PFM, $TIME_FRMT;
	
	public function ddts($tz) {
		if ($tz)
			date_default_timezone_set($tz);
	}
	
	public function onRun($tick) {
		$load = $this->serv->getTickUsage();
		$tps = $this->serv->getTicksPerSecond();
		$plon = count($this->serv->getOnlinePlayers());
		$mxon = $this->serv->getMaxPlayers();
		$a = 0;
		foreach ($this->serv->getOnlinePlayers() as $p) {
			if ($p != null) {
				$name = $p->getName();
				
				if ($this->PP) {
					if ($this->PP_v == '1.1' || $this->PP_v == '1.0')
						$ppg = $a = $this->PP->getUser($p)->getGroup()->getName();
					else
						$ppg = $a = $this->PP->getUserDataMgr()->getData($p)['group'];
				} else
					$ppg = '§cNoPPpl';
				
				if ($this->FACT) {
					if (count($fact = $this->FACT->getPlayerFaction($name)) == 0)
						$fact = $this->noF[$a];
				} else $fact = '§cNoFactpl';
				
				if ($this->eT == 1)
					$cash = $this->CASH->myMoney($name);
				elseif ($this->eT == 2)
					$cash = $this->CASH->getMoney($name);
				else
					$cash = '§cNoEcopl';
				
				if ($this->KD) {
					$kll = $this->KD->getKills($name);
					$dth = $this->KD->getDeaths($name);
				} else
					$kll = $dth =  '§cNoPl';
				
				if ($p->getInventory() != null) {
					$id = $p->getInventory()->getItemInHand()->getId();
					$mt = $p->getInventory()->getItemInHand()->getDamage();
				} else
					$id = $mt = 0;
				
				$time = date($this->TIME_FRMT[$a]);
				$text = str_replace(array('%NICK%', '%MONEY%', '%FACTION%', '%ITEM_ID%', '%ITEM_META%', '%TIME%', '%ONLINE%', '%MAX_ONLINE%', '%X%', '%Y%', '%Z%', '%IP%', '%PP_GROUP%', '%TAG%', '%LOAD%', '%TPS%', '%KILLS%', '%DEATHS%', '%LEVEL%'), array($name, $cash, $fact, $id, $mt, $time, $plon, $mxon, intval($p->x), intval($p->y), intval($p->z), $p->getAddress(), $ppg, $p->getNameTag(), $load, $tps, $kll, $dth, $p->getLevel()->getName()), $this->FRMT[$a]);
				if ($this->ppup[$a])
					$p->sendPopup($text);
			}
		}
	}
}
