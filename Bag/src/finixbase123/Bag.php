<?php

namespace finixbase123;

use finixbase123\command\BagCommand;
use finixbase123\inventory\BagInventory;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;

class Bag extends PluginBase implements Listener
{

    /**
     * @var Bag|null
     */
    private static $instance;
    public $database, $db;

    public function onEnable()
    {
        Server::getInstance()->getPluginManager()->registerEvents($this, $this);
        Server::getInstance()->getCommandMap()->register('bag', new BagCommand($this, '가방', '가방 명령어 입니다.', '/가방', ['bag']));
        $this->database = new Config($this->getDataFolder() . 'Inventory.yml', Config::YAML);
        $this->db  = $this->database->getAll();
    }

    public function onDisable()
    {
        $this->database->setAll($this->db);
        $this->database->save();
    }

    public function onLoad()
    {
        self::$instance = $this;
    }

    public static function getInstance()
    {
        return self::$instance;
    }

    public function onLogin(PlayerLoginEvent $event)
    {
        if(!isset($this->db[$event->getPlayer()->getName()])) {
            $this->db[$event->getPlayer()->getName()]['items'] = [];
        }
    }

    public function onPacketReceive (DataPacketReceiveEvent $event)
    {

        $packet = $event->getPacket();

        if(! $packet instanceof ContainerClosePacket)
            return;

        $player = $event->getPlayer();
        $inv = $player->getWindow ($packet->windowId);

        if (! $inv instanceof BagInventory)
            return;
            $this->db[$player->getName()]['items'] = (array_map(function (Item $item) {

                return $item->jsonSerialize();

            }, $inv->getContents(true)));

        $pk = new ContainerClosePacket();
        $pk->windowId = $player->getWindowId($inv);
        $player->sendDataPacket($pk);
    }
}