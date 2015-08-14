# Common Terra and Docker Commands

```
# Get a list of terra commands
terra

# Create Terra app
terra app:add drupal https://github.com/terra-ops/example-drupal

# Create Terra environment
terra environment:add drupal local
# or
terra e:a drupal local

# Enable Terra environment
terra environment:enable drupal local
# or
terra e:e drupal local

# Drush alias will be created:
# @drupal.local


# View logfiles
docker logs drupallocal_app_1

# Show docker containers
docker ps

# SSH in to docker container
docker exec -it drupallocal_app_1 bash

# Kill docker container
docker kill [name]

# Clean up docker files
# From https://meta.discourse.org/t/low-on-disk-space-cleaning-up-old-docker-containers/15792
docker rm `docker ps -a | grep Exited | awk '{print $1 }'`
docker rmi `docker images -aq`


```
