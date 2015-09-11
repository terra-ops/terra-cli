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
        // Check if "app" argument is an action.
        if ($input->getArgument('app_name') == 'add' || $input->getArgument('app_name') == 'remove') {
            // Move argument from app_name to action
            $input->setArgument('action', $input->getArgument('app_name'));
            $input->setArgument('app_name', '');

            // Move argument from env_name to domain
            $input->setArgument('domain', $input->getArgument('environment_name'));
            $input->setArgument('environment_name', '');
        }

        // Ask for an app.
        $helper = $this->getHelper('question');
        $this->getApp($input, $output);

        // Ask for an environment.
        $helper = $this->getHelper('question');
        $this->getEnvironment($input, $output);

        // If action argument is empty, show the list.
        if (empty($input->getArgument('action'))) {

            $environment = new EnvironmentFactory($this->environment, $this->app);
            $rows[] = array('http://'. $environment->getHost() . ':' . $environment->getPort());
            $rows[] = array('http://' . $environment->getUrl());

            // Get all domains
            foreach ($environment->environment->domains as $domain) {
                $rows[] = array('http://' . $domain);
            }

            $table = $this->getHelper('table');
            $table
                ->setHeaders(array("Domains for {$this->app->name} {$this->environment->name}"))
                ->setRows($rows)
            ;
            $table->render($output);
            return;
        }
        elseif ($input->getArgument('action') == 'add') {
            $output->writeln('Adding a domain...');
            $this->executeAddDomain($input, $output);
        }

    }

    /**
     * Add a domain.
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function executeAddDomain(InputInterface $input, OutputInterface $output) {

        // Ask for a domain
        $domain_question = new Question('What domain would you like to add as a VIRTUAL_HOST for the server? (Do NOT include http://) ');
        $name = $this->getAnswer($input, $output, $domain_question, 'domain', 'argument', TRUE);

        // Add the domain to the domain property.
        $output->writeln("Adding domain: <info>{$name}</info>");

        $this->environment->domains[] = $name;

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion("Add the domain <comment>$name</comment> to the environment <info>{$this->app->name}:{$this->environment->name}</info> [y/N]? ", FALSE);

        if (!$helper->ask($input, $output, $question)) {
            $output->writeln("<fg=red>Domain not added.</>");
            $output->writeln('');
            return;
        }

        // Save the new version to the config.
        $this->getApplication()->getTerra()->getConfig()->add('apps', array($this->app->name, 'environments', $this->environment->name), (array) $this->environment);
        $this->getApplication()->getTerra()->getConfig()->save();
        $output->writeln("<info>Domain added!</info>");
        $output->writeln('');
    }
}
