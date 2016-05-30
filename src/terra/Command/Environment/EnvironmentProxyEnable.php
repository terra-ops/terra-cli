<?php

namespace terra\Command\Environment;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use terra\Command\Command;

class EnvironmentProxyEnable extends Command
{
    protected function configure()
    {
        $this
        ->setName('url-proxy:enable')
        ->setDescription('Enable the URL proxy allowing multiple domains')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Hello Terra!');
        $cmd = 'docker run -d -p 80:80 -v /var/run/docker.sock:/tmp/docker.sock:ro jwilder/nginx-proxy';

        $process = new Process($cmd);
        $process->setTimeout(null);
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                echo 'DOCKER > '.$buffer;
            } else {
                echo 'DOCKER > '.$buffer;
            }
        });
    }
}
