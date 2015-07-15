<?php

namespace terra\Command\Environment;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use terra\Factory\EnvironmentFactory;

class EnvironmentScale extends Command
{
    protected function configure()
    {
        $this
        ->setName('environment:scale')
        ->setDescription('Scale the app container.')
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
        ->addArgument(
            'scale',
            InputArgument::OPTIONAL,
            'The number of app containers to run.'
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
        $scale = $input->getArgument('scale');

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
        $environment_factory->writeConfig();
        $current_scale = $environment_factory->getScale();

        $output->writeln("Scaling Environment <comment>{$app_name} {$environment_name}</comment>...");
        $output->writeln("Current scale: <comment>{$current_scale}</comment>");

        // If no scale ask for scale.
        if (empty($scale)) {
            $question = new Question(
                'How many app containers? '
            );
            $scale = $helper->ask($input, $output, $question);
        }
        $output->writeln("Target scale: <comment>{$scale}</comment>");

        $environment_factory->scale($scale);

        $output->writeln("Environment <comment>{$app_name} {$environment_name}</comment> scaled to <info>{$scale}</info>");
    }
}
