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

namespace Teaclon\TReportSystem\task;

use pocketmine\Player;
use pocketmine\Server;
use Teaclon\TSeriesAPI\task\PluginTask;


use Teaclon\TReportSystem\Main;



class BanWasReporterTask extends PluginTask
{
	private $plugin;
	
	public function __construct(\Teaclon\TReportSystem\Main $plugin)
	{
		$this->plugin = $plugin;
		parent::__construct($plugin);
	}
	
	
	
	public function me($tick)
	{
		if(!method_exists($this->plugin, "getTSApi"))
		{
			$this->plugin->ssm(Main::NORMAL_PRE."§c服务器无法找到所依赖的插件, 无法使用本插件!", "info", "server");
			$this->plugin->getServer()->getPluginManager()->disablePlugin($this);
			return null;
		}
		
		$list = $this->plugin->temp()->getAll();
		if(count($list) === \null) return;
		foreach($list as $player => $info)
		{
			if(isset($info["BanTime"]) && isset($info["isBan"]))
			{
				if(($info["BanTime"] > 0) && ($info["isBan"] === \true)) $this->plugin->temp()->setNested($player.".BanTime", $info["BanTime"] - 1);
				else
				{
					$this->plugin->temp()->setNested($player.".BanTime", null);
					$this->plugin->temp()->setNested($player.".isBan", false);
					$this->plugin->info(Main::NORMAL_PRE."§f举报系统提醒 §e> §c玩家 §e{$player} §c已被解除封禁, 信用值已扣除.");
					$this->plugin->temp()->save();
					$this->plugin->getServer()->getScheduler()->cancelTask($this->getTaskId());
				}
			}
		}
	}
}
?>