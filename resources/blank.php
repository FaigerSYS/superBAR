<?PHP
//You can 'use' another classes
# use pocketmine\Server;

$onStart = function() {
	//There you can place your startup code that executes once after the server has fully started.
	//You can create variable for this addon that can be used for '$onExecute()' function. For this use $this->createVariable .
	//It can be useful if you must one-time get plugin or something else. Don't forget that you can use arrays ;)
	$this->createVariable = array('Halo, ', ' :)');
	return '%TEST%'; //Then you must return string for 'hot-format'
};

$onExecute = function($player, $myVar) {
	//There you must place your code, that used for HUD. You can get player by $player .
	//To get your variable that you create in '$onStart()' use $myVar . Example:
	$tst = $myVar[0] . $player->getName() . $myVar[1];
	return $tst; //Then you must return output for HUD
};

return true; // Just do not touch this \\
?>
