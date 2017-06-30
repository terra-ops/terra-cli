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

RUN echo "Creating user $TERRA_USER with UID $TERRA_UID and GID $TERRA_GID and HOME of $TERRA_HOME"
RUN addgroup --gid $TERRA_UID $TERRA_USER
RUN adduser --uid $TERRA_UID --gid $TERRA_GID --system --home $TERRA_HOME $TERRA_USER
RUN adduser $TERRA_USER www-data

USER $TERRA_USER
WORKDIR $TERRA_HOME
VOLUME $TERRA_HOME
VOLUME /var/www/html/
EXPOSE 80