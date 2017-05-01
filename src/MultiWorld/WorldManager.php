<?php

namespace MultiWorld;

use pocketmine\level\generator\Generator;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\Server;

class WorldManager {

    /** @var  MultiWorld */
    public $plugin;

    public function __construct($plugin) {
        $this->plugin = $plugin;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function worldExists($name) {
        if(file_exists($this->plugin->getServer()->getDataPath().$name)) {
            return true;
        }
    }

    /**
     * @param string $generator
     * @return string
     */
    public function getGenerator($generator) {
        switch (strtolower($generator)) {
            case "normal":
            case "default":
            case "1":
                return "normal";
            case "nether":
            case "hell":
            case "2":
                return "hell";
            /*
            case "end":
            case "3":
                return "end";
            */
            case "flat":
            case "superflat":
            case "4":
                return "flat";
            case "void":
            case "sky":
            case "5":
                return "void";
            default:
                return "normal";
        }
    }

    /**
     * @param Player $player
     * @param string $name
     * @param string $generator
     * @param int $seed
     */
    public function generate(Player $player, $name, $generator, $seed) {
        if(!$this->worldExists($name)) {
            $gen = $this->getGenerator($generator);
            $this->plugin->getServer()->generateLevel($name,intval($seed),Generator::getGenerator($gen));
        }
        else {
            $player->sendMessage(MultiWorld::$prefix."§cWorld is now generated.");
        }
    }

    /**
     * @param Level $level
     * @param Player $player
     */
    public function delete(Level $level, Player $player) {
        $name = $level->getName();
        foreach ($level->getPlayers() as $players) {
            $players->teleport($this->plugin->getServer()->getDefaultLevel()->getSafeSpawn());
        }
        $this->unload($name);
        @rmdir($this->plugin->getServer()->getDataPath()."worlds/".$level->getFolderName().$name);
        $player->sendMessage(MultiWorld::$prefix."§aWorld removed.");
    }

    /**
     * @param string $name
     */
    public function load($name) {
        if($this->worldExists($name)) {
            $this->plugin->getServer()->loadLevel($name);
            $this->plugin->getLogger()->debug(MultiWorld::$prefix."§6Loading world {$name}.");
        }
    }

    /**
     * @param string $name
     */
    public function unload($name) {
        if($this->worldExists($name)) {
            foreach ($this->plugin->getServer()->getLevelByName($name)->getPlayers() as $player) {
                $player->teleport($this->plugin->getServer()->getDefaultLevel()->getSafeSpawn());
            }
            $this->plugin->getServer()->unloadLevel($this->plugin->getServer()->getLevelByName($name));
            $this->plugin->getLogger()->debug(MultiWorld::$prefix."§6Unloading world {$name}.");
        }
    }

    /**
     * @param $int
     * @return mixed|string
     */
    public function getWorldList($int) {
        // loaded
        if($int == 1) {
            $list = implode(", ", $this->plugin->getServer()->getLevels());
            return $list;
        }
        if($int == 2) {
            $list = implode(", ", scandir($this->plugin->getServer()->getDataPath()."worlds"));
            $list = str_replace(", .", "", $list);
            $list = str_replace(".", "", $list);
            return $list;
        }
    }

    /**
     * @param Player $player
     * @param string $name
     */
    public function teleportToWorld(Player $player, $name) {
        if($this->worldExists($name)) {
            $this->load($name);
            $player->teleport($this->plugin->getServer()->getLevelByName($name)->getSafeSpawn());
            $player->sendMessage(MultiWorld::$prefix."§aYou have been teleported to {$name}.");
        }
        else {
            $player->sendMessage(MultiWorld::$prefix."§cWorld does not exists!");
        }
    }

    /**
     * @param Player $player
     * @param string $name
     */
    public function sendLevelInfo(Player $player, $name) {

        // load level
        $this->load($name);

        // define level
        $level = $this->plugin->getServer()->getLevelByName($name);

        //players
        $players = count($level->getPlayers());
        //seed
        $seed = $level->getSeed();
        //spawn
        $x = $level->getSafeSpawn()->getX();$y = $level->getSafeSpawn()->getY();$z = $level->getSafeSpawn()->getZ();
        //time
        $time = $level->getTime();

        //define text
        $text = "§b--- §7[ §5LevelInfo: {$name} §7] §b---\n".
            "§2Players: §a{$players}\n".
            "§2Seed: §a{$seed}\n".
            "§2Spawn: §a{$x}, {$y}, {$z}\n".
            "§2Time: §a{$time}";

        //send message
        $player->sendMessage($text);
    }

    public function rename($oldname, $newname, Player $player) {
        if($this->worldExists($oldname)) {
            $this->unload($oldname);
            $path = $this->plugin->getServer()->getDataPath()."worlds";
            @rename($path.$oldname,$path.$newname);
            $player->sendMessage(MultiWorld::$prefix."§aLevel renamed.");
        }
    }
}