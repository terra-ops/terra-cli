# Terra

Terra is the spiritual successor to Aegir & DevShop.

Terra uses Docker and Docker compose to stand up websites.

Terra is being designed to work both as a local development solution and as a scalable production hosting platform.

Terra is in it's infancy, but we encourage you to try it out.

Terra's mission is to make web development as easy and streamlined as possible.

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


