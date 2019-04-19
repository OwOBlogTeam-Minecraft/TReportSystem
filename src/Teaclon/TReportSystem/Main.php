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

// 插件: TReportSystem;
// 功能: 举报系统;
// 作者: 锤子(Teaclon/Tommy131);
// 创建日期: 2018-01-02;
// 完成日期: 2018-01-03;
// 当前版本: v2.2.1;

/*
 * -- 插件已基本完成.
 * -- 使用方法: 
 * ------------- | 举报  查看有关"举报"功能的信息
 * ------------- | 工单  查看有关"工单"功能的信息
 * ------------- | 反馈  查看有关"反馈"功能的信息
*/


namespace Teaclon\TReportSystem;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

use pocketmine\command\CommandSender;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerInteractEvent;

use pocketmine\item\DiamondSword;
use pocketmine\item\IronSword;



class Main extends \pocketmine\plugin\PluginBase implements \pocketmine\event\Listener
{
	const PLUGIN_VERSION       = "2.4.0";
	
	const STRING_PRE           = "TReportSystem";
	const UPDATE_PRE           = self::NORMAL_PRE."§a UPDATE §e>§f ";
	const WARNING_PRE          = "§e".self::STRING_PRE." §r§e>§f ";
	const ERROR_PRE            = "§c".self::STRING_PRE." §r§e>§f ";
	const NORMAL_PRE           = "§b".self::STRING_PRE." §r§e>§f ";
	
	public static $instance    = null;
	private $mypath            = null;
	private $server            = null;
	private $logger            = null;
	private $tsapi             = null;
	private $config            = null;
	
	
	private $report, $pconfig, $report_temp, $temp;
	public $bancheck_status = [];
	public $bantime = 180;
	
	const HANGUP = 0;
	const CHEAT  = 1;
	const DISALLOWRULES = 2;
	const BULLYPLAYERS = 3;
	const DESTROYSERVER = 4;
	const MALICIOUSBRUSHSCREEN = 5;
	
	
	public $bantype = 
	[
		self::HANGUP                => "长时间挂机",
		self::CHEAT                 => "作弊",
		self::DISALLOWRULES         => "不遵守游戏规则",
		self::BULLYPLAYERS          => "欺负玩家",
		self::DESTROYSERVER         => "毁坏服务器公物",
		self::MALICIOUSBRUSHSCREEN  => "恶意刷屏"
	];
	
