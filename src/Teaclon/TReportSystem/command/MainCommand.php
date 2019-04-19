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


class MainCommand extends BaseCommand
{
	const MY_COMMAND             = "report";
	const MY_COMMAND_PEREMISSION = [self::PERMISSION_ALL];
	public $myprefix = Main::NORMAL_PRE;
	private $tsapi   = null;
	private $pconfig = null;
	
	
	
	
	
	public function __construct(Main $plugin)
	{
		$this->tsapi   = $plugin->getTSApi();
		$this->pconfig = $plugin->pconfig();
		// CommandName, Description, usage, aliases, overloads;
		$this->init($plugin, self::MY_COMMAND, Main::STRING_PRE."的举报功能指令", null, ["举报"], []);
	}
	
	
	
	public function execute(CommandSender $sender, $commandLabel, array $args)
	{
		$sname = $sender->getName();
		
		if(($sender instanceof Player) && !$this->plugin->isPlayerSetEmail($sname))
		{
			$this->sendMessage($sender, "§c检测到你没有设置邮箱, 请输入 §d/§6邮箱 §f<§e你的邮箱§f> §c设置邮箱.");
			return true;
		}
		if(!isset($args[0]))
		{
			$this->execute($sender, $commandLabel, ["help"]);
			return \true;
		}
		
		if($args[0] === "帮助") $args[0] = "help";
		if($args[0] === "类型") $args[0] = "type";
		if(isset(self::getHelpMessage()[$args[0]]))
		{
			switch($args[0])
			{
				case "help":
				case "帮助":
				default:
					$this->sendMessage($sender, "§e--------------§b".Main::STRING_PRE."指令助手§e--------------");
					foreach(self::getHelpMessage() as $cmd => $message)
					{
						if($this->hasSenderPermission($sender, $cmd))
						$this->sendMessage($sender, str_replace("{cmd}", self::MY_COMMAND, $message));
						else continue;
					}
					$this->sendMessage($sender, "如果你已经填写过邮箱, 可以忽略填写邮箱.");
					$this->sendMessage($sender, "§e---------------------------------------");
					return true;
				break;
				
				case "type":
				case "类型":
					$this->sendMessage($sender, "可以举报的类型有:");
					foreach($this->plugin->config()->get("举报类型") as $id => $type)
					{
						$this->sendMessage($sender, "§eID: §a{$id}§e; 类型: §c{$type}");
					}
					return true;
				break;
			}
		}
		else
		{
			if(!isset($args[0]))
			{
				$this->sendMessage($sender, "§c指令使用错误. 请输入你想举报的玩家名称, 或者输入 §d/§6".self::MY_COMMAND." help §c查看帮助.");
				return true;
			}
			if(strtolower($args[0]) === strtolower($sname))
			{
				$this->sendMessage($sender, "不是很建议你举报你自已哟~");
				return true;
			}
			
			if(!isset($args[1]))
			{
				$this->sendMessage($sender, "§c操作错误. 请输入你要举报的类型, 确认无误后在进行最后的操作.");
				return true;
			}
			if(!isset($args[2]))
			{
				$this->sendMessage($sender, "§c操作错误. 请输入举报原因, 确认无误后在进行最后的操作.");
				return true;
			}
			if(!$this->plugin->isPlayerSetEmail($sname))
			{
				$this->sendMessage($sender, "§c操作错误. 请输入你的邮箱, 确认无误后在进行最后的操作.");
				return true;
			}
			
			if($this->pconfig->exists(strtolower($sname)) && $this->pconfig->get(strtolower($sname))["remaining_reports"] == 0)
			{
				$this->sendMessage($sender, "抱歉, 你今日的举报次数已经用完了, 请等到明天在进行本操作吧~");
				return true;
			}
			
			
			$was_report_player = $args[0];       // 被举报者名字;
			$was_report_type = $args[1];         // 被举报类型;
			$reason = $args[2];                  // 举报原因;
			
			if(!$this->plugin->getTSApi()->getPlayerDataFileInString($args[0]))
			{
				$this->sendMessage($sender, "§c该玩家不存在! 请输入一个正确的玩家名称.");
				return true;
			}
			if(!isset($this->plugin->bantype[$was_report_type]))
			{
				$this->sendMessage($sender, "§c抱歉, 你输入的举报类型有错, 请检查正确后再提交你的请求.");
				return true;
			}
			
			if(!file_exists($this->plugin->getServer()->getDataPath(). "/players/".strtolower($args[0]).".dat"))
			{
				$this->sendMessage($sender, "§c很抱歉, 我们没能在本服务器找到玩家 §e{$was_report_player} §c的资料, 请确认你举报的玩家名字是否正确.");
				return true;
			}
			if($this->plugin->addReport_Temp($sname, $reason, 1, $was_report_player, $was_report_type))
			{
				$this->plugin->ssm($this->myprefix."§b------新举报!------", "info", "server");
				$this->plugin->ssm($this->myprefix."来自玩家 §e{$sname}§f; 被举报者 §c{$was_report_player}", "info", "server");
				$this->plugin->ssm($this->myprefix."类型 §6{$this->plugin->bantype[$was_report_type]}§f; 原因 §b{$reason}", "info", "server");
				$this->plugin->ssm($this->myprefix."举报者邮箱 §d{$this->plugin->getEmailFromPlayer($sname)}", "info", "server");
				$this->plugin->ssm($this->myprefix."-------------------", "info", "server");
				$this->plugin->ssm($this->myprefix."处理该工单请输入 §6工单 查询 §f找到工单ID, 然后处理工单.", "info", "server");
				
				$this->pconfig->setNested(strtolower($sname).".report_times", $this->pconfig->get(strtolower($sname))["report_times"]+1);
				$this->pconfig->setNested(strtolower($sname).".remaining_reports", $this->pconfig->get(strtolower($sname))["remaining_reports"]-1);
				$this->pconfig->save();
				$wating_result_msg = str_replace("{player}", $sname, $this->plugin->config()->get("举报回馈信息"));
				$wating_result_msg = str_replace("{reportplayer}", $was_report_player, $wating_result_msg);
				$this->sendMessage($sender, $wating_result_msg);
			}
		}
		return true;
	}
	
	
	public static function getCommandPermission(string $cmd)
	{
		$cmds = 
		[
			"help"     => [self::PERMISSION_ALL],
			"type"     => [self::PERMISSION_ALL],
			// "撤销举报" => [self::PERMISSION_ALL],
		];
		
		$cmd = strtolower($cmd);
		return isset($cmds[$cmd]) ? $cmds[$cmd] : "admin";
	}
	
	public static function getHelpMessage() : array
	{
		return 
		[
			"help"   => "用法: §d/§6{cmd} §f<§e玩家名§f> <§eID§f> <§e原因§f> <§e你的邮箱§f>  举报一个玩家",
			"type"   => "用法: §d/§6{cmd} 类型                             §f查询存在的举报类型",
			// "撤销举报" => "用法: §d/§6{cmd} 撤销举报 §f<§e玩家名§f>                        撤销一个之前你举报玩家名",
		];
	}
	
	
}
?>