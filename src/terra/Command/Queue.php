<?php

namespace terra\Command;

use terra\Factory\EnvironmentFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Queue extends Command
{
    protected function configure()
    {
        $this
            ->setName('queue')
            ->setDescription('Run all the commands in the Queue')
            ->addArgument(
                'url',
                InputArgument::OPTIONAL,
                'The AMPQ server URL in the format of "username:password@server:port/queue',
                'guest:guest@localhost:32773/terra'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $url = $input->getArgument('url');
        $server = parse_url($url);

        // AMQP server information
        $server['queue'] = $server['path'];

        $output->writeln("Checking <question>$url</question> ...");

    }
}