	public function onLoad()
	{
		$this->ssm(self::NORMAL_PRE."举报系统 插件初始化...", "info", "server");
		$this->server = $this->getServer();
		$this->logger = $this->getServer()->getLogger();
		$this->mypath = $this->getDataFolder(); if(!is_dir($this->mypath)) mkdir($this->mypath, 0777, true);
	}
	
	
	public function onEnable()
	{
		if(!$this->server->getPluginManager()->getPlugin("TSeriesAPI"))
		{
			$this->ssm(self::NORMAL_PRE."§c服务器无法找到所依赖的插件, 将无法智能判断核心环境!");
			$this->ssm(self::NORMAL_PRE."§c本插件已卸载.");
			$this->server->getPluginManager()->disablePlugin($this);
			return null;
		}
		else $this->tsapi = $this->server->getPluginManager()->getPlugin("TSeriesAPI")->setMeEnable($this);
		
		
		$this->config = new Config($this->getDataFolder()."Config.yml", Config::YAML, 
		[
			"本配置文件请不要随意更改!" => "特别是信用值重置日期, 请不要更改!",
			"最大信用值"       => 100,
			"信用值增加数"     => 1,
			"每日举报次数"     => 10,
			"被举报扣除信用值" => 1,
			"信用值过低不能进入服务器" => true,
			"过低信用值限度"   => 50,
			"举报核实奖励"     => true,
			"举报奖励类型"     => "money",
			"举报奖励物品"     => 1000,
			"举报回馈信息"     => "§e玩家{player}你好, 我们已经收到了你对玩家{reportplayer}的举报, 我们尽快在48小时内给你一个满意的答复, 非常感谢你对我们的信任. 如果举报成功你将会获得小礼品一份~",
			"封禁信息"         => "§c玩家§e{player}§c你好, 有玩家举报你的某些行为违反了服务器规则, 现以判定你{min}分钟不允许游玩本服务器, 谢谢你的配合.",
			"信用值过低信息"   => "§c玩家§e{player}§c你好, 由于你的信用值过低, 我们已将你封禁. 解封结果请联系本服务器管理员或者等待系统自动解封.",
			"信用值重置日期"   => strtotime(date("Y-m-d")) * 7,
			"举报类型"         => $this->bantype,
			"工单管理员"       => []
		]);
		$this->report      = new Config($this->getDataFolder()."ReportList.yml", Config::YAML, []); // 举报列表;
		$this->pconfig     = new Config($this->getDataFolder()."PlayersConfig.yml", Config::YAML, []); // 玩家配置文件;
		$this->report_temp = new Config($this->getDataFolder()."report_temp.yml", Config::YAML, []); // 举报缓存文件;
		$this->temp        = new Config($this->getDataFolder()."temp.json", Config::JSON, []); // 缓存文件;
		
		$this->ResetCreditValue();
		
		$this->ssm(self::NORMAL_PRE."§d-----------------------------------------------------", "info", "server");
		$this->ssm(self::NORMAL_PRE."Copyright (c) 2017-2018 TeaTech All right Reserved.", "info", "server");
		$this->ssm(self::NORMAL_PRE."Author: Teaclon", "info", "server");
		$this->ssm(self::NORMAL_PRE."Thanks you to use my plugin.", "info", "server");
		$this->ssm(self::NORMAL_PRE."§d-----------------------------------------------------", "info", "server");
		
		$this->tsapi->getCommandManager()->registerCommand(new command\AdminManagementCommand($this));
		$this->tsapi->getCommandManager()->registerCommand(new command\FeedbackCommand($this));
		$this->tsapi->getCommandManager()->registerCommand(new command\MainCommand($this));
		$this->tsapi->getCommandManager()->registerCommand(new command\MyInfoCommand($this));
		$this->tsapi->getCommandManager()->registerCommand(new command\SetEMailCommand($this));
		$this->tsapi->getCommandManager()->registerCommand(new command\WorkOrderCommand($this));
		$this->tsapi->getTaskManager()->registerTask("scheduleRepeatingTask", new task\ResetReportTimesTask($this), 20 * 86400, \false);
		
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	
	public function onDisable()
	{
		$this->ssm("举报系统插件已卸载.", "info", "server");
		if(isset($this->temp))
		{
			$this->temp()->setAll();
			$this->temp()->save();
		}
	}
	
	
	
	
	public function onPlayerJoin(PlayerJoinEvent $e)
	{
		$p = $e->getPlayer();
		$n = strtolower($p->getName());
		if(!$this->isPlayerSetEmail($n)) $p->sendMessage(self::NORMAL_PRE."§c检测到你没有设置邮箱, 请输入 §d/§6邮箱 §f<§e你的邮箱§f> §c设置邮箱!");
		$p->sendMessage(self::NORMAL_PRE."§e玩家§a{$n}§e你好, 本服务器已安装了举报系统. 你可以检举这个服务器的任何一位可疑玩家, 输入指令 §d/§6举报 §e查看详情.");
	}
	
	public function onPlayerLogin(PlayerPreLoginEvent $e)
	{
		$p = $e->getPlayer();
		$n = strtolower($p->getName());
		
		if(!$this->pconfig()->exists($n))
		{
			$this->pconfig()->setNested($n.".credit_value", $this->config->get("最大信用值"));
			$this->pconfig()->setNested($n.".remaining_reports", $this->config->get("每日举报次数"));
			$this->pconfig()->setNested($n.".report_times", 0);
			$this->pconfig()->setNested($n.".was_report_times", 0);
			$this->pconfig()->setNested($n.".email", \null);
			$this->pconfig()->save();
			return null;
		}
		
		if($this->report()->exists($n) && ($this->report()->get($n)["isBan"] == true))
		{
			$p->close("", str_replace("{player}", $n, $this->config()->get("封禁信息")));
		}
		
		if(($this->config()->get("信用值过低不能进入服务器") == true) && ($this->pconfig()->get($n)["credit_value"] < $this->config()->get("过低信用值限度")))
		{
			$p->close("", str_replace("{player}", $n, $this->config()->get("信用值过低信息")));
		}
	}
	
	public function onPlayerTouch(PlayerInteractEvent $e)
	{
		$p = $e->getPlayer();
		$n = strtolower($p->getName());
		if($e->getItem() instanceof IronSword)
		{
			if(is_int($this->bancheck_status[$n]))
			{
				$work_order_info = $this->report_temp->get($this->bancheck_status[strtolower($sname)]);
				$this->addBanPlayer($work_order_info["被举报者"], 180, $work_order_info["举报类型"], $work_order_info["提交者"]);
				$this->sendMessage($sender, "该玩家已被封禁180分钟, 180分钟过后将会自动解封该玩家.");
			}
		}
		elseif($e->getItem() instanceof DiamondSword)
		{
			if(is_int($this->bancheck_status[$n])) unset($this->bancheck_status[$n]);
			$this->sendMessage($sender, "你选择不处罚该玩家.");
		}
		
		// TODO 移除钻石剑以及铁剑;
	}
	
	
#!-------------------------------------------------------------------------------------------!# ---> Config Manager <<
	
	
	
	public function add_report_player(string $player)
	{
		$player = strtolower($player);
		if(!$this->report()->exists($player)) $this->report()->set($player, 0);
		else $this->report()->set($player, $this->report()->get($player));
		$this->report()->save();
		if($this->pconfig()->exists($player))
		{
			$this->pconfig()->setNested($player.".was_report_times", $this->pconfig()->get($player)["was_report_times"]+1);
			$this->pconfig()->setNested($player.".Whistleblower", $Whistleblower);
			$this->pconfig()->setNested($player.".BanDate", date("Y-m-d H:i:s"));
			$this->pconfig()->setNested($player.".ReportType", $type);
			$this->pconfig()->save();
		}
		return true;
	}
	
	
	public function addBanPlayer(string $player, int $time, string $type, string $Whistleblower)
	{
		$player = strtolower($player);
		if($this->temp()->exists($player)) return 2; // 玩家已被封禁;
		else
		{
			if(!isset($this->bantype[$type])) return 3; // 类型不存在;
			$this->temp()->setNested($player.".BanTime", $time); // 分钟;
			$this->temp()->setNested($player.".isBan", true);
			$this->temp()->save();
			
			if($this->pconfig()->exists($player))
			{
				$this->pconfig()->setNested($player.".credit_value", $this->pconfig()->get($player)["credit_value"] - $this->config->get("被举报扣除信用值"));
				$this->pconfig()->save();
			}
			// $this->getServer()->getScheduler()->scheduleRepeatingTask(new BanWasReporterTask($this), 1 * 20 * 60); // 1分钟运行一次;
			$this->tsapi->getTaskManager()->registerTask("scheduleRepeatingTask", new task\BanWasReporterTask($this), 20 * 60, \false);
			return true;
		}
		return false; // 未知错误;
	}
	
	
	public function DelBanPlayer(string $player, int $time)
	{
		$player = strtolower($player);
		if(!$this->temp()->exists($player)) return 2; // 玩家未被封禁;
		else
		{
			$this->temp()->setNested($player.".BanTime", null);
			$this->temp()->setNested($player.".isBan", false);
			$this->temp()->save();
			return true;
		}
		return false; // 未知错误;
	}
	
	public function getWorkOrder(array $config, CommandSender $sender) : array
	{
		$status = ($config["状态"])? "§a已处理" : "§c未处理";
		$result = ($config["处理结果"] == null)? "§c暂未处理" : $config["处理结果"];
		$handle_prople = ($config["处理人"] == null)? "§c暂无处理人" : $config["处理人"];
		
		$re1 = ["时间: §e{$config["时间"]}", "工单类型: §d{$config["工单类型"]}", "提交者: §6{$config["提交者"]}"];
		$re2 = ($config["工单类型"] === "玩家举报")
				? ["被举报者: §c{$config["被举报者"]}", "举报类型: §b{$config["举报类型"]}", "原因: {$config["原因"]}"] : ["建议: {$config["原因"]}"];
		$re3 = ["处理结果: {$result}", "处理人: {$handle_prople}", "工单状态: {$status}"];
		return array_merge($re1, $re2, $re3);
	}
	
	//                                提交者;             提交的信息;    提交的类型;         被举报者;                   举报类型;
	public function addReport_Temp(string $Whistleblower, string $msg, int $type, string $was_reporter = null, int $bantype = null) // 举报玩家或者建议反馈;
	{
		$Whistleblower = strtolower($Whistleblower);
		$was_reporter = strtolower($was_reporter);
		$typearr = ["建议反馈", "玩家举报"]; // 0: 建议反馈; 1: 玩家举报;
		if(!isset($typearr[$type])) return false; // 类型不存在;
		else
		{
			$id = mt_rand(1000, 9999);
			// if(!isset($this->report_temp()->getAll()[$typearr[$type]][$id]))$id++;
			
			
			$this->report_temp()->setNested($id.".时间", date("Y-m-d"));
			$this->report_temp()->setNested($id.".提交者", $Whistleblower);
			$this->report_temp()->setNested($id.".工单类型", $typearr[$type]);
			if(isset($was_reporter) && isset($bantype))
			{
				$this->report_temp()->setNested($id.".被举报者", $was_reporter);
				$this->report_temp()->setNested($id.".举报类型", $this->bantype[$bantype]);
			}
			$this->report_temp()->setNested($id.".原因", $msg);
			$this->report_temp()->setNested($id.".处理结果", null);
			$this->report_temp()->setNested($id.".处理人", null);
			$this->report_temp()->setNested($id.".状态", false);
			$this->report_temp()->save();
			return true;
		}
		return null;
	}
	
	public function ResetCreditValue()
	{
		if(strtotime(date("Y-m-d")) == $this->config()->get("信用值重置日期"))
		{
			$this->notice(self::NORMAL_PRE."正在恢复本服务器的所有玩家的信用值...");
			$pconfig = $this->pconfig()->getAll();
			if(count($list) == null) return;
			foreach($pconfig as $name => $info)
			{
				if($info["credit_value"] != $this->config()->get("最大信用值"))
				{
					$this->pconfig()->setNested($name.".credit_value", $this->config()->get("最大信用值"));
					$this->pconfig()->save();
					$this->ssm(self::NORMAL_PRE."玩家{$name}的信用值已恢复.", "info", "server");
				}
			}
		}
	}
	
	public function rewardPlayer(Player $player)
	{
		$type = $this->config()->get("举报奖励类型");
		if(!in_array($type, ["money", "item"]))
		{
			throw \Exception("配置文件参数 \"举报奖励类型\" 无效! 请填写 [\"money\", \"item\"] 中的一个!");
			return \false;
		}
		if($type === "money" && ($economyAPI = $this->server->getPluginManager()->getPlugin("EconomyAPI")))
		{
			if(!$economyAPI || (isset($economyAPI) && !$economyAPI->isEnabled()))
			{
				throw \Exception("无法执行反馈奖励! 本服务器不存在插件 EconomyAPI !");
				return \false;
			}
			$economyAPI->addMoney($player->getName(), (int) $this->config()->get("举报奖励物品"), \true);
			$player->sendMessage(self::NORMAL_PRE."玩家你好, 我们采纳了你的举报/反馈建议, 我们已将小礼品发送至你当前的账户中, 请注意查收~");
			$player->sendMessage("§rServer gave you some money.");
			return \true;
		}
		elseif($type === "item")
		{
			$this->server->dispatchCommand(new \pocketmine\command\ConsoleCommandSender(), "give {$player->getName()} ".$this->config()->get("举报奖励物品"));
			$player->sendMessage(self::NORMAL_PRE."玩家你好, 我们采纳了你的举报/反馈建议, 我们已将小礼品发送至你当前的账户中, 请注意查收~");
			return \true;
		}
		else
		{
			throw \Exception("未知错误!");
			return \false;
		}
	}
	
	
	
#!-------------------------------------------------------------------------------------------!# ---> Email Manager <<
	public function isPlayerSetEmail(string $player)
	{
		$player = strtolower($player);
		return ($this->pconfig()->exists($player) && isset($this->pconfig()->get($player)["email"]));
	}
	
	public function setEmailFromPlayer(string $player, $email)
	{
		$player = strtolower($player);
		if(!$this->isPlayerSetEmail($player))
		{
			$this->pconfig()->setNested($player.".email", $email);
			$this->pconfig()->save();
			return true;
		}
		else return false;
		
	}
	
	public function getEmailFromPlayer(string $player)
	{
		$player = strtolower($player);
		return $this->isPlayerSetEmail($player)? $this->pconfig()->get($player)["email"] : false;
	}
	
	
	public function check_email_format($email)
	{
		$result = preg_match("/^[a-z0-9]+[\._-]?[a-z0-9]+@[a-z0-9]+-?[a-z0-9]*(\.[a-z0-9]+-?[a-z0-9]+)?\.(com|org|net|com.cn|org.cn|net.cn|cn)$/i", $email);
		return ($result)? $email : false;
	}



#---[BASIC FUNCTIONS]--------------------------------------------------------------------------------------------#
	/**
		用法: self::ssm(信息, 日志记录等级, 发送形式)
	**/
	public final function ssm($msg, $level = "info", $type = "logger")
	{
		if(($msg === "") || ($level === "") || ($type === ""))
		{
			Server::getInstance()->getLogger()->error(self::NORMAL_PRE."[LOGGER] Error Usage(0010)");
		}
		elseif(!\in_array($level, ["info", "warning", "error", "notice", "debug", "alert", "critical", "emergency"]))
		{
			Server::getInstance()->getLogger()->error(self::NORMAL_PRE."[LOGGER] Error Usage(0015)");
		}
		elseif(!\in_array($type, ["server", "logger"]))
		{
			Server::getInstance()->getLogger()->error(self::NORMAL_PRE."[LOGGER] Error Usage(0020)");
		}
		else
		{
			$color = ($level === "notice") ? "§r§b" : null;
			if($type === "server") Server::getInstance()->getLogger()->$level($color.$msg);
			elseif($type === "logger") $this->getLogger()->$level($color.$msg);
		}
	}
	
	public static final function stopThread($plugin_name, $msg, $error_code = "")
	{
		Server::getInstance()->getLogger()->error("§c§l服务器已崩溃, 正在关闭服务器.");
		Server::getInstance()->getLogger()->error("§c§l服务器已崩溃, 正在关闭服务器.");
		Server::getInstance()->forceshutdown();
		if($error_code === "") $error_code = "NULL";
		exit("ERROR: >> Plugin: {$plugin_name}; Cause: {$msg}; Code: {$error_code}".PHP_EOL);
	}
	
	public static final function getInstance()
	{
		return self::$instance;
	}
	
	public final function getTSApi() : \Teaclon\TSeriesAPI\Main
	{
		return $this->tsapi;
	}

#---[CONFIG FUNCTIONS]--------------------------------------------------------------------------------------------#
	public function getPermission(string $player_name)
	{
		return in_array($player_name, $this->config()->get("工单管理员"));
	}
	public final function config() : Config
	{
		return $this->config;
	}
	public final function pconfig() : Config
	{
		return $this->pconfig;
	}
	public final function report() : Config
	{
		return $this->report;
	}
	public final function report_temp() : Config
	{
		return $this->report_temp;
	}
	public final function temp() : Config
	{
		return $this->temp;
	}
	
}
?>