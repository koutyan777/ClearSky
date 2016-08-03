<?php
namespace pocketmine\inventory;

use pocketmine\entity\MinecartChest;

class MinecartChestInventory extends EntityInventory{

	/**
	 * @return MinecartChest
	 */
	public function getHolder(){
		return $this->holder;
	}
}