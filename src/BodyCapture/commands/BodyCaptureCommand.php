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
        if (!$sender->hasPermission("cmd." . $this->getName())) {
            $sender->sendMessage($this->getPermissionMessage());
            return;
        }

        if (!($sender instanceof Player)) {
            $sender->sendMessage("Доступно только в игре!");
            return;
        }

        $mode = (count($args) > 0 && $args[0] === "exit") ? "exit" : "enter";

        switch ($mode) {
            case "exit":
                $this->handleExit($sender);
                break;
            case "enter":
                $this->handleEnter($sender, $args);
                break;
        }
    }

    private function handleExit(Player $sender): void
    {
        if (!$this->plugin->getManager()->isBodyViewer($sender)) {
            $sender->sendMessage("Вы ни в кого не вселялись!");
            return;
        }

        $govern = false;
        $this->plugin->getManager()->isBodyViewer($sender, $govern);

        if ($govern) {
            $observable = $this->plugin->getManager()->getObservable($sender);
            $observable->sendMessage("{$sender->getName()} покинул твоё тело!");
        }

        $this->plugin->getManager()->deleteBodyViewer($sender);
        $sender->sendMessage("Ты покинул тело игрока!");
    }

    private function handleEnter(Player $sender, array $args): void
    {
        if (count($args) < 1) {
            $sender->sendMessage($this->getUsage());
            return;
        }

        $name = $args[0];
        $target = $this->plugin->getServer()->getPlayerByPrefix($name);

        if ($target === null) {
            $sender->sendMessage("Игрока $name нет в сети, либо ты неправильно ввёл ник!");
            return;
        }

        if ($target === $sender) {
            $sender->sendMessage("Ты не можешь вселиться сам в себя!");
            return;
        }

        if (!$this->plugin->getManager()->canStartCapture($sender, $target)) {
            $sender->sendMessage("Игроком $name уже управляют, либо это недоступно сейчас!");
            return;
        }

        $this->plugin->getManager()->addBodyViewer($sender, $target, true);
        $sender->sendMessage("Ты вселился в $name!");
        $target->sendMessage("В тебя вселился {$sender->getName()}!");
    }
}
