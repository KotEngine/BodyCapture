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
        $pairs = $this->plugin->getManager()->getPairs();
        if (empty($pairs)) {
            return;
        }

        $onlinePlayers = Server::getInstance()->getOnlinePlayers();

        foreach ($pairs as $pair) {
            $viewer = $pair[0];
            $observable = $pair[1];
            $govern = $pair[2];

            if (!($viewer instanceof Player) || !($observable instanceof Player)) {
                continue;
            }

            $this->hideViewerFromOthers($viewer, $onlinePlayers);
            $this->syncPositions($viewer, $observable, $govern);
        }
    }

    private function hideViewerFromOthers(Player $viewer, array $onlinePlayers): void
    {
        foreach ($onlinePlayers as $onlinePlayer) {
            if ($onlinePlayer !== $viewer && $onlinePlayer->canSee($viewer)) {
                $onlinePlayer->hidePlayer($viewer);
            }
        }
    }

    private function syncPositions(Player $viewer, Player $observable, bool $govern): void
    {
        if ($viewer->getPosition()->distance($observable->getPosition()) <= 0.01) {
            return;
        }

        $leader = $govern ? $viewer : $observable;
        $follower = $govern ? $observable : $viewer;

        if ($follower->getPosition()->distance($leader->getPosition()) > 20) {
            $follower->teleport($leader->getPosition(), $leader->getLocation()->getYaw(), $leader->getLocation()->getPitch());
        } else {
            $follower->setMotion($leader->getPosition()->subtract(
                $follower->getPosition()->getX(),
                $follower->getPosition()->getY(),
                $follower->getPosition()->getZ()
            )->multiply(0.15));
        }

        if ($govern) {
            $observable->setRotation($viewer->getLocation()->getYaw(), $viewer->getLocation()->getPitch());
        }
    }
}
