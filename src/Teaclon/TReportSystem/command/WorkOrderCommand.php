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


class WorkOrderCommand extends BaseCommand
{
	const MY_COMMAND             = "workorder";
	const MY_COMMAND_PEREMISSION = [self::PERMISSION_ALL];
	public $myprefix = Main::NORMAL_PRE;
	private $tsapi   = null;
	private $report_temp = null;
	
	
	
	
	
	public function __construct(Main $plugin)
	{
		$this->tsapi       = $plugin->getTSApi();
		$this->report_temp = $plugin->report_temp();
		// CommandName, Description, usage, aliases, overloads;
		$this->init($plugin, self::MY_COMMAND, Main::STRING_PRE."的工单处理指令", null, ["工单"], []);
	}
	
	
	
	public function execute(CommandSender $sender, $commandLabel, array $args)
	{
		$sname = $sender->getName();
		if(($sender instanceof Player) && !$this->plugin->getPermission($sname))
		{
			$this->sendMessage($sender, "§c你没有权限使用这个指令.");
			return \true;
		}
		
		if(!isset($args[0]))
		{
			$this->sendMessage($sender, "§c操作错误. 你可以按照以下格式进行操作:");
			$this->sendMessage($sender, "§d/§6".self::MY_COMMAND." 处理 §f<§e工单ID§f> <§e结果§f>");
			$this->sendMessage($sender, "§d/§6".self::MY_COMMAND." 查询  §f简单的查询工单ID");
			$this->sendMessage($sender, "§d/§6".self::MY_COMMAND." 细查 §f<§e工单ID§f>  指定查询一个工单");
			$this->sendMessage($sender, "§d/§6".self::MY_COMMAND." 删除 §f<§e工单ID§f>  删除一个工单");
			$this->sendMessage($sender, "§d/§6".self::MY_COMMAND." 重载  §f重新读取所有配置文件");
			$this->sendMessage($sender, "空格请用\"§e@§f\"代替.");
			return true;
		}
		
		switch($args[0])
		{
		default:
			$this->sendMessage($sender, "§c操作错误. 请确认你的操作是否存在.");
			return \true;
		break;
		
		
		case "处理":
			if(!isset($args[1]))
			{
				$this->sendMessage($sender, "§c请输入一个有效的工单ID.");
				return true;
			}
			if(!$this->report_temp->exists($args[1]))
			{
				$this->sendMessage($sender, "§c没有找到工单.");
				return \true;
			}
			if(!isset($args[2]))
			{
				$this->sendMessage($sender, "§c请输入该工单的处理结果.");
				return true;
			}
			$work_order_info = $this->report_temp->get($args[1]);
			if(($work_order_info["状态"] === \false) && ($work_order_info["处理结果"] === \null))
			{
				$this->report_temp->setNested($args[1].".处理结果", str_replace("@", " ", $args[2]));
				$this->report_temp->setNested($args[1].".处理人", ($sname === "CONSOLE" && (!$sender instanceof Player))? "系统": $sname);
				$this->report_temp->setNested($args[1].".状态", \true);
				$this->report_temp->save();
				$this->report_temp->reload();
				$work_order_info = $this->report_temp->get($args[1]);
				if($work_order_info["状态"]) $this->sendMessage($sender, "§a成功处理该工单, 回执已发送给提交者 §e{$work_order_info["提交者"]} §a.");
				if($p = $this->plugin->getServer()->getPlayer($work_order_info["提交者"])) $p->sendMessage(Main::NORMAL_PRE."§d玩家你好, 你提交的举报/反馈已被处理, 获取详细情况请输入 /§6反馈 查询 §d.");
				
				if($work_order_info["工单类型"] === "玩家举报")
				{
					$this->plugin->add_report_player($work_order_info["被举报者"]);
					
					if(!$sender instanceof Player)
					{
						$this->sendMessage($sender, "是否封禁被举报者禁止进入服务器(180分钟)? [Yes/No]");
						if((strtolower(trim(fgets(STDIN))) === strtolower("Yes")) || strtolower((trim(fgets(STDIN))) === strtolower("Y")))
						{
							$this->plugin->addBanPlayer($work_order_info["被举报者"], $this->plugin->bantime, $work_order_info["举报类型"], $work_order_info["提交者"]);
							$this->sendMessage($sender, "该玩家已被封禁{$this->plugin->bantime}分钟, {$this->plugin->bantime}分钟过后将会自动解封该玩家.");
						}
						else $this->sendMessage($sender, "你选择不处罚被举报者.");
					}
					else
					{
						$this->sendMessage($sender, "是否封禁被举报者禁止进入服务器(180分钟)? [如果是请手持铁剑点击地面, 不然手持钻石剑点击地面]");
						$this->plugin->bancheck_status[strtolower($sname)] = $args[1];
						// TODO 添加钻石剑以及铁剑;
					}
				}
				$this->sendMessage($sender, "是否奖励该玩家? [Yes/No]");
				if((strtolower(trim(fgets(STDIN))) === strtolower("Yes")) || strtolower((trim(fgets(STDIN))) === strtolower("Y")))
				{
					$this->sendMessage($sender, "你选择奖励该玩家.");
					if($this->plugin->getServer()->getPlayer($work_order_info["提交者"]) instanceof Player)
					$this->plugin->rewardPlayer($this->plugin->getServer()->getPlayer($work_order_info["提交者"]));
				}
				else $this->sendMessage($sender, "你选择不处罚该玩家.");
				
				return true;
			}
			else $this->sendMessage($sender, "§c工单已被处理, 请不要重复处理.");
			return true;
		break;
		
		
		case "查询":
			$this->sendMessage($sender, "正在查找工单...");
			if(count($this->report_temp->getAll()) === 0)
			{
				$this->sendMessage($sender, "没有找到工单.");
				return true;
			}
			
			foreach($this->report_temp->getAll() as $id => $info)
			{
				$this->sendMessage($sender, "§e工单ID: §c{$id}§e; 工单类型: §b{$info["工单类型"]}§e; 处理结果: §f".(($info["处理结果"] == null)? "§c暂未处理" : $info["处理结果"]));
			}
			$this->sendMessage($sender, "输入 §d/§6".self::MY_COMMAND." 细查 §f<§e工单ID§f>  §f指定查询一个工单");
			return true;
		break;
		
		
		case "细查":
			if(!isset($args[1]))
			{
				$this->sendMessage($sender, "§c请输入一个有效的工单ID.");
				return true;
			}
			if($this->report_temp->exists($args[1]))
			{
				$this->sendMessage($sender, "§e----§f工单ID §f[§c{$args[1]}§f] 的信息§e----");
				$work_order_info = $this->report_temp->get($args[1]);
				foreach($this->plugin->getWorkOrder($work_order_info, $sender) as $msg)
				{
					$this->sendMessage($sender, $msg);
				}
				$this->sendMessage($sender, "§d/§6工单 处理 §f{$args[1]} <§e结果§f>");
			}
			else $this->sendMessage($sender, "§c没有找到工单.");
			return true;
		break;
		
		
		case "删除":
			if(!isset($args[1]))
			{
				$this->sendMessage($sender, "§c请输入一个有效的工单ID.");
				return true;
			}
			if($this->report_temp->exists($args[1]))
			{
				$this->report_temp->remove($args[1]);
				$this->report_temp->save();
				$this->sendMessage($sender, "§a成功删除工单ID §d#§e{$args[1]}§a.");
			}
			else $this->sendMessage($sender, "§c没有找到工单.");
			return true;
		break;
		
		
		case "reload":
		case "重载":
			$this->plugin->config()->reload();
			$this->plugin->report()->reload();
			$this->plugin->pconfig()->reload();
			$this->plugin->temp()->reload();
			$this->report_temp->reload();
			$this->sendMessage($sender, "§已重新读取所有配置文件.");
			return true;
		break;
		}
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