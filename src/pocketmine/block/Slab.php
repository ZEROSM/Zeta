<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;

abstract class Slab extends Transparent{

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	abstract public function getDoubleSlabId() : int;

	public function canBePlacedAt(Block $blockReplace, Vector3 $clickVector, int $face, bool $isClickedBlock) : bool{
		if(parent::canBePlacedAt($blockReplace, $clickVector, $face, $isClickedBlock)){
			return true;
		}

		if($blockReplace->getId() === $this->getId() and $blockReplace->getVariant() === $this->getVariant()){
			if(($blockReplace->getDamage() & 0x08) !== 0){ //Trying to combine with top slab
				return $clickVector->y <= 0.5 or (!$isClickedBlock and $face === Facing::UP);
			}else{
				return $clickVector->y >= 0.5 or (!$isClickedBlock and $face === Facing::DOWN);
			}
		}

		return false;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		$this->meta &= 0x07;
		if($face === Facing::DOWN){
			if($blockClicked->getId() === $this->id and ($blockClicked->getDamage() & 0x08) === 0x08 and $blockClicked->getVariant() === $this->getVariant()){
				$this->getLevel()->setBlock($blockClicked, BlockFactory::get($this->getDoubleSlabId(), $this->getVariant()), true);

				return true;
			}elseif($blockReplace->getId() === $this->id and $blockReplace->getVariant() === $this->getVariant()){
				$this->getLevel()->setBlock($blockReplace, BlockFactory::get($this->getDoubleSlabId(), $this->getVariant()), true);

				return true;
			}else{
				$this->meta |= 0x08;
			}
		}elseif($face === Facing::UP){
			if($blockClicked->getId() === $this->id and ($blockClicked->getDamage() & 0x08) === 0 and $blockClicked->getVariant() === $this->getVariant()){
				$this->getLevel()->setBlock($blockClicked, BlockFactory::get($this->getDoubleSlabId(), $this->getVariant()), true);

				return true;
			}elseif($blockReplace->getId() === $this->id and $blockReplace->getVariant() === $this->getVariant()){
				$this->getLevel()->setBlock($blockReplace, BlockFactory::get($this->getDoubleSlabId(), $this->getVariant()), true);

				return true;
			}
		}else{ //TODO: collision
			if($blockReplace->getId() === $this->id){
				if($blockReplace->getVariant() === $this->meta){
					$this->getLevel()->setBlock($blockReplace, BlockFactory::get($this->getDoubleSlabId(), $this->getVariant()), true);

					return true;
				}

				return false;
			}else{
				if($clickVector->y > 0.5){
					$this->meta |= 0x08;
				}
			}
		}

		if($blockReplace->getId() === $this->id and $blockClicked->getVariant() !== $this->getVariant()){
			return false;
		}

		return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function getVariantBitmask() : int{
		return 0x07;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{

		if(($this->meta & 0x08) > 0){
			return new AxisAlignedBB(0, 0.5, 0, 1, 1, 1);
		}else{
			return new AxisAlignedBB(0, 0, 0, 1, 0.5, 1);
		}
	}
}
