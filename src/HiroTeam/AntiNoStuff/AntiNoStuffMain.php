<?php

#██╗░░██╗██╗██████╗░░█████╗░████████╗███████╗░█████╗░███╗░░░███╗
#██║░░██║██║██╔══██╗██╔══██╗╚══██╔══╝██╔════╝██╔══██╗████╗░████║
#███████║██║██████╔╝██║░░██║░░░██║░░░█████╗░░███████║██╔████╔██║
#██╔══██║██║██╔══██╗██║░░██║░░░██║░░░██╔══╝░░██╔══██║██║╚██╔╝██║
#██║░░██║██║██║░░██║╚█████╔╝░░░██║░░░███████╗██║░░██║██║░╚═╝░██║
#╚═╝░░╚═╝╚═╝╚═╝░░╚═╝░╚════╝░░░░╚═╝░░░╚══════╝╚═╝░░╚═╝╚═╝░░░░░╚═╝
namespace HiroTeam\AntiNoStuff;

use Chalapa13\WorldGuard\WorldGuard;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;


class AntiNoStuffMain extends PluginBase
{

    /**
     * @var WorldGuard
     */
    public $Wg;

    /**
     * @var Config
     */
    private $data;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $AntiNoStuffRegions = [];


    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents(new AntiNoStuffListener($this), $this);


        if ($this->getServer()->getPluginManager()->getPlugin("WorldGuard") !== null) {
            $this->Wg = $this->getServer()->getPluginManager()->getPlugin("WorldGuard");
        } else {
            $this->getLogger()->critical("WorldGuard not found, this plugin requires WorldGuard");
        }

        $this->data = new Config($this->getDataFolder() . "data.yml", Config::YAML, [
            "regions" => []
        ]);
        if (!file_exists($this->getDataFolder() . "config.yml")) {
            $this->saveResource("config.yml");
        }
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

        $this->AntiNoStuffRegions = $this->data->getAll()["regions"];

    }

    /**
     * @return Config
     */
    public function getMainConfig(): Config
    {
        return $this->config;
    }

    /**
     * @return array
     */
    public function getAllNoStuffArea(): array
    {
        return $this->AntiNoStuffRegions;
    }

    /**
     * @return bool
     */
    public function WorldGuardIsRun(): bool
    {
        return !empty($this->Wg);
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function PlayerIsFullStuff(Player $player): bool
    {
        $armorInventory = $player->getArmorInventory();
        $allArmor = [
            "helmet" => $armorInventory->getHelmet()->getId(),
            "chestplate" => $armorInventory->getChestplate()->getId(),
            "leggins" => $armorInventory->getLeggings()->getId(),
            "boots" => $armorInventory->getBoots()->getId(),
        ];
        foreach ($allArmor as $location => $armor) {
            if ($armor === 0) {
                return false;
            }
        }
        return true;
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        $commandName = strtolower($command->getName());

        if ($commandName === "antinostuff") {

            if (!$sender->hasPermission("command.antinostuff")) {
                $sender->sendMessage("§cyou don't have permission to use this command !");
                return true;
            }

            if (empty($this->Wg)) {
                $sender->sendMessage("§cThe plugin must be used with WorldGuard to work!");
                return true;
            }
            $argmument = array_shift($args);

            if ($argmument === "list") {
                if (empty($this->AntiNoStuffRegions)) {
                    $sender->sendMessage("§cYou don't have regions");
                    return true;
                }
                $sender->sendMessage("§eList of AntiNoStuff regions:§r " . implode(", ", $this->AntiNoStuffRegions));
                return true;
            }

            $value = implode(" ", $args);
            if (empty($value)) {
                return false;
            }
            if ($argmument === "add") {
                array_push($this->AntiNoStuffRegions, $value);
                $this->data->set("regions", $this->AntiNoStuffRegions);
                $this->data->save();
                $sender->sendMessage("§aYou have successfully add " . $value . " as AntiNoStuff region");
                return true;

            }
            if ($argmument === "remove") {
                unset($this->AntiNoStuffRegions[array_search($value, $this->AntiNoStuffRegions)]);
                $this->data->set("regions", $this->AntiNoStuffRegions);
                $this->data->save();
                $sender->sendMessage("§aYou have successfully remove " . $value . " as AntiNoStuff region");
                return true;
            }
        }
        return false;
    }
}