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

class SubscribeCommand extends ContainerAwareCommand
{
    /** @var ContainerInterface */
    private $container;
    /** @var  \Symfony\Component\Console\Output\OutputInterface */
    private $output;
    /** @var  \Symfony\Component\Console\Input\InputInterface */
    private $input;
    /** @var  boolean */
    protected $isDebug;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    protected function configure()
    {
        // call: php app/console predis:subscribe isDebug
        $this
            ->setName('predis:subscribe')
            ->setDescription('Subscribe new messages')
            ->addArgument('isDebug', InputArgument::OPTIONAL, 'Turn on debug mode: true, false. Default - false', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        declare(ticks = 1);

        register_shutdown_function(array($this, 'stopCommand'));
        set_error_handler(array($this, 'errorHandler'));

        if (function_exists("pcntl_signal")) {
            pcntl_signal(SIGTERM, [$this, 'stopCommand']);
            pcntl_signal(SIGINT, [$this, 'stopCommand']);
        } else {

        }

        $this->isDebug = $input->getArgument('isDebug');

        $client = $this->container->get('snc_redis.default');
        $pubsub = $client->pubSubLoop();

        // Subscribe to your channels
        $pubsub->subscribe('control_channel', 'notifications');

        // Start processing the pubsup messages. Open a terminal and use redis-cli
        // to push messages to the channels. Examples:
        //   ./redis-cli PUBLISH notifications "this is a test"
        //   ./redis-cli PUBLISH control_channel quit_loop
        foreach ($pubsub as $message) {
            switch ($message->kind) {
                case 'subscribe':
                    echo "Subscribed to {$message->channel}", PHP_EOL;
                    break;

                case 'message':
                    if ($message->channel == 'control_channel') {
                        if ($message->payload == 'quit_loop') {
                            echo 'Aborting pubsub loop...', PHP_EOL;
                            $pubsub->unsubscribe();
                        } else {
                            echo "Received an unrecognized command: {$message->payload}.", PHP_EOL;
                        }
                    } else {
                        echo "Received the following message from {$message->channel}:",
                        PHP_EOL, "  {$message->payload}", PHP_EOL, PHP_EOL;
                    }
                    break;
            }
        }

        // Always unset the pubsub consumer instance when you are done! The
        // class destructor will take care of cleanups and prevent protocol
        // desynchronizations between the client and the server.
        unset($pubsub);

        $this->logMessage("Finish execute daemon.");
    }

    function f($redis, $chan, $msg) {
        switch($chan) {
            case 'chan-1':
                print "get $msg from $chan\n";
                break;
            case 'chan-2':
                print "get $msg FROM $chan\n";
                break;
            case 'chan-3':
                break;
        }
    }

    public function stopCommand()
    {
        $this->logMessage("Stop signal from system.");
    }

    public function errorHandler()
    {
        $this->logMessage("Error handler.");
    }

    protected function logMessage($message)
    {
        if ($this->isDebug) {
            $this->output->writeln($message);
        }
    }
}