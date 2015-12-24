<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ChatUser
 *
 * @ORM\Table(name="chat_user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ChatUserRepository")
 */
class ChatUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(type="integer", unique=true)
     */
    private $rid;

    /**
     * @var Chat
     * @ORM\ManyToOne(targetEntity="Chat", inversedBy="users")
     * @ORM\JoinColumn(name="chat_id", referencedColumnName="id")
     */
    private $Chat;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set rid
     *
     * @param integer $rid
     *
     * @return ChatUser
     */
    public function setRid($rid)
    {
        $this->rid = $rid;

        return $this;
    }

    /**
     * Get rid
     *
     * @return integer
     */
    public function getRid()
    {
        return $this->rid;
    }

    /**
     * Set chat
     *
     * @param \AppBundle\Entity\Chat $chat
     *
     * @return ChatUser
     */
    public function setChat(\AppBundle\Entity\Chat $chat = null)
    {
        $this->Chat = $chat;

        return $this;
    }

    /**
     * Get chat
     *
     * @return \AppBundle\Entity\Chat
     */
    public function getChat()
    {
        return $this->Chat;
    }
}
