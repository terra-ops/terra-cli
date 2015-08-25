<?php

namespace terra\Command\Environment;

use Symfony\Component\Console\Input\InputOption;
use terra\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
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
        else {
            $rebuild_source_config = $environment_factory->config['rebuild_source'];
            $output->writeln("Found <comment>rebuild_source: $rebuild_source_config</comment> in <fg=cyan>.terra.yml</> file...");
        }

        // Get source and target aliases
        $source_alias = $rebuild_source_argument? $rebuild_source_argument: $environment_factory->config['rebuild_source'];
        $target_alias = $environment_factory->getDrushAlias();

        // Mention to user where the alias is coming from.
        if ($rebuild_source_argument)
        {
            $output->writeln("Using terra command option for source alias: <fg=cyan>$rebuild_source_argument</>");
        }

        // Check that source doesn't equal target
        if ($source_alias == $target_alias) {
            throw new \Exception("You cannot use the same source and target (Source: $source_alias Target:$target_alias). Please check your config and try again.");
        }

        // Check ssh & sql access to both.
        $output->writeln('');
        $errors = FALSE;
        foreach (array($source_alias, $target_alias) as $alias) {
            $output->writeln("Checking access to alias <fg=cyan>$alias</> ...");

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
                $errors = TRUE;
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
                $errors = TRUE;
            }
            $output->writeln('');
        }

        // If errors, don't continue
        if ($errors) {
            throw new \Exception('We were unable to connect to both of your environments. Please check your config & try again.');
        }

        // Database Sync Confirmation
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion("Are you sure you want to destroy <fg=red>{$target_alias}</> and replace it with data from <fg=cyan>{$source_alias}</>? [y\N] ", false);
        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('<error>Database Cancelled</error>');
        }

        // Database Sync
        $cmd = "drush {$source_alias} sql-dump --gzip | gzip -cd | drush {$target_alias} sqlc";
        $output->writeln('');
        $output->writeln('Running...');
        $output->writeln("<comment>$cmd</comment>");

        $process = new Process($cmd);
        $process->setTimeout(NULL);
        $process->run();

        if ($process->isSuccessful()) {
            $output->writeln("<info>SUCCESS</info> Database Copied successfully!");
        }
        else {
            $output->writeln("<error>FAILURE</error> Database Copy Failed!");
        }

        // Files Sync Confirmation
        $helper = $this->getHelper('question');

        // Files Sync
        // Get Source Path
        $default_source = "$source_alias:%files";
        $default_target = "$target_alias:%files";

        $source_question = new Question("Source path? [$default_source] ", $default_source);
        $destination_question = new Question("Destination path? [$default_target] ", $default_target);

        $source = $helper->ask($input, $output, $source_question);
        $target = $helper->ask($input, $output, $destination_question);

        $cmd = "drush rsync $source $target -y";

        $question = new ConfirmationQuestion("Copy files from <fg=cyan>{$source_alias}</> to <fg=red>{$target_alias}</>? Any existing files will be overwritten [y\N] ", false);
        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('<error>Rebuild Cancelled</error>');
            return;
        }

        $output->writeln('');
        $output->writeln('Running...');
        $output->writeln("<comment>$cmd</comment>");

        $process = new Process($cmd);
        $process->setTimeout(NULL);
        $process->run();

        if ($process->isSuccessful()) {
            $output->writeln("<info>SUCCESS</info> Files copied successfully!");
        }
        else {
            $output->writeln("<error>FAILURE</error> Files did not copy successfully!");
        }
    }
}
