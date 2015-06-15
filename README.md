# Terra

Terra is the spiritual successor to Aegir & DevShop.

Terra uses Docker and Docker compose to stand up websites.

Terra is being designed to work both as a local development solution and as a scalable production hosting platform.

Terra is in it's infancy, but we encourage you to try it out.

Terra's mission is to make web development as easy and streamlined as possible.

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
