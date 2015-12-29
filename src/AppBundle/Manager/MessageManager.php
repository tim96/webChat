<?php
/**
 * Created by PhpStorm.
 * User: tim
 * Date: 12/25/2015
 * Time: 9:33 PM
 */

namespace AppBundle\Manager;

use AppBundle\Interfaces\ChatInterface;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\WebSocket\Version\RFC6455\Connection;

class MessageManager implements MessageComponentInterface
{
    /** @var  ConnectionInterface[] */
    protected $connections;
    /** @var  ChatInterface */
    protected $chatHandler;
    /** @var  boolean */
    protected $isDebug;

    public function __construct($chatHandler)
    {
        $this->connections = array();
        $this->chatHandler = $chatHandler;
        $this->isDebug = false;
        $this->chatHandler->clearChats();
    }

    /**
     * When a new connection is opened it will be passed to this method
     * @param  ConnectionInterface $conn The socket/connection that just connected to your application
     * @throws \Exception
     */
    function onOpen(ConnectionInterface $conn)
    {
        $this->addLog('New connection #'.$this->getRid($conn));

        $this->connections[$this->getRid($conn)] = $conn;
    }

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     * @param  ConnectionInterface $conn The socket/connection that is closing/closed
     * @throws \Exception
     */
    function onClose(ConnectionInterface $conn)
    {
        $this->addLog('Close connection #'.$this->getRid($conn));

        $rid = array_search($conn, $this->connections);
        if ($user = $this->chatHandler->getUserByRid($rid)) {
            $chat = $user->getChat();
            $this->chatHandler->removeUserFromChat($user, $chat);
            foreach ($chat->getUsers() as $user) {
                $this->connections[$user->getRid()]->close();
            }
        }
        unset($this->connections[$rid]);
    }

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     * @param  ConnectionInterface $conn
     * @param  \Exception $e
     * @throws \Exception
     */
    function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->addLog('Error connection #'.$this->getRid($conn));
        $this->addError($e);

        $conn->close();
    }

    /**
     * Triggered when a client sends data through the socket
     * @param  \Ratchet\ConnectionInterface $from The socket/connection that sent the message to your application
     * @param  string $msg The message received
     * @throws \Exception
     */
    function onMessage(ConnectionInterface $from, $msg)
    {
        // $this->addLog('New messsage '.$msg.' from #'.$this->getRid($from));

        $msg = json_decode($msg, true);
        $rid = array_search($from, $this->connections);
        switch ($msg['type']) {
            case 'request':
                $chat = $this->chatHandler->findOrCreateChatForUser($rid);
                $this->addLog('Chat is completed '.$chat->getIsCompleted().' from #'.$rid);
                if ($chat->getIsCompleted()) {
                    $msg = json_encode(array('type' => 'response'));
                    $this->addLog('Send responce. Users: '.count($chat->getUsers()));
                    foreach ($chat->getUsers() as $user) {
                        $this->addLog('Send message. to #'.$user->getRid());
                        $conn = $this->connections[$user->getRid()];
                        $conn->send($msg);
                    }
                }
                break;
            case 'message':
                if ($chat = $this->chatHandler->getChatByUser($rid)) {
                    foreach ($chat->getUsers() as $user) {
                        $conn = $this->connections[$user->getRid()];
                        $msg['from'] = $conn === $from ? 'me' : 'guest';
                        $conn->send(json_encode($msg));
                    }
                }
                break;
        }
    }

    /**
     * @param ConnectionInterface|Connection $conn
     * @return string
     */
    private function getRid(ConnectionInterface $conn)
    {
        return $conn->resourceId;
    }

    private function addLog($text)
    {
        if ($this->isDebug) {
            echo 'Log: ' . $text . "\r\n<br/>";
        }
    }

    private function addLogError($text)
    {
        echo 'Error: ' . $text . "\r\n<br/>";
    }

    private function addError($ex)
    {
        if ($ex instanceof \Exception) {
            $this->addLogError($ex->getMessage());
        } else {
            $this->addLogError($ex);
        }
    }

    public function setIsDebug($value)
    {
        $this->isDebug = $value;
    }
}