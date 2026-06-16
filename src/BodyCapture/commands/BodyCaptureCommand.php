<?php
namespace BodyCapture\commands;
use BodyCapture\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class bodyCaptureCommand extends Command
{

    private Loader $plugin;

    public function __construct(Loader $loader)
    {
        $this->plugin = $loader;
        parent::__construct(
            "bodycapture",
            "Вселиться в тело игрока и управлять им.",
            "/bodycapture [игрок]\n/bodycapture exit",
        );
        $this->setPermission("cmd.bodycapture");
        $this->setPermissionMessage("Нет прав!");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if ($sender->hasPermission("cmd." . $this->getName())) {
            if (count($args) > 0 and $args[0] == "exit") {
                if ($sender instanceof Player) {
                    if ($this->plugin->getManager()->isBodyViewer($sender)) {
                        $govern = false;
                        $this->plugin->getManager()->isBodyViewer($sender, $govern);
                        if ($govern) {
                            $player = $this->plugin->getManager()->getObservable($sender);
                            $player->sendMessage("{$sender->getName()} покинул твоё тело!");
                        }
                        $this->plugin->getManager()->deleteBodyViewer($sender);
                        $sender->sendMessage("Ты покинул тело игрока!");
                    } else {
                        $sender->sendMessage("Вы ни в кого не вселялись!");
                    }
                } else {
                    $sender->sendMessage("Доступно только в игре!");
                }
            } else {
                if ($sender instanceof Player) {
                    if (count($args) >= 1) {
                        $name = $args[0];
                        if (($player = $this->plugin->getServer()->getPlayerByPrefix($name)) !== null) {
                            if ($player !== $sender) {
                                if (!$this->plugin->getManager()->isBodyObservable($player, $govern) || !$govern) {
                                    if (!$this->plugin->getManager()->isBodyViewer($player, $gvrn)) {
                                        $this->plugin->getManager()->addBodyViewer($sender, $player, true);
                                        $sender->sendMessage("Ты вселился в $name!");
                                        $player->sendMessage("В тебя вселился {$sender->getName()}!");
                                    } else {
                                        $sender->sendMessage("На данный момент ты не можешь вселиться в $name!");
                                    }
                                } else {
                                    $sender->sendMessage("Игроком $name уже управляют!");
                                }
                            } else {
                                $sender->sendMessage("Ты не можешь вселиться сам в себя!");
                            }
                        } else {
                            $sender->sendMessage("Игрока $name нет в сети, либо ты неправильно ввёл ник!");
                        }
                    } else {
                        $sender->sendMessage($this->getUsage());
                    }
                } else {
                    $sender->sendMessage("Доступно только в игре!");
                }
            }
        }else{
            $sender->sendMessage($this->getPermissionMessage());
        }
    }
}