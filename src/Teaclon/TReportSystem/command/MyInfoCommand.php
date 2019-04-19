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


class MyInfoCommand extends BaseCommand
{
	const MY_COMMAND             = "myinfo";
	const MY_COMMAND_PEREMISSION = [self::PERMISSION_ALL];
	public $myprefix = Main::NORMAL_PRE;
	private $tsapi   = null;
	private $pconfig = null;
	
	
	
	
	
	public function __construct(Main $plugin)
	{
		$this->tsapi   = $plugin->getTSApi();
		$this->pconfig = $plugin->pconfig();
		// CommandName, Description, usage, aliases, overloads;
		$this->init($plugin, self::MY_COMMAND, Main::STRING_PRE."的个人信息查询指令", null, ["我的信息"], []);
	}
	
	
	
	public function execute(CommandSender $sender, $commandLabel, array $args)
	{
		$sname = $sender->getName();
		
		if(($sender instanceof Player) && $this->pconfig->exists(strtolower($sname)))
		{
			$this->sendMessage($sender, "§e玩家§b{$sname}§e你好, 你在本服务器的个人信息如下:");
			$this->sendMessage($sender, "§f邮箱: "          .(($this->plugin->isPlayerSetEmail($sname))? "§6".$this->plugin->getEmailFromPlayer($sname) : "§c没有设置邮箱"));
			$this->sendMessage($sender, "§f信用值: §6"      .$this->pconfig->get(strtolower($sname))["credit_value"]);
			$this->sendMessage($sender, "§f被举报次数: §c"  .$this->pconfig->get(strtolower($sname))["was_report_times"]);
			$this->sendMessage($sender, "§f举报他人次数: §c".$this->pconfig->get(strtolower($sname))["report_times"]);
			if($this->pconfig->get(strtolower($sname))["credit_value"] >= 60)
			$this->sendMessage($sender, "§a你的信用值良好, 请继续保持!");
		}
		elseif(!$sender instanceof Player) $this->sendMessage($sender, "控制台闹什么闹.");
		else $this->sendMessage($sender, "§c未知错误, 请联系开发者§bTeaclon§c修复!");
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