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
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Lesser General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
*/

namespace Muqsit;
use pocketmine\event\player\{PlayerInteractEvent, PlayerJoinEvent};
use pocketmine\command\{Command, CommandSender};
use pocketmine\utils\TextFormat as TF;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\level\sound\ExpPickupSound;

class XPBottle extends PluginBase implements Listener{

  public function onEnable(){
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }

  public function preventCrashes(PlayerJoinEvent $e){//This will be removed when MCPE fixes crashes caused by meta over 32000
    $p = $e->getPlayer();
    foreach($p->getInventory()->getContents() as $item){
      if($item->getId() === 384 && $item->getDamage() >= 32000){
	$p->sendMessage(TF::BOLD.TF::RED."(!) ".TF::RESET.TF::RED."An XP bottle in your inventory caused you to crash!");
	$p->sendMessage(TF::YELLOW.TF::BOLD."(!) ".TF::RESET.TF::YELLOW."We have refunded your XP.");
	$p->addExperience($item->getDamage());
	$p->getInventory()->remove($item);
      }
    }
  }
	
  public function calculateExpReduction($p, $exp){
    $xp = $p->getExp();
    $level = $p->getExpLevel();
    $p->setExperienceAndLevel($xp - $exp, $level);
  }

  public function redeemExp($player, $exp){
    $currentExp = $player->getExp();
    if($currentExp >= $exp){
      $this->calculateExpReduction($player, $exp);
      $xpBottle = Item::get(384,$exp,1);
      $xpBottle->setCustomName(TF::RESET.TF::GREEN.TF::BOLD.$player->getName()."'s Experience Bottle".TF::RESET."\n".TF::LIGHT_PURPLE."Value: ".TF::WHITE.$exp);
      $player->getInventory()->addItem($xpBottle);
      $player->sendMessage(TF::GREEN.TF::BOLD."XPBottle ".TF::RESET.TF::GREEN."You have successfully redeemed ".TF::YELLOW.$exp.TF::GREEN.".");
      $player->getLevel()->addSound(new ExpPickupSound($player), [$player]);
    }else{
      $player->sendMessage(TF::RED.TF::BOLD."XPBottle ".TF::RESET.TF::RED."You don't have enough experience. Your current experience is ".TF::YELLOW.$currentExp);
    }
  }
  
  public function onInteract(PlayerInteractEvent $e){
    $p = $e->getPlayer();
    $i = $e->getItem();
    if($i->getId() === 384 && $i->getDamage() > 0){
      $i->setCount($i->getCount() - 1);
      $p->getInventory()->setItem($p->getInventory()->getHeldItemSlot(), $i);
      $p->addExperience($e->getItem()->getDamage());
      $p->getLevel()->addSound(new ExpPickupSound($p), [$p]);
      $e->setCancelled();
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
