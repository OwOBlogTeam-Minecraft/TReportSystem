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

namespace Teaclon\TReportSystem\command;

use Teaclon\TReportSystem\Main;

use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use Teaclon\TSeriesAPI\command\subcommand\BaseCommand;
use Teaclon\TSeriesAPI\command\CommandManager;


class SetEMailCommand extends BaseCommand
{
	const MY_COMMAND             = "setemail";
	const MY_COMMAND_PEREMISSION = [self::PERMISSION_ALL];
	public $myprefix = Main::NORMAL_PRE;
	private $tsapi   = null;
	
	
	
	
	
	public function __construct(Main $plugin)
	{
		$this->tsapi   = $plugin->getTSApi();
		// CommandName, Description, usage, aliases, overloads;
		$this->init($plugin, self::MY_COMMAND, Main::STRING_PRE."的邮箱设置指令", null, ["邮箱"], []);
	}
	
	
	
	public function execute(CommandSender $sender, $commandLabel, array $args)
	{
		$sname = $sender->getName();
		if(!$this->plugin->isPlayerSetEmail($sname))
		{
			if(!isset($args[0]))
			{
				$this->sendMessage($sender, "用法: §d/§6".self::MY_COMMAND." §f<§e你的邮箱§f>  设置邮箱");
				return true;
			}
			if(!$this->plugin->check_email_format($args[0]))
			{
				$this->sendMessage($sender, "§c邮箱格式错误, 请重新输入!");
				return true;
			}
			
			if($this->plugin->setEmailFromPlayer($sname, $args[0]))
			{
				$this->sendMessage($sender, "§a邮箱设置成功, 如需更换请联系管理员. 你设置的邮箱为: §e".$args[0]);
				return true;
			}
			else
			{
				$this->sendMessage($sender, "§c未知错误, 请联系开发者§bTeaclon§c修复!");
				$this->sendMessage($sender, "§c错误代码: §f[§eTE-0". __LINE__ ."§f]");
				return true;
			}
		}
		else
		{
			$this->sendMessage($sender, "§c你已经设置过你的邮箱了, 如果需要更换邮箱, 请联系管理员更换!");
			return true;
		}
		return true;
	}
	
	
	public static function getCommandPermission(string $cmd)
	{
		
	}
	
	public static function getHelpMessage() : array
	{
		return [];
	}
	
	
}
?>