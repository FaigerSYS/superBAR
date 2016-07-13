<?PHP
namespace FaigerSYS\superBAR;

use pocketmine\scheduler\PluginTask;

class hotBAR extends PluginTask {
	public $serv, $eT, $noF, $ppup, $CASH, $FACT, $FT_v, $GP, $GT, $PP, $PP_v, $KD, $PFM, $TIME_FRMT, $RPLC;
	public $FRMT = array(), $ADNS = array(), $VR = array(), $DISP = array();
	
	public function init($tz = false) {
		if ($tz)
			date_default_timezone_set($tz);
		$this->RPLC = $this->getStrings();
	}
	
	public function getStrings() {
		return array('%NICK%', '%MONEY%', '%FACTION%', '%ITEM_ID%', '%ITEM_META%', '%TIME%', '%ONLINE%', '%MAX_ONLINE%', '%X%', '%Y%', '%Z%', '%IP%', '%PP_GROUP%', '%TAG%', '%LOAD%', '%TPS%', '%KILLS%', '%DEATHS%', '%LEVEL%', '%PING%', '%GT%', '%AGT%');
	}
	
	public function onRun($tick) {
		$fact = $ppg = $cash = $kll = $dth = $png = $gt = $agt = '§cNoPlug';
		$load = $this->serv->getTickUsage();
		$tps = $this->serv->getTicksPerSecond();
		$plon = count($this->serv->getOnlinePlayers());
		$mxon = $this->serv->getMaxPlayers();
		$FRMT = $this->FRMT;
		$RPLC = $this->RPLC;
		$ADNS = $this->ADNS;
		$DISP = $this->DISP;
		$VR = $this->VR;
		$a = $id = $mt = 0;
		foreach ($this->serv->getOnlinePlayers() as $p) {
			if ($DISP[$name = $p->getName()]) {
				if ($this->PP) {
					if ($this->PP_v < 1.2)
						$ppg = $a = $this->PP->getUser($p)->getGroup()->getName();
					else
						$ppg = $a = $this->PP->getUserDataMgr()->getData($p)['group'];
				}
				
				if ($this->FACT) {
					if ($this->FT_v < 1.5)
						$fact = $this->FACT->getPlayerFaction($name);
					else
						$fact = $this->FACT->getSessionFromName($name)->getFactionName();
					if (count($fact) == 0)
						$fact = $this->noF[$a];
				}
				
				if ($this->eT == 1)
					$cash = $this->CASH->myMoney($name);
				elseif ($this->eT == 2)
					$cash = $this->CASH->getMoney($name);
				
				if ($this->KD) {
					$kll = $this->KD->getKills($name);
					$dth = $this->KD->getDeaths($name);
				}
				
				if ($this->GT) {
					$gt = $this->GT->getSessionTime($name, '%i%:%s%');
					$agt = $this->GT->getAllTime($name, '%H%:%i%:%s%');
				}
				
				if ($this->GP)
					$png = $this->GP->getPing($name);
				
				if ($p->getInventory() != null) {
					$id = $p->getInventory()->getItemInHand()->getId();
					$mt = $p->getInventory()->getItemInHand()->getDamage();
				}
				
				$ADNG = [];
				foreach ($ADNS as $file => $execute) {
					array_push($ADNG, $execute($p, $VR[$file]));
				}
				
				$text = str_replace($RPLC, array_merge(array($name, $cash, $fact, $id, $mt, date($this->TIME_FRMT[$a]), $plon, $mxon, intval($p->x), intval($p->y), intval($p->z), $p->getAddress(), $ppg, $p->getNameTag(), $load, $tps, $kll, $dth, $p->getLevel()->getName(), $png, $gt, $agt), $ADNG), $FRMT[$a]);
				if ($this->ppup[$a])
					$p->sendPopup($text);
				else
					$p->sendTip($text);
			}
		}
	}
}
