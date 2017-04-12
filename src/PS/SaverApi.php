<?php

namespace PS;

use pocketmine\math\Vector3;
use pocketmine\level\Level;
use pocketmine\utils\Config;
use pocketmine\block\Block;
use pocketmine\Server;

class SaverApi {
	
	const DIR_NOT_EXIST = -1;
	const SUCCES = 1;
	const NOT_EDIT_JSON_SAVED = 0;

	public function getServer() {
		return Server::getInstance();
	}

	public function saveOnJson(Vector3 $a,Vector3 $b,Level $level,string $dir,string $name) {
		if(is_dir($dir)) {
			$value = $this->getValueOfDirection($a,$b);
			$json = new Config($dir.$name.'.json',Config::JSON,[]);
			$json->save();
			$blocks = [];
			$g = $a;
			$key = true;
			while($key) {
				$blocks = $this->addBlockToArray($level->getBlock($g),$a,$blocks);
				if($g->x != $b->x) {
					$g->x += $value['x'];
				}else
					 if($g->z != $b->z) {
					$g->z += $value['z'];
					$g->x = $a->x;
				}else
					 if($g->y != $a->y) {
					$g->y += $value['y'];
					$g->x = $a->x;
					$g->z = $a->z;
				}else {
					$key = false;
				}
			}
			$json->setAll($blocks);
			$json->set('info','EDIT PHP FOR POCKETMINE');
			$json->save();
			return self::SUCCES;
		}
		return self::DIR_NOT_EXIST;
	}

	private function getValueOfDirection($a,$b) {
		$val = [];

		if($a->y < $b->y) {
			$val['y'] = 1;
		}else {
			$val['y'] = -1
		}

		if($a->z < $b->z) {
			$val['z'] = 1;
		}else {
			$val['z'] = -1;
		}

		if($a->x < $b->x) {
			$val['x'] = 1;
		}else {
			$val['x'] = -1;
		}
		return $val;
	}

	private function addBlockToArray($block,$a,$array) {
		$id = $block->getId();
		$meta = $block->getDamage();
		$x = $block->x - $a->x;
		$y = $block->y - $a->y;
		$z = $block->z - $a->z;
		array_push($blocks,[
          'x'=>$x,
          'z'=>$z,
          'y'=>$y,
          'id'=>$id,
          'meta'=>$meta
           ]);
		return $blocks;
	}

	public function extractJsonToWorld(string $emp,Level $level,Vector3 $to,bool $del = false) {
		if(is_file($emp)) {
			$file = pathinfo($emp);
			if($file['extension'] == 'json') {
				$json = new Config($emp);
				if($json->get('info') == 'EDIT PHP FOR POCKETMINE') {
					$json->remove('info');
					$this->extractNow($json,$level,$to);
					if($del) {
						unlink($emp);
						unset($json);
					}else {
						$json->set('info','EDIT PHP FOR POCKETMINE');
						$json->save();
						unset($json);
					}
					return self::SUCCES;
				}
			}
		}
		return self::NOT_EDIT_JSON_SAVED;
	}

	private function extractNow($json,$level,$to) {
		$blocks = $json->getAll();
		foreach($blocks as $block) {
			$pos = new Vector3($to->x + $block['x'],$to->y + $block['y'],$to->z + $block['z']);
			$level->setBlock($pos,new Block($block['id'],$block['meta']));
		}
	}
}