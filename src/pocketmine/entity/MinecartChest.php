<?php

namespace pocketmine\entity;

use pocketmine\Player;
use pocketmine\item\Item as ItemItem;
use pocketmine\inventory\InventoryHolder;
use pocketmine\tile\Container;
use pocketmine\tile\Nameable;
use pocketmine\inventory\MinecartChestInventory;
use pocketmine\level\format\FullChunk;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\IntTag;

class MinecartChest extends Minecart implements InventoryHolder, Container, Nameable{
	
	/** @var MinecartChestInventory */
	protected $inventory;
	const NETWORK_ID = 98;

	public function __construct(FullChunk $chunk, CompoundTag $nbt){
		parent::__construct($chunk, $nbt);
		$this->inventory = new MinecartChestInventory($this);
		
		if(!isset($this->namedtag->Items) or !($this->namedtag->Items instanceof ListTag)){
			$this->namedtag->Items = new ListTag("Items", []);
			$this->namedtag->Items->setTagType(NBT::TAG_Compound);
		}
		
		for($i = 0; $i < $this->getSize(); ++$i){
			$this->inventory->setItem($i, $this->getItem($i));
		}
	}

	public function spawnTo(Player $player){
		$pk = $this->addEntityDataPacket($player);
		$pk->type = self::NETWORK_ID;
		$player->dataPacket($pk);
		Entity::spawnTo($player);
	}

	public function getDrops(){
		return [ItemItem::get(ItemItem::MINECART, 0, 1), ItemItem::get(ItemItem::CHEST, 0, 1)];
	}

	public function saveNBT(){
		$this->namedtag->Items = new ListTag("Items", []);
		$this->namedtag->Items->setTagType(NBT::TAG_Compound);
		for($index = 0; $index < $this->getSize(); ++$index){
			$this->setItem($index, $this->inventory->getItem($index));
		}
	}

	/**
	 *
	 * @return int
	 */
	public function getSize(){
		return 27;
	}

	/**
	 *
	 * @param
	 * $index
	 * 
	 * @return int
	 */
	protected function getSlotIndex($index){
		foreach($this->namedtag->Items as $i => $slot){
			if((int) $slot["Slot"] === (int) $index){
				return (int) $i;
			}
		}
		
		return -1;
	}

	/**
	 * This method should not be used by plugins, use the Inventory
	 *
	 * @param int $index 
	 *
	 * @return Item
	 */
	public function getItem($index){
		$i = $this->getSlotIndex($index);
		if($i < 0){
			return ItemItem::get(ItemItem::AIR, 0, 0);
		}
		else{
			return NBT::getItemHelper($this->namedtag->Items[$i]);
		}
	}

	/**
	 * This method should not be used by plugins, use the Inventory
	 *
	 * @param int $index 
	 * @param Item $item 
	 *
	 * @return bool
	 */
	public function setItem($index, ItemItem $item){
		$i = $this->getSlotIndex($index);
		
		$d = NBT::putItemHelper($item, $index);
		
		if($item->getId() === ItemItem::AIR or $item->getCount() <= 0){
			if($i >= 0){
				unset($this->namedtag->Items[$i]);
			}
		}
		elseif($i < 0){
			for($i = 0; $i <= $this->getSize(); ++$i){
				if(!isset($this->namedtag->Items[$i])){
					break;
				}
			}
			$this->namedtag->Items[$i] = $d;
		}
		else{
			$this->namedtag->Items[$i] = $d;
		}
		
		return true;
	}

	/**
	 *
	 * @return MinecartChestInventory
	 */
	public function getInventory(){
		$this->inventory;
	}

	public function getName(){
		return isset($this->namedtag->CustomName) ? $this->namedtag->CustomName->getValue() : "Minecart with Chest";
	}

	public function hasName(){
		return isset($this->namedtag->CustomName);
	}

	public function setName($str){
		if($str === ""){
			unset($this->namedtag->CustomName);
			return;
		}

		$this->namedtag->CustomName = new StringTag("CustomName", $str);
	}

	public function getSpawnCompound(){
		$c = new CompoundTag("", [
			new StringTag("id", Tile::CHEST),
			new IntTag("x", (int) $this->x),
			new IntTag("y", (int) $this->y),
			new IntTag("z", (int) $this->z),
			new IntTag("entityId", $this->getId())
		]);

		if($this->hasName()){
			$c->CustomName = $this->namedtag->CustomName;
		}

		return $c;
	}
}
