<?php
namespace pocketmine\inventory;

use pocketmine\entity\Entity;

class EntityInventory extends ContainerInventory{
	public function __construct(Entity $entity, $type = InventoryType::CHEST){
		parent::__construct($entity, InventoryType::get($type));
	}

	/**
	 * @return Entity
	 */
	public function getHolder(){
		return $this->holder;
	}
	
	public function getEntity(){
		return $this->getHolder();
	}
}