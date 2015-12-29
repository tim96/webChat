<?php

namespace AppBundle\Interfaces;

interface ChatInterface
{
    public function removeUserFromChat($user, $chat);

    public function findOrCreateChatForUser($rid);

    public function getChatByUser($rid);

    public function getUserByRid($rid);

    public function getUncompletedChat();

    public function clearChats();
}