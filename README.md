Director
========

Director is a console tool for orchestrating your server & your software.

Coupled with Ansible, Director makes it easy to track and manage large numbers of servers.

More coming soon.


Next Steps
----------

role:add

  Adds to the available server roles.
  Adds the role to .playbook.yml

server:assign

  Assigns a role to a server.
  Adds the server to .inventory

server:unassign

  Unassigns a role to a server.
  Removes the server from .inventory

director:direct

  Runs ansible-playbook using current .playbook.yml and .inventory.
  Updates all of the servers.
