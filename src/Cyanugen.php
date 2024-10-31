<?php

/*
 *    ____
 *   / ___|   _  __ _ _ __  _   _  __ _  ___ _ __
 *  | |  | | | |/ _` | '_ \| | | |/ _` |/ _ \ '_ \
 *  | |__| |_| | (_| | | | | |_| | (_| |  __/ | | |
 *   \____\__, |\__,_|_| |_|\__,_|\__, |\___|_| |_|
 *        |___/                   |___/ Pro
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author zsTr3mx_ (@zstr3mx_dev)
 *
*/

use pocketmine\event\Listener;
use pocketmine\event\player\{PlayerJoinEvent, PlayerChatEvent, PlayerQuitEvent};
use pocketmine\network\Network;
use pocketmine\network\protocol\TextPacket;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Cyanugen extends PluginBase implements Listener {

    private $config;
    private $cyanugenIcon = "E000";
    
    private $packetPrefix = "§";

    public function onEnable() {
        $this->saveDefaultConfig();
        $this->config = $this->getConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info(TextFormat::GREEN . "Cyanugen plugin enabled");
    }

    public function onPlayerJoin(PlayerJoinEvent $event) {
        $pk = new TextPacket();
        $pk->type = TextPacket::TYPE_CHAT;
        $pk->source = "";
        $pk->message = $this->config->getNested("packets.join_packet");
        $event->getPlayer()->dataPacket($pk->setChannel(Network::CHANNEL_TEXT));
    }

    public function onPlayerChat(PlayerChatEvent $event) {
        $message = $event->getMessage();
        $versionTrigger = $this->config->getNested("packets.version_trigger");

        if (strpos($message, $versionTrigger) == 0) {
            $clientVersion = explode(";", $message)[1];
            $player = $event->getPlayer();
            $welcomeMessage = str_replace("CLIENT_VERSION", $clientVersion, $this->config->getNested("messages.welcome"));
            $player->sendMessage($welcomeMessage);
            
            if ($this->config->getNested("settings.username_edit")) {
                $player->setNameTag(hex2bin($this->cyanugenIcon) . " " . $player->getName());
            }
            
            if ($this->config->getNested("settings.scripting_enabled")) {
                $scriptMessage = $this->config->getNested("messages.scripts_enabled");
                $player->sendMessage($scriptMessage);

                $pk = new TextPacket();
                $pk->type = TextPacket::TYPE_CHAT;
                $pk->source = "";
                $pk->message = $this->config->getNested("packets.script_packet");
                $player->dataPacket($pk->setChannel(Network::CHANNEL_TEXT));
            }

            $event->setCancelled(true);
        }
    }
}
