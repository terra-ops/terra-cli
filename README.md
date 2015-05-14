Director
========

Director is a console tool for orchestrating your server & your software.

Coupled with Ansible, Director makes it easy to track and manage large numbers of servers.

More coming soon.

Current Commands
----------------

- status

  Outputs the current servers and apps in the registry.

- server:add

  Adds a server to the registry.

- app:add

  Adds an app to the registry.

- app:update

  Update the apps information.

- app:init

  Clones the app's source code to the desired path.


Next Steps
----------

- role:add

  Adds a role to the registry.
  Adds the role to .playbook.yml

- server:assign

  Assigns a role to a server.
  Adds the server to .inventory

- server:unassign

  Unassigns a role to a server.
  Removes the server from .inventory

- director:direct

  Runs ansible-playbook using current .playbook.yml and .inventory.
  Updates all of the servers.

Installation
------------

We are still working on getting director to work out of the box.
 
 These environment variables are needed after you clone this repo and run `composer install`:
 
 PATH=$PATH:/vagrant/director/vendor/bin
 
 export PYTHONPATH=/vagrant/director/vendor/jonpugh/ansible/lib:
 export ANSIBLE_LIBRARY=/vagrant/director/vendor/jonpugh/ansible/library
 export MANPATH=/vagrant/director/vendor/jonpugh/ansible/docs/man:
 export ANSIBLE_HOSTS=/vagrant/director/inventory
