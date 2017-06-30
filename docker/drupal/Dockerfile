# Officially maintained by Docker.
FROM drupal:7-apache

# terra/drupal (this file) maintained by THINKDROP
MAINTAINER Jon Pugh <jon@thinkdrop.net>

# ARG allows values to be passed from `docker build` command.
# Use `id -u` to determine your current UID.
# Terra CLI will build these for us soon, but for now, use this command:
# `docker build -t terra/drupal --build-arg TERRA_UID=501 .`
ARG TERRA_UID=1000

# TERRA_GID will default to TERRA_UID, unless passed as a build-arg to `docker-build`.
ARG TERRA_GID=$TERRA_UID

ENV TERRA_USER=terra
ENV TERRA_HOME=/var/terra

# We need to install sudo so we can allow terra user to start apache.
# @TODO: Add this to a new upstream image.
RUN apt-get -qq update && apt-get -qq -y install \
  git \
  sudo \
  zip

RUN echo "Creating user $TERRA_USER with UID $TERRA_UID and GID $TERRA_GID and HOME of $TERRA_HOME"
RUN addgroup --gid $TERRA_UID $TERRA_USER
RUN adduser --uid $TERRA_UID --gid $TERRA_GID --system --home $TERRA_HOME $TERRA_USER
RUN adduser $TERRA_USER www-data

RUN echo "Granting sudo apache2-foreground access to $TERRA_USER"
RUN echo "Defaults:$TERRA_USER !requiretty \n$TERRA_USER ALL=NOPASSWD: /usr/local/bin/apache2-foreground" >> /etc/sudoers.d/$TERRA_USER

RUN cat /etc/sudoers.d/$TERRA_USER

USER $TERRA_USER
WORKDIR $TERRA_HOME
VOLUME $TERRA_HOME
VOLUME /var/www/html/
EXPOSE 80

# Default for drupal:7-apache is apache2-foreground. We want to run as Terra user so we use sudo.
CMD ["sudo", "apache2-foreground"]