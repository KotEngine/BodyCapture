<?php

namespace BodyCapture\events;

use BodyCapture\Loader;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;

class Listener implements \pocketmine\event\Listener
{
    private Loader $plugin;

    public function __construct(Loader $loader)
    {
        $this->plugin = $loader;
    }

    public function PlayerQuitEvent(PlayerQuitEvent $e): void
    {
        $player = $e->getPlayer();
        foreach($this->plugin->getManager()->players_array as $pair){
            $viewer = $pair[0];
            $observable = $pair[1];
            $govern = $pair[2];
            if($player === $observable){
                $this->plugin->getManager()->deleteBodyViewer($viewer);
                $viewer->sendMessage("Ты покинул тело игрока!");
                break;
            }
            if($player === $viewer){
                $this->plugin->getManager()->deleteBodyViewer($viewer);
                if($govern){
                    $observable->sendMessage("{$viewer->getName()} покинул твоё тело!");
                }
                break;
            }
        }
    }
    public function EntityDamageEvent(EntityDamageEvent $e): void
    {
        $player = $e->getEntity();
        if($player instanceof Player){
            if($e instanceof EntityDamageByEntityEvent){
                $damager = $e->getDamager();
                if($damager instanceof Player){
                    if($this->plugin->getManager()->isBodyObservable($player,$gov)){
                        if($this->plugin->getManager()->isBodyViewer($damager,$gov)){
                            $e->cancel();
                        }
                    }
                }

            }
            $gov = false;
            if($this->plugin->getManager()->isBodyViewer($player,$gov)){
                if(!$gov){
                    $e->cancel();
                }
            }
        }
    }
}