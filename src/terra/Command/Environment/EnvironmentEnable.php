<?php

namespace terra\Command\Environment;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use terra\Factory\EnvironmentFactory;

// ...

class EnvironmentEnable extends Command
{
    protected function configure()
    {
        $this
        ->setName('environment:enable')
        ->setDescription('Enable environment.')
        ->addArgument(
            'app_name',
            InputArgument::OPTIONAL,
            'The name the app to enable.'
        )
        ->addArgument(
            'environment_name',
            InputArgument::OPTIONAL,
            'The name the environment to enable.'
        )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // If there are no apps, return
        if (count($this->getApplication()->getTerra()->getConfig()->get('apps')) == 0) {
            $output->writeln('<comment>There are no apps to remove!</comment>');
            $output->writeln('Use the command <info>terra app:add</info> to add your first app.');

            return;
        }

        $helper = $this->getHelper('question');
        $app_name = $input->getArgument('app_name');
        $environment_name = $input->getArgument('environment_name');

        // If no name specified provide options
        if (empty($app_name)) {
            $question = new ChoiceQuestion(
                'Which app? ',
                array_keys($this->getApplication()->getTerra()->getConfig()->get('apps')),
                null
            );
            $app_name = $helper->ask($input, $output, $question);
        }

        $app = $this->getApplication()->getTerra()->getConfig()->get('apps', $app_name);

        // If no environments:
        if (count(($app['environments'])) == 0) {
            $output->writeln("<comment>There are no environments for the app $app_name!</comment>");
            $output->writeln('Use the command <info>terra environment:add</info> to add your first environment.');

            return;
        }

        // If no environment name specified provide options
        if (empty($environment_name)) {
            $question = new ChoiceQuestion(
                'Which environment? ',
                array_keys($app['environments']),
                null
            );
            $environment_name = $helper->ask($input, $output, $question);
        }

        $environment = $app['environments'][$environment_name];

        // Attempt to enable the environment.
        $environment_factory = new EnvironmentFactory($environment, $app);
        if (!$environment_factory->enable()) {
            $output->writeln('<error>Something went wrong, environment not enabled.</error>');

            return;
        }

        // Get new port, set new URL to environment object.
        $port = $environment_factory->getPort();
        $app['environments'][$environment_name]['url'] = "http://localhost:$port";

        // Save environment metadata.
        $this->getApplication()->getTerra()->getConfig()->add('apps', array($app_name, 'environments', $environment_name), $app['environments'][$environment_name]);

        // Save config to files.
        if ($this->getApplication()->getTerra()->getConfig()->save()) {
            $output->writeln('<info>Environment enabled!</info>  Available at http://'.$environment_factory->getUrl().' and '.$app['environments'][$environment_name]['url']);
        } else {
            $output->writeln('<error>Environment info not saved.</error>');
        }

        // Write drush alias.
        $drush_alias_file_path = "{$_SERVER['HOME']}/.drush/{$app_name}.aliases.drushrc.php";
        if ($environment_factory->writeDrushAlias()) {
            $output->writeln("<info>Drush alias file created at {$drush_alias_file_path}</info>");
            $output->writeln("Wrote drush alias file to <comment>$drush_alias_file_path</comment>");
            $output->writeln("Use <info>drush @{$app_name}.{$environment_name}</info> to access the site.");
        } else {
            $output->writeln('<error>Unable to save drush alias.</error>');
        }

        // Run the enable hooks
        $output->writeln('');
        $output->writeln('Running <comment>ENABLE</comment> app hook...');

        $environment_factory->getConfig();
        if (!empty($environment_factory->config['hooks']['enable'])) {
            // Output what we are running
            $formatter = $this->getHelper('formatter');
            $errorMessages = array($environment_factory->config['hooks']['enable']);
            $formattedBlock = $formatter->formatBlock($errorMessages, 'question');
            $output->writeln($formattedBlock);

            chdir($environment_factory->getSourcePath());
            $process = new Process($environment_factory->config['hooks']['enable']);
            $process->run(function ($type, $buffer) {
                if (Process::ERR === $type) {
                    echo $buffer;
                } else {
                    echo $buffer;
                }
            });
        }
    }
}
