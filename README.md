# Terra

![Terra command line interface](https://pbs.twimg.com/media/CHj2HvyUYAAaivy.png:large)

## Documentation & Issues

[Read the Docs](http://terra.readthedocs.org/) or help [improve the docs](https://github.com/terra/devshop/edit/0.x/README.md).

[Issues](https://github.com/terra-ops/terra-app/issues) and [Story Boarding](https://huboard.com/terra-ops/terra-app/)

## Origin

Terra is the spiritual successor to Aegir & DevShop. It came from the idea that we could benefit from starting over. 

The feeling was with modern libraries like symfony, ansible, and docker we would be able to do a lot quickly, and, well I've been able to do a lot, quickly.  

Now I am on a mission to call everyone to action to work behind a common tool for us all.

## Purpose

To make having a website as simple as possible throughout it's entire lifetime, through entirely open source software.

Terra is a human interface for working on, deploying, testing, and scaling web software projects. 

Terra makes it quick and painless to manage your projects and environments.  

Push a button to get a testing infrastructure.

## "Apps"

An "App" is your website. It is the source code for your project.  Terra knows the git URL and (will) know the available branches and tags.  Terra will help you update your app from it's upstream repository using git.

## "Environment"

The Environment is all of the systems needed to run the source code.

The "EnvironmentFactory" class will be pluggable.  Out of the box it provides a working docker cluster, but we can extend it to work with a "multiple apache vhost" model or with a different container provider.

Users will be able to use terra to control environments hosted by multiple hosting providers, including localhost all through the same interface.

## Community & Collaboration is Key

We wish to make this tool work for everyone.  We wish to get feedback from all parties interested in solving these problems in order to prioritize what to work on.

Please join us in the Issue Queues on GitHub and the chat rooms on gitter.

## Scalable Out of the Box

We wanted to start from scalable.  Terra's purpose is to make scaling a push button affair.

Currently Terra uses Docker and Docker compose to stand up and scale websites.  

This makes it easy to get environments running quickly on hosted servers or on local computers for development.

## Planning

See https://huboard.com/terra-ops/terra-app/ for the user story board.

## Requirements

- **docker** 
- **docker-compose**

## Commands

#### `terra status`
  List all apps on this system.

#### `terra app:add` 
  Add a new app to the system.
  
  Currently only Drupal sites are supported.
  
#### `terra app:remove`
  Remove an app from the system
  
#### `terra environment:add`
  Add an environment for an app.
  
#### `terra environment:remove`
  Remove an environment.

#### `terra environment:enable`
  Runs `docker-compose up` to initiate an environment.
  
#### `terra environment:status`
  Provides status information about an environment, including path and URL.

# History

Some of the R&D for Terra happened in a project called "director": https://github.com/jonpugh/director-drupal

Director is now deprecated.

# Comparisons to Kalabox

There is a striking similarity to this project and Kalabox.   

We love the concept of Kalabox and the Kalamuna team, but there are a fey key differences:

1. Terra is designed for all things: local development, testing, and production.
2. Terra is written in PHP & Symfony: Kalabox is written in Node JS
3. Terra is the successor to Aegir & devshop.  We hope to recruit a large community from those tools.


