<?php

namespace finixbase123\inventory;

use finixbase123\Bag;
use pocketmine\block\BlockIds;
use pocketmine\inventory\BaseInventory;
use pocketmine\inventory\ContainerInventory;
use pocketmine\math\Vector3;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\tile\Spawnable;

class BagInventory extends ContainerInventory
{
    private $vector;
    protected $title;
    public function __construct(Vector3 $vector, string $title)
    {
        $this->title = $title;
        parent::__construct($vector);
    }

    public function onOpen(Player $player):void
    {
        BaseInventory::onOpen($player);

        $this->vector = $player->add(0, 4)->floor();

        $x = $this->vector->x;
        $y = $this->vector->y;
        $z = $this->vector->z;

        for($i= 0; $i <= 1; $i++) {
            $pk = new UpdateBlockPacket();
            $pk->x = $x;
            $pk->y = $y;
            $pk->z = $z + $i;
            $pk->blockRuntimeId = RuntimeBlockMapping::toStaticRuntimeId(BlockIds::CHEST);
            $pk->flags = UpdateBlockPacket::FLAG_ALL_PRIORITY;
            $player->sendDataPacket($pk);

            $pk = new BlockActorDataPacket();
            $pk->x = $x;
            $pk->y = $y;
            $pk->z = $z + $i;
            $pk->namedtag = (new NetworkLittleEndianNBTStream())->write(new CompoundTag("", [
                new StringTag("id", "Chest"),
                new IntTag("x", $x),
                new IntTag("y", $y),
                new IntTag("z", $z + $i),
                new StringTag("CustomName", $this->title),
                new IntTag("pairz", $z + (1 - $i)),
                new IntTag("pairx", $x)

            ]));
            $player->sendDataPacket($pk);
        }

        Bag::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($player, $x, $y, $z): void
        {
            $pk = new ContainerOpenPacket();
            $pk->x = $x;
            $pk->y = $y;
            $pk->z = $z;
            $pk->windowId = $player->getWindowId($this);
            $player->sendDataPacket($pk);

            $this->sendContents($player);
        }), 10);

    }

    public function onClose(Player $player):void
    {
        BaseInventory::onClose($player);

        $x = $this->vector->x;
        $y = $this->vector->y;
        $z = $this->vector->z;

        $block = $player->getLevel()->getBlock($this->vector);

        for($i = 0; $i <= 1; $i++)
        {
            $pk = new UpdateBlockPacket();
            $pk->x = $x;
            $pk->y = $y;
            $pk->z = $z + $i;
            $pk->blockRuntimeId = RuntimeBlockMapping::toStaticRuntimeId($block->getId(), $block->getDamage());
            $pk->flags = UpdateBlockPacket::FLAG_ALL_PRIORITY;
            $player->sendDataPacket($pk);

            $tile = $player->getLevel()->getBlock($this->vector);
            if($tile instanceof Spawnable)
            {
                $player->sendDataPacket($tile->createSpawnPacket());
            }else{
                $pk = new BlockActorDataPacket();
                $pk->x = $x;
                $pk->y = $y;
                $pk->z = $z + $i;
                $pk->namedtag = (new NetworkLittleEndianNBTStream())->write(new CompoundTag());
                $player->sendDataPacket($pk);
            }
        }
    }

    public function getName() : string
    {

        return $this->title;
    }

    public function getNetworkType():int
    {

        return WindowTypes::CONTAINER;
    }

    public function getDefaultSize() : int
    {
        return 54;
    }
}
