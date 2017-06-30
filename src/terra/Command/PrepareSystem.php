<?php

namespace terra\Command;

use terra\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Process;

class PrepareSystem extends Command
{
    protected function configure()
    {
        $this
        ->setName('prepare:system')
        ->setDescription('Builds app containers for your system and launches URL Proxy, among other things.')
        ->addArgument(
            'uid',
            InputArgument::OPTIONAL,
            'The UID to use when generating the container users. Defaults to the current user.'
        )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
      $output->writeln([
        "First, I need a couple of things:",
        "  1. A web container built to match your user, so volume mounts match your control user.",
        "  2. A local URL proxy (jwilder/nginx-proxy), so requests to local URLs can resolve to individual containers.",
        ""
      ]);

      if (!$input->getArgument('uid')) {
        $uid = trim(shell_exec('id -u'));
        if (empty($uid) || !intval($uid)) {
          throw new \Exception("UID not found. The command `id -u` failed and returned `$uid`. Please pass your user's UID as an argument to this command: `terra prepare:system 1100`");
        }
        $output->writeln([
          "No UID argument entered, so I looked. Your ID was found to be $uid.",
        ]);
      }
      else {
        $uid = $input->getArgument('uid');
      }

      // Build app containers.
      $path_to_drupal_docker = realpath(__DIR__ . '/../../../docker/drupal');
      $cmd = "docker build -t terra/drupal:local --build-arg TERRA_UID={$uid} {$path_to_drupal_docker}";

      $helper = $this->getHelper('question');
      $question = new ConfirmationQuestion("I'd like to run `$cmd`  Ok?  ", false);

      // If yes, gather the necessary info for creating .terra.yml.
      if ($helper->ask($input, $output, $question)) {

        $output->writeln([
          "Great! running...",
        ]);

        $process = new Process($cmd);
        $process->setTimeout(null);
        $process->run(function ($type, $buffer) {
          if (Process::ERR === $type) {
            echo 'DOCKER > '.$buffer;
          } else {
            echo 'DOCKER > '.$buffer;
          }
        });

        if ($process->isSuccessful()) {

          $output->writeln([
            '',
            "<info>Ok! Your Docker host now has an image for terra/drupal:local</info>",
            "I'll use this image to launch your Drupal environments.",
            '',
          ]);
        }
        else {

          $output->writeln([
            "",
            "<fg=red>Uh oh! The `docker-build` command failed!</>",
            "The command I tried to run was: ",
            "<comment>{$cmd}</comment>",
            "",
            "Please check your settings, try to run the command manually, then try again."
          ]);

          exit(1);
        }
      }
      else {
        throw new \Exception("Hmm, sorry then. Terra can't work properly without ");
      }

      $output->writeln([
        "",
        "Next up: URL Proxy. I need to launch a container with jwilder/nging-proxy for this to work.",
        "You can review the Docker image here: https://hub.docker.com/r/jwilder/nginx-proxy/",
        "",
      ]);

      $process = new Process('docker inspect terra-nginx-proxy');
      $process->setTimeout(null);
      $process->run();
      if ($process->isSuccessful()) {

        $output->writeln([
          "",
          "Wait a minute, it looks like you already have a container 'terra-nginx-proxy',",
        ]);

        $process = new Process('docker ps --filter name=terra-nginx-proxy -q');
        $process->run();
        // docker ps with filter outputs empty but OK if there are no containers.
        if (!empty($process->getOutput())) {
          $output->writeln([
            "and it is running.",
            "I can remove it for you by running `$cmd`... We can just launch a new one. ",
          ]);

          // Ask to remove it.
          $cmd = 'docker kill terra-nginx-proxy; docker rm -fv terra-nginx-proxy';
          $question = new ConfirmationQuestion("Ok?  [y/N]", false);
          if ($helper->ask($input, $output, $question)) {
            $process = new Process($cmd);
            $process->run(function ($type, $buffer) {
              if (Process::ERR === $type) {
                echo 'DOCKER > '.$buffer;
              } else {
                echo 'DOCKER > '.$buffer;
              }
            });

            if (!$process->isSuccessful()) {
              $output->writeln([
                "",
                "<fg=red>Uh oh! The `docker kill` and `docker rm` commands failed!</>",
              ]);
            }
          }
        }
        else {
          $output->writeln([
            "but it is not running. Would you like me to remove it before starting a new container?",
          ]);

          // Ask to remove it.
          $cmd = 'docker rm -fv terra-nginx-proxy';
          $question = new ConfirmationQuestion("Would you like me to stop and remove it by running `$cmd`? We can just launch anotherone next. Ok?  ", false);
          if ($helper->ask($input, $output, $question)) {
            $process = new Process($cmd);
            $process->run(function ($type, $buffer) {
              if (Process::ERR === $type) {
                echo 'DOCKER > '.$buffer;
              } else {
                echo 'DOCKER > '.$buffer;
              }
            });

            if (!$process->isSuccessful()) {
              $output->writeln([
                "",
                "<fg=red>Uh oh! The `docker kill` and `docker rm` commands failed!</>",
              ]);
            }
          }
        }
      }

      $cmd = 'docker run --name terra-nginx-proxy -d -p 80:80 -v /var/run/docker.sock:/tmp/docker.sock:ro --security-opt label:disable jwilder/nginx-proxy';

      $output->writeln([
        "I'd like to run `$cmd`  "
      ]);

      $helper = $this->getHelper('question');
      $question = new ConfirmationQuestion("Ok?  [Y/n]", false);

      // If yes, gather the necessary info for creating .terra.yml.
      if ($helper->ask($input, $output, $question)) {

        $process = new Process($cmd);
        $process->setTimeout(null);
        $process->run(function ($type, $buffer) {
          if (Process::ERR === $type) {
            echo 'DOCKER > '.$buffer;
          } else {
            echo 'DOCKER > '.$buffer;
          }
        });

        if ($process->isSuccessful()) {

          $output->writeln([
            "",
            "Ok, that worked! You can now use VIRTUAL_HOST domain names to load sites.",
            "",
          ]);
        }
        else {
          throw new \Exception("Ouch. Something went wrong when running the command. Review and try again.");
        }
      }
    }
}