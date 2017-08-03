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

use iTXTech\SimpleFramework\Scheduler\AsyncTask;

class TSSMTask extends AsyncTask{
	const API_PREFIX = "/data/data/com.termux/files/usr/libexec/termux-api ";

	private $shutdown;
	/** @var \Threaded */
	private $queue;

	public function __construct(){
		$this->shutdown = false;
		$this->queue = new \Threaded;
	}

	public function shutdown(){
		$this->shutdown = true;
	}

	public function getInfo(){
		return $this->queue->shift();
	}

	public function onRun(){
		while(!$this->shutdown){
			$this->process(TSSM::STATUS_BATTERY, self::API_PREFIX . "BatteryStatus");
			$this->process(TSSM::STATUS_TEMP, self::API_PREFIX . "SensorTemp");
			$this->process(TSSM::STATUS_HUMIDITY, self::API_PREFIX . "SensorHumidity");
			$this->process(TSSM::STATUS_PRESSURE, self::API_PREFIX . "SensorPressure");
			$this->process(TSSM::STATUS_LIGHT, self::API_PREFIX . "SensorLight");
			sleep(1);
		}
	}

	private function process(int $type, string $cmd){
		exec($cmd, $output);
		$info = implode("\n", $output);
		$json = json_decode($info, true);
		if(is_array($json)){
			$json["TSSMType"] = $type;
			$this->queue[] = json_encode($json);
		}
	}
}
