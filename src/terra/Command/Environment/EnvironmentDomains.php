<?php

namespace terra\Command\Environment;

use Symfony\Component\Console\Input\InputOption;
use terra\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use terra\Factory\EnvironmentFactory;

// ...

class EnvironmentDomains extends Command
{
    protected function configure()
    {
        $this
        ->setName('environment:domains')
        ->setDescription('Manage the Domains that are assigned to environments.')
        ->addArgument(
            'app_name',
            InputArgument::OPTIONAL,
            'The app you would like manage the domains for.'
        )
        ->addArgument(
            'environment_name',
            InputArgument::OPTIONAL,
            'The name of the environment.'
        )
        ->addArgument(
            'action',
            InputArgument::OPTIONAL,
            'empty to list, "add" to create, "remove" to delete.'
        )
        ->addArgument(
            'domain',
            InputArgument::OPTIONAL,
            'The domain name to act on.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Ask for an app.
        $helper = $this->getHelper('question');
        $this->getApp($input, $output);

        // Ask for an environment.
        $helper = $this->getHelper('question');
        $this->getEnvironment($input, $output);

    }
}
