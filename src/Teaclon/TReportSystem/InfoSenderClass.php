<?php

/*                             Copyright (c) 2017-2018 TeaTech All right Reserved.
 *
 *      ████████████  ██████████           ██         ████████  ██           ██████████    ██          ██
 *           ██       ██                 ██  ██       ██        ██          ██        ██   ████        ██
 *           ██       ██                ██    ██      ██        ██          ██        ██   ██  ██      ██
 *           ██       ██████████       ██      ██     ██        ██          ██        ██   ██    ██    ██
 *           ██       ██              ████████████    ██        ██          ██        ██   ██      ██  ██
 *           ██       ██             ██          ██   ██        ██          ██        ██   ██        ████
 *           ██       ██████████    ██            ██  ████████  ██████████   ██████████    ██          ██
**/

namespace Teaclon\TReportSystem;

use pocketmine\plugin\PluginBase;

abstract class InfoSenderClass extends PluginBase
{
	const AUTHOR_NAME = "Teaclon";
	const AUTHOR_CONTACT = "NONE";
	
	public function trying($msg = null)
	{
		$this->throwLoggerMessage($msg, "trying");
	}
	
	public function successful($msg = null)
	{
		$this->throwLoggerMessage($msg, "successful");
	}
	
	public function info($msg = null)
	{
		$this->throwLoggerMessage($msg, "default");
	}
	
	public function notice($msg = null)
	{
		$this->throwLoggerMessage($msg, "notice");
	}
	
	public function error($msg = null)
	{
		$this->throwLoggerMessage($msg, "error");
	}
	
	public function warning($msg = null)
	{
		$this->throwLoggerMessage($msg, "warning");
	}
	
	public function alert($msg = null)
	{
		$this->throwLoggerMessage($msg, "alert");
	}
	
	public function debug($msg = null)
	{
		$this->throwLoggerMessage($msg, "debug");
	}
	
	
	public function throwLoggerMessage($msg = null, $type = "info")
	{
		$type = strtolower($type);
		if(!in_array($type, ["info", "notice", "error", "warning", "alert", "debug", "success", "try"])) {
			throw new \Exception("调用错误");
		}
		if($type === "success") {
			$this->getServer()->getLogger()->info(" §aSUCCESSFUL >>  ".$msg);
		} elseif($type === "try") {
			$this->getServer()->getLogger()->info(" §aTRY >>  ".$msg);
		} else {
			$this->getServer()->getLogger()->{$type}(" ".strtoupper($type)." >> {$msg}");
		}
	}
	
	
	
	public function getPrefix($prefix = null)
	{
		return $prefix===null? "§f[§6§l{$this->getPluginName()}§r§f] ": "§f[§6§l{$this->getPluginName()}§r§f - {$prefix}§f] ";
	}
	
	public function getPluginName()
	{
		return $this->getDescription()->getName();
	}
	
	
	public function sendAuthor()
	{
		
		$this->info("\n/*                             Copyright (c) 2017-2018 TeaTech All right Reserved.
 *
 *      ████████████  ██████████           ██         ████████  ██           ██████████    ██          ██
 *           ██       ██                 ██  ██       ██        ██          ██        ██   ████        ██
 *           ██       ██                ██    ██      ██        ██          ██        ██   ██  ██      ██
 *           ██       ██████████       ██      ██     ██        ██          ██        ██   ██    ██    ██
 *           ██       ██              ████████████    ██        ██          ██        ██   ██      ██  ██
 *           ██       ██             ██          ██   ██        ██          ██        ██   ██        ████
 *           ██       ██████████    ██            ██  ████████  ██████████   ██████████    ██          ██
**/").PHP_EOL;
	}
	
	public function getAuthor()
	{
		return self::AUTHOR_NAME;
	}
	public function getContact()
	{
		return self::AUTHOR_CONTACT;
	}
	
}
?>
