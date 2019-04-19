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


class AdminManagementCommand extends BaseCommand
{
	const MY_COMMAND             = "setadmin";
	const MY_COMMAND_PEREMISSION = [self::PERMISSION_ALL];
	public $myprefix = Main::NORMAL_PRE;
	private $tsapi   = null;
	private $config = null;
	
	
	
	
	
	public function __construct(Main $plugin)
	{
		$this->tsapi   = $plugin->getTSApi();
		$this->config = $plugin->pconfig();
		// CommandName, Description, usage, aliases, overloads;
		$this->init($plugin, self::MY_COMMAND, Main::STRING_PRE."的管理页管理指令", null, ["管理员"], []);
	}
	
	
	
	public function execute(CommandSender $sender, $commandLabel, array $args)
	{
		$sname = $sender->getName();
		if(($sender instanceof Player) && (!$this->plugin->getPermission($sname)))
		{
			$this->sendMessage($sender, "§c你没有权限使用这个指令.");
			return true;
		}
		
		if(!isset($args[0]))
		{
			$this->sendMessage($sender, "§c请输入管理员名称.");
			$this->sendMessage($sender, "用法: §d/§6".self::MY_COMMAND." §f<§e管理员名称§f>  添加一个管理员");
			return true;
		}
		
		
		if(!file_exists($this->plugin->getServer()->getDataPath(). "/players/".strtolower($args[0]).".dat"))
		{
			$this->sendMessage($sender, "§c玩家 §e{$args[0]} §c不存在.");
			return true;
		}
		if(!$this->getPermission($args[0]))
		{
			$con = $this->config->getAll();
			$con["工单管理员"][] = $args[0];
			$this->config->set("工单管理员", $con["工单管理员"]);
			$this->config->save();
			$this->sendMessage($sender, "§a成功添加管理员 §e".$args[0]."§a.");
			unset($con);
			return true;
		}
		else
		{
			$con = $this->config->getAll();
			$key = array_search($args[0], $this->config->get("工单管理员"));
			unset($con["工单管理员"][$key]);
			$this->config->set("工单管理员", $con["工单管理员"]);
			$this->config->save();
			$this->sendMessage($sender, "§a已移除管理员 §e".$args[0]."§a.");
			return true;
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