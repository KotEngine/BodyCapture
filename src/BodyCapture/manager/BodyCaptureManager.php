<?php
namespace BodyCapture\manager;
use BodyCapture\Loader;
use pocketmine\player\Player;

class BodyCaptureManager
{
    private Loader $plugin;

    public function __construct(Loader $loader)
    {
        $this->plugin = $loader;
    }

    public array $players_array = [];

    public function isBodyObservable(Player $observable, &$govern = false): bool
    {
        foreach ($this->players_array as $pair) {
            if ($pair[1] == $observable) {
                $govern = $pair[2];
                return true;
            }
        }
        return false;
    }

    public function isBodyViewer(Player $viewer, &$govern = false): bool
    {
        foreach ($this->players_array as $pair) {
            if ($pair[0] == $viewer) {
                $govern = $pair[2];
                return true;
            }
        }
        return false;
    }

    public function addBodyViewer(Player $viewer, Player $observable, bool $govern): void
    {
        $observable->hidePlayer($viewer);
        $pos = $viewer->getPosition();
        $viewer->teleport($observable->getPosition(), $observable->getLocation()->getYaw(), $observable->getLocation()->getPitch());
        foreach ($this->players_array as &$pair) {
            if ($pair[0] == $viewer) {
                $viewer->showPlayer($pair[1]);
                if ($pair[2]) {
                    $pair[1]->sendMessage("{$viewer->getName()} покинул твоё тело!");
                }
                $pair[1] = $observable;
                $viewer->hidePlayer($observable);
                $pair[2] = $govern;
                if ($govern) {
                    $pair[3] = time();
                }
                return;
            }
        }
        $viewer->hidePlayer($observable);
        $this->players_array[] = [$viewer, $observable, $govern, time(), $pos];
    }

    public function getObservable(Player $viewer)
    {
        foreach ($this->players_array as $pair) {
            if ($pair[0] == $viewer) {
                return $pair[1];
            }
        }
        return null;
    }

    public function deleteBodyViewer(Player $viewer): void
    {
        foreach ($this->players_array as $key => $pair) {
            if ($pair[0] == $viewer) {
                $viewer->teleport($pair[4]);
                $pair[1]->showPlayer($viewer);
                $viewer->showPlayer($pair[1]);
                foreach ($this->plugin->getServer()->getOnlinePlayers() as $onlinePlayer) {
                    $onlinePlayer->showPlayer($viewer);
                }
                unset($this->players_array[$key]);
                return;
            }
        }
    }
}