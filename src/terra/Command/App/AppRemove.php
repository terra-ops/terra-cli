<?php

namespace terra\Command\App;

use terra\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\ChoiceQuestion;

class AppRemove extends Command
{
    protected function configure()
    {
        $this
        ->setName('app:remove')
        ->setDescription('Removes an app.')
        ->addArgument(
            'name',
            InputArgument::OPTIONAL,
            'The name the app to remove.'
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
        $name = $input->getArgument('name');

        // If no name specified provide options
        if (empty($name)) {
            $question = new ChoiceQuestion(
                'Which app would you like to remove? ',
                array_keys($this->getApplication()->getTerra()->getConfig()->get('apps')),
                null
            );
            $name = $helper->ask($input, $output, $question);
        }

        // Confirm removal of the app.
        $question = new ConfirmationQuestion("Are you sure you would like to remove the app <question>$name</question>? [y/N] ", false);
        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('<error>Cancelled</error>');

            return;
        } else {
            $this->getApplication()->getTerra()->getConfig()->remove('apps', $name);
            $this->getApplication()->getTerra()->getConfig()->save();
            $output->writeln("<info>App $name has been removed.</info>");

            // @TODO: Remove all environments and files associated with this app.
        }
    }
}
