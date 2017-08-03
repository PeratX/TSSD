<?php

/**
 * TSSM
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PeratX
 */

namespace PeratX\TSSM;

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\TextFormat;
use iTXTech\SimpleFramework\Framework;
use iTXTech\SimpleFramework\Module\Module;

class TSSM extends Module{
	const STATUS_BATTERY = 0;
	const STATUS_TEMP = 1;
	const STATUS_HUMIDITY = 2;
	const STATUS_PRESSURE = 3;
	const STATUS_LIGHT = 4;

	/** @var TSSMTask */
	private $task;

	/** @var [][] */
	private $data;

	public function load(){
		Framework::$usleep = 1000000;
		$this->framework->getScheduler()->scheduleAsyncTask($this->task = new TSSMTask());
	}

	public function unload(){
		$this->task->shutdown();
	}

	public function doTick(int $currentTick){
		while(($info = $this->task->getInfo()) != null){
			$info = json_decode($info, true);
			$this->data[$info["TSSMType"]] = $info;
		}
		Logger::info("\x1bc电量：" . $this->getBatteryPer() . "\t\t" . "室温：" . $this->getTemp() . " \t\t" . "湿度：" . $this->getHumidity() . "\n"
			. "光照：" . $this->getLight() . "\t\t" . "气压：" . $this->getPressure() . "\t\t" . "电池温度：" . $this->getBatteryTemp());
	}

	private function getBatteryPer(): string{
		if(($data = $this->getData(self::STATUS_BATTERY)) !== null){
			$per = round($data["percentage"]);
			$prefix = TextFormat::GREEN;
			if($per <= 20){
				$prefix = TextFormat::RED;
			}elseif($per <= 50){
				$prefix = TextFormat::YELLOW;
			}
			return $prefix . $per . TextFormat::WHITE;
		}
		return "Unknown";
	}

	private function getBatteryTemp(): string{
		if(($data = $this->getData(self::STATUS_BATTERY)) !== null){
			$temp = round($data["temperature"]);
			$prefix = TextFormat::RED;
			if($temp <= 30){
				$prefix = TextFormat::GREEN;
			}elseif($temp <= 40){
				$prefix = TextFormat::YELLOW;
			}
			return $prefix . $temp . TextFormat::WHITE;
		}
		return "Unknown";
	}

	private function getTemp(): string{
		if(($data = $this->getData(self::STATUS_TEMP)) !== null){
			$temp = round($data["data"], 2);
			$prefix = TextFormat::RED;
			if($temp <= 26){
				$prefix = TextFormat::GREEN;
			}elseif($temp <= 30){
				$prefix = TextFormat::YELLOW;
			}elseif($temp <= 36){
				$prefix = TextFormat::DARK_PURPLE;
			}
			return $prefix . $temp . TextFormat::WHITE;
		}
		return "Unknown";
	}

	private function getHumidity(): string{
		if(($data = $this->getData(self::STATUS_HUMIDITY)) !== null){
			$humidity = round($data["data"], 2);
			$prefix = TextFormat::RED;
			if($humidity <= 70){
				$prefix = TextFormat::GREEN;
			}elseif($humidity <= 80){
				$prefix = TextFormat::YELLOW;
			}
			return $prefix . $humidity . TextFormat::WHITE;
		}
		return "Unknown";
	}

	private function getLight(): string{
		if(($data = $this->getData(self::STATUS_LIGHT)) !== null){
			$light = round($data["data"], 2);
			return $light . TextFormat::WHITE;
		}
		return "Unknown";
	}

	private function getPressure(): string{
		if(($data = $this->getData(self::STATUS_PRESSURE)) !== null){
			$pressure = round($data["data"], 2);
			return $pressure . TextFormat::WHITE;
		}
		return "Unknown";
	}

	private function getData(int $type){
		if(isset($this->data[$type])){
			return $this->data[$type];
		}
		return null;
	}
}