<?php

namespace BeeAZZ\SeasonShop;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\{Command, CommandSender};
use pocketmine\item\{Item, ItemFactory, VanillaItems};
use BeeAZZ\SeasonShop\libs\davidglitch04\libEco\libEco;
use BeeAZZ\SeasonShop\libs\jojoe77777\FormAPI\SimpleForm;
use BeeAZZ\SeasonShop\libs\jojoe77777\FormAPI\CustomForm;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;


class SeasonShop extends PluginBase implements Listener{
  
  protected $season = "";
  
  protected $price = "";
  
  protected $shop;
  
  protected $i;
  
  protected const VERSION = 1;
  
  protected const SHOPVERSION = 1;
  
  
  public function onEnable() : void{
   $this->getServer()->getPluginManager()->registerEvents($this, $this);
   $this->saveDefaultConfig();
   $this->saveResource("seasonshop.yml");
   $this->shop = new Config($this->getDataFolder()."seasonshop.yml",Config::YAML);
   if($this->getConfig()->get("version") !== self::VERSION){
   $this->getLogger()->notice("§c§lPlease use the latest config");
   $this->getServer()->getPluginManager()->disablePlugin($this);
   }
   if($this->shop->get("version") !== self::SHOPVERSION){
   $this->getLogger()->notice("§c§lPlease use the latest seasonshop.yml");
   $this->getServer()->getPluginManager()->disablePlugin($this);
  }
  }
  
  public function getSeason(){
     date_default_timezone_set($this->getConfig()->get("date_default_timezone_set"));
     $day = date("d");
        if($day <= 31){
            $season = "Last Winter";
        }
        if($day <= 29){
            $season = "Winter";
        }
        if($day <= 24){
            $season = "Last Autumn";
        }
        if($day <= 22){
            $season = "Autumn";
        }
        if($day <= 14){
            $season = "Last Summer";
        }
        if($day <= 12){
            $season = "Summer";
        }
        if($day <= 7){
            $season = "Last Spring";
        }
        if($day <= 5){
            $season = "Spring";
        }
        return $season;
    }
  public function onJoin(PlayerJoinEvent $event){
   $player = $event->getPlayer();
   $this->getSeason(); #Checking the Season With Events Instead of Tasks Will Be Smoother
   $player->sendMessage(str_replace("{Season}", $this->getSeason(), $this->getConfig()->get("Now-Season")));
  }
    
  public function getSell(){
    switch ($this->getSeason()){
      case "Spring":
        $price = $this->getConfig()->get("Price-Spring");
        break;
      case "Summer";
          $price = $this->getConfig()->get("Price-Summer");
        break;
      case "Autumn":
          $price = $this->getConfig()->get("Price-Autumn");
        break;
      case "Winter":
          $price = $this->getConfig()->get("Price-Winter");
        break;
      default:
        $price = 0;
    }
    return $price;
   }


  public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
   switch($cmd->getName()){
    case "sshop":
     $player = $sender;
     if(!$player instanceof Player){
      $player->sendMessage("Please Use Command In Game");
      return true;
     }
     if($player->hasPermission("seasonshop.command")){
     if($this->getSeason() !== "Spring" && $this->getSeason() !== "Summer" && $this->getSeason() !== "Autumn" && $this->getSeason() !== "Winter"){
     $player->sendMessage($this->getConfig()->get("Last-Season"));
     return true;
  }
   $this->shop($player);
  }
  break;
   }
   return true;
  }
  
   public function shop($player){
    $form = new SimpleForm(function(Player $player, $data){
    if($data === null){
     return true;
   }
    $id = $this->shop->get(strtolower($data))["id"];
    $meta = $this->shop->get(strtolower($data))["meta"];
    $amount = $this->shop->get(strtolower($data))["amount"];
    $prices = $this->shop->get(strtolower($data))["price"];
    $item = ItemFactory::getInstance()->get($id, $meta, $amount);
    $price = $this->getSell() * $amount * $prices;
    if(!$player->getInventory()->contains($item)){
     $player->sendMessage($this->getConfig()->get("No-Item-Sell"));
     return true;
    }
     $player->getInventory()->removeItem($item);
     libEco::addMoney($player, $price);
     $player->sendMessage(str_replace("{PRICE}", $price, $this->getConfig()->get("Sell-MSG")));
    });
    for($i = 0; $i <= 100; $i++){
    if($this->shop->exists($i)){
     $form->addButton($this->shop->get(strtolower($i))["name"]);
    }
    }
    $form->setTitle($this->getConfig()->get("UI")["Title"]);
    $form->setContent(str_replace("{SEASON}", $this->getSeason(), $this->getConfig()->get("UI")["Content"]));
    $form->sendToPlayer($player);
    }
   }
