#!/bin/bash

# Terra Install Script for UBUNTU
# WORK IN PROGRESS!
# This script is based on the Install Instructions for terra.

# See https://github.com/terra-ops/terra-app/blob/master/docs/install.md


# Update Apt, Install PHP and Git
apt-get update
apt-get install php5-cli git

# Install Composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer4

# Install Docker and Docker Compose
wget -qO- https://get.docker.com/ | sh
curl -L https://github.com/docker/compose/releases/download/1.2.0/docker-compose-`uname -s`-`uname -m` > /usr/local/bin/docker-compose

# Install Terra Manually
git clone https://github.com/terra-ops/terra-app.git /usr/share/terra
cd /usr/share/terra
composer install
ln -s /usr/share/terra/bin/terra /usr/local/bin/terra

# Notify User
echo "==========================================================="
echo " Terra has been installed! "
echo " You should add the user you will use terra with to the docker group."
echo " Run the following command:"

echo " $ usermod -aG docker your_user "
echo " "
echo " Thanks! If you have any issues, please submit to https://github.com/terra-ops/terra-app/issues"
echo ""
echo " Now run 'terra' to ensure that it installed correctly."
echo "==========================================================="
echo " "

