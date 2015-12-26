<?php

namespace AppBundle\Command;

use AppBundle\Manager\MessageManager;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface as InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DaemonCommand extends ContainerAwareCommand
{
    /** @var ContainerInterface */
    private $container;
    /** @var  \Symfony\Component\Console\Output\OutputInterface */
    private $output;
    /** @var  \Symfony\Component\Console\Input\InputInterface */
    private $input;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    protected function configure()
    {
        // call: php app/console daemon:start
        $this
            ->setName('daemon:start')
            ->setDescription('Start web chat application')
            ->addArgument('isDebug', InputArgument::OPTIONAL, 'Turn on debug mode: true, false. Default - false', false)
            ->addOption('port', null, InputOption::VALUE_OPTIONAL, 'The port for incoming connection. Default - 8080', 8080)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        if (function_exists("pcntl_signal")) {
            pcntl_signal(SIGTERM, [$this, 'stopCommand']);
            pcntl_signal(SIGINT, [$this, 'stopCommand']);
        }

        $isDebug = $input->getArgument('isDebug');
        $port = $input->getOption('port');

        $chat = $this->container->get('app.chat.handler');
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new MessageManager($chat)
                )
            ),
            $port
        );
        if ($isDebug) {
            $this->output->writeln("Start server.");
        }

        $server->run();

        if ($isDebug) {
            $this->output->writeln("Finish execute daemon.");
        }
    }

    public function stopCommand()
    {
        $this->output->writeln("Stop signal from system.");
    }


}