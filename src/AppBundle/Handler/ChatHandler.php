<?php
/**
 * Created by PhpStorm.
 * User: tim
 * Date: 12/24/2015
 * Time: 9:14 PM
 */

namespace AppBundle\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ChatHandler
{
    /** @var  ContainerInterface */
    protected $container;
    /** @var  EntityManager */
    protected $om;

    public function __construct(ContainerInterface $container, ObjectManager $om)
    {
        $this->container = $container;
        $this->om = $om;
    }

    protected function getManager()
    {
        return $this->om;
    }

    protected function getContainer()
    {
        return $this->container;
    }
}