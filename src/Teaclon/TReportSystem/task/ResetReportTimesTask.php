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



class ResetReportTimesTask extends PluginTask
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
		
		$pconfig = $this->plugin->pconfig()->getAll();
		if(count($pconfig) <= 0) return null;
		foreach($pconfig as $name => $info)
		{
			if($info["remaining_reports"] === 0)
			{
				$this->plugin->pconfig()->setNested($name.".remaining_reports", $this->plugin->config()->get("每日举报次数"));
				$this->plugin->pconfig()->save();
			}
		}
	}
}
?>