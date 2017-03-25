<?php

namespace terra\Command;

class AppCommands extends Command {
  
  /**
   * Add an application to the system.
   *
   * @param string $name The system name of the app.
   * @param string $repository_url URL of the git repository.
   * @param string $description Description of the app (optional).
   */
  public function appAdd($name = NULL, $repository_url = NULL, $description = NULL) {
    
    $this->getAnswer($name, 'System name of the app: ');
    $this->getAnswer($repository_url, 'Git repository of the URL: ');
    $this->getAnswer($description, 'Description: ');
    
    $this->say("Adding $name with $repository_url and $description");
    
    $app = [
      'name' => $name,
      'description' => $description,
      'repo' => $repository_url,
    ];
    
    $this->config->add('apps', $name, $app);
    $this->config->save();
  }
}