<?php

namespace terra\Command\Environment;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Process\Process;
use terra\Command\Command;
use terra\Factory\EnvironmentFactory;

class EnvironmentTest extends Command
{
    protected function configure()
    {
        $this
        ->setName('environment:test')
        ->setDescription('Run tests on the environment.')
        ->addArgument(
            'app_name',
            InputArgument::OPTIONAL,
            'The name the app.'
        )
        ->addArgument(
            'environment_name',
            InputArgument::OPTIONAL,
            'The name the environment.'
        )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Hello Terra!');

        // If there are no apps, return
        if (count($this->getApplication()->getTerra()->getConfig()->get('apps')) == 0) {
            $output->writeln('<comment>There are no apps!</comment>');
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

        if (empty($app)) {
            $output->writeln('<error>No app by that name!</error>');
            $output->writeln('Use the command <info>terra app:add</info> to add your first app.');

            return;
        }

        // If no environments:
        if (count(($app['environments'])) == 0) {
            $output->writeln('<comment>There are no environments!</comment>');
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

        $app = $this->getApplication()->getTerra()->getConfig()->get('apps', $app_name);
        $environment = $app['environments'][$environment_name];
        $environment_factory = new EnvironmentFactory($environment, $app);

        $environment_factory->getConfig();

        // Run the tests
        // @TODO: Move to factory.

        foreach ($environment_factory->config['hooks']['test'] as $hook) {
            $output->writeln('<info>TERRA</info> | <comment>Test: Start...</comment>');
            $output->writeln('<info>TERRA</info> | '.$hook);

            // Set environment variables for behat tests
            $env = array();
            $env['HOME'] = $_SERVER['HOME'];

            $behat_vars = array(
              'extensions' => array(
                'Behat\MinkExtension' => array(
                  'base_url' => 'http://'.$environment_factory->getUrl(),
                ),
              ),
            );

            $env['BEHAT_PARAMS'] = json_encode($behat_vars);

            $process = new Process($hook, $environment_factory->getSourcePath(), $env);
            $process->run(function ($type, $buffer) {
                if (Process::ERR === $type) {
                    echo $buffer;
                } else {
                    echo $buffer;
                }
            });

            if (!$process->isSuccessful()) {
                $output->writeln('<info>TERRA</info> | <fg=red>Test Failed</> '.$hook);
            } else {
                $output->writeln('<info>TERRA</info> | <info>Test Passed: </info> '.$hook);
            }
            $output->writeln('');
        }
    }
}
