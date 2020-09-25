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
        $inv = new BagInventory(new Vector3($sender->x, $sender->y, $sender->z),'BagInventory');
        $inv->setContents(array_map(function (array $array) {

            return Item::jsonDeserialize($array);

        }, $this->owner->db[$sender->getName()]['items']));
        $sender->addWindow($inv);
    }
}