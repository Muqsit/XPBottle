<?php

/*
*
*  _    _ ______ ______                 _       
* \ \  / (_____ (____  \       _   _   | |      
*  \ \/ / _____) )___)  ) ___ | |_| |_ | | ____ 
*   )  ( |  ____/  __  ( / _ \|  _)  _)| |/ _  )
*  / /\ \| |    | |__)  ) |_| | |_| |__| ( (/ / 
* /_/  \_\_|    |______/ \___/ \___)___)_|\____)
*
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Lesser General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
*/

namespace Muqsit;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\command\{Command, CommandSender};
use pocketmine\utils\TextFormat as TF;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\Player;

class XPBottle extends PluginBase implements Listener{

  public function onEnable(){
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }
  
  public function redeemExp($player, $exp){
    $currentExp = $player->getExp();
    if($currentExp >= $exp){
      $player->setExp($currentExp - $exp);
      $xpBottle = Item::get(384,$exp,1);
      $xpBottle->setCustomName(TF::GREEN.TF::BOLD.$player->getName()."'s Experience Bottle\n".TF::PURPLE."Value: ".TF::WHITE.$exp);
      $player->getInventory()->addItem($xpBottle);
      $player->sendMessage(TF::GREEN.TF::BOLD."XPBottle ".TF::RESET.TF::GREEN."You have successfully redeemed ".TF::YELLOW.$exp.TF::GREEN.".");
    }else{
      $player->sendMessage(TF::RED.TF::BOLD."XPBottle ".TF::RESET.TF::RED."You don't have enough experience. Your current experience is ".TF::YELLOW.$currentExp);
    }
  }
  
  public function onInteract(PlayerInteractEvent $e){
    $p = $e->getPlayer();
    if($e->getItem()->getId() === 384 && $e->getItem()->getDamage() > 0){
      $p->addExperience($e->getItem()->getDamage());
    }
  }
  
  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
    if(!$sender instanceof Player) return;
    switch(strtolower($cmd->getName())){
      case "exp":
        $sender->sendMessage(TF::GREEN.TF::BOLD."XPBottle ".TF::RESET.TF::GREEN."You have ".TF::YELLOW.$sender->getExp()." XP".TF::GREEN." with you right now.");
      break;
      case "xpbottle":
        if(!$sender->hasPermission("redeem.exp")) return;
        if(!isset($args[0])) $sender->sendMessage(TF::YELLOW."/xpbottle <amount>\n".TF::GRAY."Check your current experience using the command ".TF::YELLOW."/exp");
        if(isset($args[0])){
          if(is_numeric($args[0])) $this->redeemExp($sender, $args[0]);
          else $sender->sendMessage(TF::RED.TF::BOLD."XPBottle ".TF::RESET.TF::RED."You have provided an invalid amount.");
        }
      break;
    }
  }
}
