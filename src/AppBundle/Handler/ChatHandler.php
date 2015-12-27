<?php
/**
 * Created by PhpStorm.
 * User: tim
 * Date: 12/24/2015
 * Time: 9:14 PM
 */

namespace AppBundle\Handler;

use AppBundle\Entity\Chat;
use AppBundle\Entity\ChatUser;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ChatHandler
{
    /** @var  ContainerInterface */
    protected $container;
    /** @var  EntityManager */
    protected $om;
    /** @var \AppBundle\Repository\ChatRepository  */
    private $repositoryChat;
    /** @var \AppBundle\Repository\ChatUserRepository  */
    private $repositoryChatUser;

    public function __construct(ContainerInterface $container, ObjectManager $om)
    {
        $this->container = $container;
        $this->om = $om;
        $this->repositoryChat = $this->om->getRepository('AppBundle:Chat');
        $this->repositoryChatUser = $this->om->getRepository('AppBundle:ChatUser');
    }

    protected function getManager()
    {
        return $this->om;
    }

    protected function getContainer()
    {
        return $this->container;
    }

    public function removeUserFromChat(ChatUser $user, Chat $chat)
    {
        if ($chat->getIsCompleted()) {
            $chat->removeUser($user);
            $chat->setIsCompleted(false);
        } else {
            $this->om->remove($chat);
        }

        $this->om->remove($user);
        $this->om->flush();
    }

    public function findOrCreateChatForUser($rid)
    {
        $chat_user = new ChatUser();
        $chat_user->setRid($rid);

        $chat = $this->getUncompletedChat();
        if ($chat) {
            $chat->setIsCompleted(true);
        } else {
            $chat = new Chat();
        }
        $chat_user->setChat($chat);

        $this->om->persist($chat);
        $this->om->persist($chat_user);
        $this->om->flush();

        return $chat;
    }

    public function getChatByUser($rid)
    {
        $chat_user = $this->getUserByRid($rid);
        return $chat_user ? $chat_user->getChat() : null;
    }

    public function getUserByRid($rid)
    {
        return $this->repositoryChatUser->findOneBy(array('rid' => $rid));
    }

    public function getUncompletedChat()
    {
        return $this->repositoryChat->findOneBy(array('isCompleted' => false));
    }
}