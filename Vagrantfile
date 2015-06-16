# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
  config.vm.define 'terra' do |t|
    t.vm.box = "ubuntu/trusty64"
    t.vm.network "private_network", ip: "192.168.33.10"

    t.vm.provision "bootstrap",
      type: "shell",
      keep_color: true,
      inline: <<-SHELL
      apt-get update
      apt-get -y install php5-cli wget
      wget -qO- https://get.docker.com/ | sh
      sudo usermod -aG docker your-user
      curl -L https://github.com/docker/compose/releases/download/1.2.0/docker-compose-`uname -s`-`uname -m` > /usr/local/bin/docker-compose
      chmod +x /usr/local/bin/docker-compose
      curl -sS https://getcomposer.org/installer | php
      mv composer.phar /usr/local/bin/composer
      ln -s /usr/local/bin/composer /usr/bin/composer
      composer global require terra/terra-app:dev-master
      cd ~/.composer/vendor/terra/terra-app
      composer install
      ln -s ~/.composer/vendor/terra/terra-app/terra /usr/bin/terra
    SHELL
  end
end
