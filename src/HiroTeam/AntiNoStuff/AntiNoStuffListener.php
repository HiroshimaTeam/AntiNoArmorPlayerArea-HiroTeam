<?php

#██╗░░██╗██╗██████╗░░█████╗░████████╗███████╗░█████╗░███╗░░░███╗
#██║░░██║██║██╔══██╗██╔══██╗╚══██╔══╝██╔════╝██╔══██╗████╗░████║
#███████║██║██████╔╝██║░░██║░░░██║░░░█████╗░░███████║██╔████╔██║
#██╔══██║██║██╔══██╗██║░░██║░░░██║░░░██╔══╝░░██╔══██║██║╚██╔╝██║
#██║░░██║██║██║░░██║╚█████╔╝░░░██║░░░███████╗██║░░██║██║░╚═╝░██║
#╚═╝░░╚═╝╚═╝╚═╝░░╚═╝░╚════╝░░░░╚═╝░░░╚══════╝╚═╝░░╚═╝╚═╝░░░░░╚═╝
namespace HiroTeam\AntiNoStuff;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;

class AntiNoStuffListener implements Listener
{

    /**
     * @var AntiNoStuffMain
     */
    private $main;

    /**
     * @var array
     */
    private $playerMotionCooldown = [];

    /**
     * AntiNoStuffListener constructor.
     * @param AntiNoStuffMain $main
     */
    public function __construct(AntiNoStuffMain $main)
    {
        $this->main = $main;
    }

    /**
     * @param PlayerMoveEvent $event
     * @priority NORMAL
     */
    public function onMove(PlayerMoveEvent $event)
    {
        if ($this->main->WorldGuardIsRun()) {
            if (!$event->getFrom()->equals($event->getTo())) {
                $player = $event->getPlayer();
                $regions = $this->main->Wg->getRegionOf($player);
                if (in_array($regions, $this->main->getAllNoStuffArea())) {
                    if ($this->main->PlayerIsFullStuff($player)) {
                        return;
                    }
                    if (!isset($this->playerMotionCooldown[$player->getName()]) or $this->playerMotionCooldown[$player->getName()] > 3) {
                        $player->setMotion($event->getFrom()->subtract($player->getLocation())->normalize()->multiply(2));
                        $this->playerMotionCooldown[$player->getName()] = 0;
                    } else{
                        $this->playerMotionCooldown[$player->getName()] = $this->playerMotionCooldown[$player->getName()] + 1;
                    }
                    $player->sendMessage($this->main->getMainConfig()->get("NoStuffMessage"));
                }
            }
        }
    }

    /**
     * @param PlayerQuitEvent $event
     * @priority NORMAL
     */
    public function DumpMemory(PlayerQuitEvent $event){
        $playerName = $event->getPlayer()->getName();
        if(isset($this->playerMotionCooldown[$playerName])){
            unset($this->playerMotionCooldown[$playerName]);
        }
    }
}