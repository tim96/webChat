<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/version", name="symfony_version")
     */
    public function symfonyVersionAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
        ));
    }

    /**
     * @Route("/", name="chat")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('AppBundle:Default:chat.html.twig');
    }

    /**
     * @Route("/about", name="about")
     */
    public function aboutAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('AppBundle:Default:about.html.twig');
    }

    /**
     * @Route("/redis", name="redis")
     * @Template("AppBundle:Default:redis.html.twig")
     *
     * @param Request $request
     * @return array
     */
    public function redisAction(Request $request)
    {
        $redis = $this->container->get('snc_redis.default');
        $val = $redis->incr('value');

        return array('value' => $val);
    }
}
