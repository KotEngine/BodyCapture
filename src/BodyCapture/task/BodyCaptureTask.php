<?php
namespace BodyCapture\task;
use BodyCapture\Loader;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class BodyCaptureTask extends Task
{

    private Loader $plugin;

    public function __construct(Loader $loader)
    {
        $this->plugin = $loader;
    }

    public function onRun(): void
    {
        $plugin = $this->plugin;
        $viewers = $plugin->getManager()->players_array;
        foreach ($viewers as $pair){
            $viewer = $pair[0];
            $observable = $pair[1];
            $govern = $pair[2];
            foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
                if($onlinePlayer!=$viewer && $onlinePlayer->canSee($viewer)){
                    $onlinePlayer->hidePlayer($viewer);
                }
            }
            if ($viewer instanceof Player and $observable instanceof Player) {
                if ($viewer->getPosition()->distance($observable->getPosition()) > 0.01) {
                    if (!$govern) {
                        if ($viewer->getPosition()->distance($observable->getPosition()) > 20) {
                            $viewer->teleport($observable->getPosition(), $observable->getLocation()->getYaw(), $observable->getLocation()->getPitch());
                        } else {
                            $viewer->setMotion($observable->getPosition()->subtract($viewer->getPosition()->getX(), $viewer->getPosition()->getY(), $viewer->getPosition()->getZ())->multiply(0.15));
                        }
                    } else {
                        if ($viewer->getPosition()->distance($observable->getPosition()) > 20) {
                            $observable->teleport($viewer->getPosition(), $viewer->getLocation()->getYaw(), $viewer->getLocation()->getPitch());
                        } else {
                            $observable->setMotion($viewer->getPosition()->subtract($observable->getPosition()->getX(), $observable->getPosition()->getY(), $observable->getPosition()->getZ())->multiply(0.15));
                        }
                        $observable->setRotation($viewer->getLocation()->getYaw(), $viewer->getLocation()->getPitch());
                    }
                }
            }
        }
    }
}