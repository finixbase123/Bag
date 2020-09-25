<?php

namespace finixbase123\command;

use finixbase123\Bag;
use finixbase123\inventory\BagInventory;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\CommandException;
use pocketmine\item\Item;
use pocketmine\math\Vector3;

class BagCommand extends Command
{

    /**
     * @var Bag
     */
    private $owner;

    public function __construct(Bag $owner, string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        $this->owner = $owner;
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if(!isset($args[0])) {
            $inv = new BagInventory(new Vector3($sender->x, $sender->y, $sender->z), 'My Inventory');
            $inv->setContents(array_map(function (array $array) {

                return Item::jsonDeserialize($array);

            }, $this->owner->db[strtolower($sender->getName())]['items']));
            $sender->addWindow($inv);
        }else{
            if(!$sender->isOp()) {
                $sender->sendMessage(Bag::PREFIX . '당신에게는 권한이 없습니다.');
                return false;
            }
            if(!isset($this->owner->db[strtolower($args[0])])) {
                $sender->sendMessage(Bag::PREFIX . '인식되지 않는 플레이어입니다.');
                return false;
            }
            $inv = new BagInventory(new Vector3($sender->x, $sender->y, $sender->z), $args . '\'s Inventory');
            $inv->setContents(array_map(function (array $array) {

                return Item::jsonDeserialize($array);

            }, $this->owner->db[strtolower($args[0])]['items']));
            $sender->addWindow($inv);
        }
    }
}
