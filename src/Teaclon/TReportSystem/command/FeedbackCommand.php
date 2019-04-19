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


class FeedbackCommand extends BaseCommand
{
	const MY_COMMAND             = "feedback";
	const MY_COMMAND_PEREMISSION = [self::PERMISSION_ALL];
	public $myprefix = Main::NORMAL_PRE;
	private $tsapi   = null;
	
	
	
	
	
	public function __construct(Main $plugin)
	{
		$this->tsapi   = $plugin->getTSApi();
		// CommandName, Description, usage, aliases, overloads;
		$this->init($plugin, self::MY_COMMAND, Main::STRING_PRE."的反馈功能指令", null, ["反馈"], []);
	}
	
	
	
	public function execute(CommandSender $sender, $commandLabel, array $args)
	{
		$sname = $sender->getName();
		if(!$this->plugin->isPlayerSetEmail($sname))
		{
			$this->sendMessage($sender, "§c检测到你没有设置邮箱, 请输入 §d/§6".SetEMailCommand::MY_COMMAND." §f<§e你的邮箱§f> §c设置邮箱.");
			return true;
		}
		if(!isset($args[0]))
		{
			$this->sendMessage($sender, "§e玩家§b{$sname}§e你好, 欢迎使用建议反馈系统. 你可以进行如下操作:");
			$this->sendMessage($sender, "§d/§6".self::MY_COMMAND." §f<§e你的建议§f>  进行建议反馈.");
			$this->sendMessage($sender, "§d/§6".self::MY_COMMAND." 查询        §f查询我提交的举报/建议.");
			return true;
		}
		if($args[0] === "查询")
		{
			$my_report = [];
			
			foreach($this->plugin->report_temp()->getAll() as $id => $info)
			{
				if($info["提交者"] === strtolower($sname)) $my_report[$id] = $info;
			}
			
			if(count($my_report) === \null)
			{
				$this->sendMessage($sender, "§e玩家§b{$sname}§e你好, 你目前没有提交任何举报/建议~");
				return true;
			}
			$this->sendMessage($sender, "§e玩家§b{$sname}§e你好, 你提交的工单如下:");
			foreach($my_report as $id => $info)
			{
				$this->sendMessage($sender, "§e----§f提交ID §f[§c{$id}§f] 的信息§e----");
				foreach($this->plugin->getWorkOrder($info, $sender) as $msg)
				{
					$this->sendMessage($sender, $msg);
				}
			}
			return true;
		}
		else
		{
			if($this->plugin->temp()->exists($sname) && ($this->plugin->temp()->get($sname)["建议反馈状态"] === true))
			{
				$this->sendMessage($sender, "§e玩家§b{$sname}§e你好, 你今天已经反馈过问题了, 请等明天再来反馈问题吧~");
				return true;
			}
			
			$feedback = $args[0];
			if($this->plugin->addReport_Temp($sname, $feedback, 0))$this->sendMessage($sender, "§a建议反馈成功~ 请等待我们的处理结果吧~");
			$this->plugin->temp()->setNested($sname.".建议反馈状态", true);
			$this->plugin->temp()->save();
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