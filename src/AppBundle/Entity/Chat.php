<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Chat
 *
 * @ORM\Table(name="chat")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ChatRepository")
 */
class Chat
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
     * @var bool     *
     * @ORM\Column(type="boolean")
     */
    protected $isCompleted;

    /**
     * @var ArrayCollection     *
     * @ORM\OneToMany(targetEntity="ChatUser", mappedBy="Chat")
     */
    private $users;

    public function __construct()
    {
        $this->isCompleted = false;
        $this->users = new ArrayCollection();
    }

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
     * Set isCompleted
     *
     * @param boolean $isCompleted
     *
     * @return Chat
     */
    public function setIsCompleted($isCompleted)
    {
        $this->isCompleted = $isCompleted;

        return $this;
    }

    /**
     * Get isCompleted
     *
     * @return boolean
     */
    public function getIsCompleted()
    {
        return $this->isCompleted;
    }

    /**
     * Add user
     *
     * @param \AppBundle\Entity\ChatUser $user
     *
     * @return Chat
     */
    public function addUser(\AppBundle\Entity\ChatUser $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \AppBundle\Entity\ChatUser $user
     */
    public function removeUser(\AppBundle\Entity\ChatUser $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }
}
