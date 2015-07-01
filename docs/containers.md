Terra Containers
================

Terra creates a group of containers for every environment using Docker Compose.

Terra dynamically creates a `docker-compose.yml` file for each environment in the `~/.terra/environments` folder.

The default container arrangement is a proof of concept for a scalable, distributed arrangement of services for a typical website.  

However the plan is to allow each project to override parts or the entire docker-compose configuration in the app source code itself.

Default containers
------------------

### Load

This container is the endpoint for each environment. 

The tutum/haproxy image is what makes the `terra environment:scale` command possible.  After the app containers are scaled up, the "load" container is restarted and instantly connected to all of the app containers.

Setting the `VIRTUAL_HOST` environment variable will make it possible to lookup that enviromment by a URL rather than on a random port.

### App
Container: `terra/drupal` 
Docker Hub: https://registry.hub.docker.com/u/terra/drupal/
Source code: https://github.com/terra-ops/docker-drupal

App is the scalable NGINX container.  It is linked to the `load` container, so when you add more, they are automatically added to the `load` container's server list.

Using the command `terra environment:scale project environment 5` command will pass through to `docker-compose scale app=5'.  Currently terra is hard coded to scale the app container, but with help, we can implement many different methods of scaling.
