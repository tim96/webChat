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
use AppBundle\Interfaces\ChatInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ChatHandler implements ChatInterface
{
    /** @var  ContainerInterface */
    protected $container;
    /** @var  EntityManager */
    protected $om;
    /** @var \AppBundle\Repository\ChatRepository  */
    private $repositoryChat;
    /** @var \AppBundle\Repository\ChatUserRepository  */
    private $repositoryChatUser;
    /** @var  boolean */
    protected $isDebug;

    public function __construct(ContainerInterface $container, ObjectManager $om)
    {
        $this->container = $container;
        $this->om = $om;
        $this->repositoryChat = $this->om->getRepository('AppBundle:Chat');
        $this->repositoryChatUser = $this->om->getRepository('AppBundle:ChatUser');
        $this->isDebug = false;
    }

    protected function getManager()
    {
        return $this->om;
    }

    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * @param ChatUser $user
     * @param Chat $chat
     */
    public function removeUserFromChat($user, $chat)
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
            $this->addLog('Find chat: '.$chat->getId());
            $chat->setIsCompleted(true);
        } else {
            $this->addLog('Create new chat.');
            $chat = new Chat();
        }
        $chat_user->setChat($chat);
        $chat->addUser($chat_user);

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

    public function clearChats()
    {
        /** @var \Doctrine\DBAL\Connection $conn */
        $conn = $this->om->getConnection();
        $platform = $conn->getDatabasePlatform();
        $conn->query('SET FOREIGN_KEY_CHECKS=0');
        $tableChatUser = $this->om->getClassMetadata('AppBundle\Entity\ChatUser');
        $conn->executeUpdate($platform->getTruncateTableSQL($tableChatUser->getTableName()));
        $tableChat = $this->om->getClassMetadata('AppBundle\Entity\Chat');
        $conn->executeUpdate($platform->getTruncateTableSQL($tableChat->getTableName()));
        $conn->query('SET FOREIGN_KEY_CHECKS=1');

        // delete table with rollback/commit functionalty
        //
        // $cmd = $em->getClassMetadata($className);
        // $connection = $em->getConnection();
        // $connection->beginTransaction();
        //
        // try {
        //    $connection->query('SET FOREIGN_KEY_CHECKS=0');
        //    $connection->query('DELETE FROM '.$cmd->getTableName());
        //    // Beware of ALTER TABLE here--it's another DDL statement and will cause
        //    // an implicit commit.
        //    $connection->query('SET FOREIGN_KEY_CHECKS=1');
        //    $connection->commit();
        // } catch (\Exception $e) {
        //    $connection->rollback();
        // }
    }

    private function addLog($text)
    {
        // todo: replace to monolog
        if ($this->isDebug) {
            echo 'Log: ' . $text . "\r\n<br/>";
        }
    }

    public function setIsDebug($value)
    {
        $this->isDebug = $value;
    }
}