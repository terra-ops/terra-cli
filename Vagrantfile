# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
  config.vm.define 'terra' do |t|
    t.vm.box = "ubuntu/trusty64"
    t.vm.hostname = "terra"
    t.vm.network "private_network", ip: "192.168.33.10"

    # Run the terra install script
    t.vm.provision "shell",
      path: "install.sh"

    # Add "vagrant" user to the docker group.
    t.vm.provision "shell",
      inline: "usermod -aG docker vagrant"
  end
end
