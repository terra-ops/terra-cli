<?php

namespace terra\Command\Environment;

use Symfony\Component\Console\Input\InputOption;
use terra\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Process\Process;
use terra\Factory\EnvironmentFactory;

class EnvironmentRebuild extends Command
{
    protected function configure()
    {
        $this
        ->setName('environment:rebuild')
        ->setDescription('Recreates and environment from the rebuild_source.')
        ->addArgument(
            'app_name',
            InputArgument::OPTIONAL,
            'The name the app to remove.'
        )
        ->addArgument(
            'environment_name',
            InputArgument::OPTIONAL,
            'The name the environment to remove.'
        )
        ->addOption(
            'source',
            '-s',
            InputOption::VALUE_OPTIONAL,
            'The drush alias to use instead of the one provided by .terra.yml.'
        )
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Ask for an app and environment.
        $this->getApp($input, $output);
        $this->getEnvironment($input, $output);

        // Don't continue unless we have an environment.
        if (empty($this->environment)) {
            return;
        }

        // Get Environment and Config
        $environment_factory = new EnvironmentFactory($this->environment, $this->app);
        $environment_factory->getConfig();

        // Get argument override
        $rebuild_source_argument = $input->getOption('source');

        // Check for config
        if (empty($environment_factory->config['rebuild_source'])) {
            if (empty($rebuild_source_argument)) {
                throw new \Exception("To run the 'environment:rebuild' command you must have 'rebuild_source: @drushalias' in your app's .terra.yml file (or specify the source drush alias with 'environment:rebuild --rebuild_source=@alias').");
            }
        }

        // Get source and target aliases
        $source_alias = $rebuild_source_argument? $rebuild_source_argument: $environment_factory->config['rebuild_source'];
        $target_alias = $environment_factory->getDrushAlias();

        // Check that source doesn't equal target
        if ($source_alias == $target_alias) {
            throw new \Exception("You cannot use the same source and target (Source: $source_alias Target:$target_alias). Please check your config and try again.");
        }

        // Check ssh & sql access to both.
        $output->writeln("Checking access to alias <comment>$source_alias</comment> ...");
        $output->writeln("Checking access to alias <comment>$target_alias</comment> ...");

        foreach (array($source_alias, $target_alias) as $alias) {
            $cmd = "drush $alias ssh 'echo \$SSH_CLIENT'";

            // SQL
            $process = new Process($cmd);
            $process->setTimeout(NULL);
            $process->run();

            if ($process->isSuccessful()) {
                $output->writeln("<info>SUCCESS</info> Connected to $alias via SSH. <comment>$cmd</comment>");
            }
            else {
                $output->writeln("<error>FAILURE</error> Unable to connect to $alias via SSH. <comment>$cmd</comment>");
            }

            // SQL
            $cmd = "drush $alias sql-query 'DESCRIBE system'";
            $process = new Process($cmd);
            $process->setTimeout(NULL);
            $process->run();

            if ($process->isSuccessful()) {
                $output->writeln("<info>SUCCESS</info> Connected to $alias via MySQL. <comment>$cmd</comment>");
            }
            else {
                $output->writeln("<error>FAILURE</error> Unable to connect to $alias via MySQL. <comment>$cmd</comment>");
            }

        }


//
//Drush alias @northdakota.current found!
//    Connected to @northdakota.current successfully!
//    Are you sure you would like to destroy the database from environment @nd.local?  It will be replaced with the database from @northdakota.current. [y/N] y
//Running drush @northdakota.current sql-dump --gzip | gzip -cd | drush @nd.local sqlc...

    }
}
