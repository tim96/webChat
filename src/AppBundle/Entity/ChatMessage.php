<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ChatMessage
 *
 * @ORM\Table(name="chat_message")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ChatMessageRepository")
 */
class ChatMessage
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
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}

