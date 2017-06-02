<?php

namespace Terra\Commands;

use Terra\Commands;

class AppCommands extends Commands {
  
  /**
   * Adds a new app.
   *
   * @param string $name The system name of the app.
   * @param string $repository_url URL of the git repository.
   * @param string $description Description of the app (optional).
   */
  public function appAdd($name = NULL, $repository_url = NULL, $description = NULL) {
  
    $this->getAnswer($name, 'System name of the app: ');
    $this->getAnswer($repository_url, 'Git repository of the URL: ');
    $this->getAnswer($description, 'Description: ');
  
    $description_label = empty($description)? 'none': $description;
    $this->say("Adding app:$name with URL:$repository_url and description: $description_label");
  
    $app = [
      'name' => $name,
      'description' => $description,
      'repo' => $repository_url,
    ];
  
    $this->getConfig()->add('apps', $name, $app);
    $this->getConfig()->save();
  }
}
