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
	
	
	public function throwLoggerMessage($msg = null, $type = "default")
	{
		if($type==="trying")
		{
			$this->getServer()->getLogger()->info(" §6§lTRY >>  §r§6".$msg);
		}
		elseif($type==="successful")
		{
			$this->getServer()->getLogger()->info(" §aSUCCESSFUL >>  ".$msg);
		}
		elseif($type==="default")
		{
			$this->getServer()->getLogger()->info($msg);
		}
		elseif($type==="notice")
		{
			$this->getServer()->getLogger()->notice(" §bNOTICE >>  ".$msg);
		}
		elseif($type==="error")
		{
			$this->getServer()->getLogger()->error(" §cERROR >>  ".$msg);
		}
		elseif($type==="warning")
		{
			$this->getServer()->getLogger()->warning(" §eWARNING >>  ".$msg);
		}
		elseif($type==="alert")
		{
			$this->getServer()->getLogger()->alert(" §4ALERT >>  ".$msg);
		}
		elseif($type==="debug")
		{
			$this->getServer()->getLogger()->debug(" §rDEBUG >>  ".$msg);
		}
		else
		{
			throw new \Exception("调用错误");
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