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

# This will be updated as drush is released.
ARG DRUSH_VERSION=8.1.12

ENV TERRA_USER=terra
ENV TERRA_HOME=/var/terra

# We need to install sudo so we can allow terra user to start apache.
# @TODO: Add this to a new upstream image.
RUN apt-get -qq update && apt-get -qq -y install \
  git \
  mysql-client \
  php5-mysql \
  sudo \
  zip

RUN echo "Creating user $TERRA_USER with UID $TERRA_UID and GID $TERRA_GID and HOME of $TERRA_HOME"
RUN addgroup --gid $TERRA_UID $TERRA_USER
RUN adduser --uid $TERRA_UID --gid $TERRA_GID --system --home $TERRA_HOME $TERRA_USER
RUN adduser $TERRA_USER www-data

RUN echo "Granting sudo apache2-foreground access to $TERRA_USER"
RUN echo "Defaults:$TERRA_USER !requiretty \n$TERRA_USER ALL=NOPASSWD: /usr/local/bin/apache2-foreground" >> /etc/sudoers.d/$TERRA_USER

# Add our own Apache configuration
COPY apache-vhost.conf /etc/apache2/sites-available/000-default.conf

# Set ownership of /etc/apache2/envvars so hubdrop-entrypoint.sh can write to it
RUN chown $TERRA_USER /etc/apache2/envvars

# Prepare log file
RUN touch /var/log/terra
RUN chown $TERRA_USER /var/log/terra
RUN chgrp $TERRA_USER /var/log/terra

# Add our own entrypoint
COPY terra-entrypoint.sh /usr/local/bin/terra-entrypoint
RUN chmod +x /usr/local/bin/terra-entrypoint

# Add composer CLI
# Instructions from from https://getcomposer.org/download/
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php -r "if (hash_file('SHA384', 'composer-setup.php') === '669656bab3166a7aff8a7506b8cb2d1c292f042046c5a994c43155c0be6190fa0355160742ab2e1c88d40d5be660b410') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"  && \
    php composer-setup.php  && \
    php -r "unlink('composer-setup.php');" && \
    mv composer.phar /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer && \
    composer

# Install Drush. We will handle site-local drush soon.
RUN echo "Installing drush version $DRUSH_VERSION"
RUN curl https://github.com/drush-ops/drush/releases/download/$DRUSH_VERSION/drush.phar -L -O -s && \
    mv drush.phar /usr/local/bin/drush && \
    chmod +x /usr/local/bin/drush && \
    drush

RUN cat /etc/sudoers.d/$TERRA_USER

USER $TERRA_USER
WORKDIR $TERRA_HOME
VOLUME $TERRA_HOME
VOLUME /var/www/html/
EXPOSE 80

# Default for drupal:7-apache is apache2-foreground. We want to run as Terra user so we use sudo.
CMD ["/usr/local/bin/terra-entrypoint"]