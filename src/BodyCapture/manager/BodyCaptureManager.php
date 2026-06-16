<?php
namespace BodyCapture\manager;
use BodyCapture\Loader;
use pocketmine\player\Player;

class BodyCaptureManager
{
    private Loader $plugin;
    private array $players_array = [];

    public function __construct(Loader $loader)
    {
        $this->plugin = $loader;
    }

    public function getPairs(): array
    {
        return $this->players_array;
    }

    public function isBodyObservable(Player $observable, &$govern = false): bool
    {
        foreach ($this->players_array as $pair) {
            if ($pair[1] === $observable) {
                $govern = $pair[2];
                return true;
            }
        }
        return false;
    }

    public function isBodyViewer(Player $viewer, &$govern = false): bool
    {
        foreach ($this->players_array as $pair) {
            if ($pair[0] === $viewer) {
                $govern = $pair[2];
                return true;
            }
        }
        return false;
    }

    public function canStartCapture(Player $sender, Player $target): bool
    {
        if ($sender === $target) {
            return false;
        }
        if ($this->isBodyObservable($sender) || $this->isBodyViewer($sender)) {
            return false;
        }
        $govern = false;
        if ($this->isBodyObservable($target, $govern) && $govern) {
            return false;
        }
        if ($this->isBodyViewer($target)) {
            return false;
        }
        return true;
    }

    public function addBodyViewer(Player $viewer, Player $observable, bool $govern): void
    {
        $pos = $viewer->getPosition();
        $observable->hidePlayer($viewer);
        $viewer->hidePlayer($observable);
        $viewer->teleport($observable->getPosition(), $observable->getLocation()->getYaw(), $observable->getLocation()->getPitch());
        $this->players_array[] = [$viewer, $observable, $govern, time(), $pos];
    }

    public function getObservable(Player $viewer)
    {
        foreach ($this->players_array as $pair) {
            if ($pair[0] === $viewer) {
                return $pair[1];
            }
        }
        return null;
    }

    public function deleteBodyViewer(Player $viewer): void
    {
        foreach ($this->players_array as $key => $pair) {
            if ($pair[0] === $viewer) {
                $observable = $pair[1];
                $viewer->teleport($pair[4]);
                $observable->showPlayer($viewer);
                $viewer->showPlayer($observable);
                foreach ($this->plugin->getServer()->getOnlinePlayers() as $onlinePlayer) {
                    $onlinePlayer->showPlayer($viewer);
                }
                unset($this->players_array[$key]);
                $this->players_array = array_values($this->players_array);
                return;
            }
        }
    }
}
