<?php
namespace BodyCapture;
use BodyCapture\commands\BodyCaptureCommand;
use BodyCapture\manager\BodyCaptureManager;
use BodyCapture\task\BodyCaptureTask;
use BodyCapture\events\Listener;
use pocketmine\plugin\PluginBase;

class Loader extends PluginBase
{
    private BodyCaptureManager $manager;

    protected function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents(new Listener($this), $this);
        $this->manager = new BodyCaptureManager($this);
        $this->regCommand();
        $this->regTask();
    }

    public function regCommand(): void
    {
        $this->getServer()->getCommandMap()->register("bodycapture", new BodyCaptureCommand($this));
    }

    public function regTask(): void
    {
        $this->getScheduler()->scheduleRepeatingTask(new BodyCaptureTask($this), 20);
    }

    public function getManager(): BodyCaptureManager
    {
        return $this->manager;
    }
}