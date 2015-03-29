<?php

namespace AnsibleWrapper;

use Symfony\Component\Process\ProcessUtils;

/**
 * Interacts with a working copy.
 *
 * All commands executed via an instance of this class act on the working copy
 * that is set through the constructor.
 */
class AnsibleWorkingCopy
{
    /**
     * The AnsibleWrapper object that likely instantiated this class.
     *
     * @var \AnsibleWrapper\AnsibleWrapper
     */
    protected $wrapper;

    /**
     * Path to the directory containing the working copy.
     *
     * @var string
     */
    protected $directory;

    /**
     * The output captured by the last run Ansible commnd(s).
     *
     * @var string
     */
    protected $output = '';

    /**
     * A boolean flagging whether the repository is cloned.
     *
     * If the variable is null, the a rudimentary check will be performed to see
     * if the directory looks like it is a working copy.
     *
     * @param bool|null
     */
    protected $cloned;

    /**
     * Constructs a AnsibleWorkingCopy object.
     *
     * @param \AnsibleWrapper\AnsibleWrapper $wrapper
     *   The AnsibleWrapper object that likely instantiated this class.
     * @param string $directory
     *   Path to the directory containing the working copy.
     */
    public function __construct(AnsibleWrapper $wrapper, $directory)
    {
        $this->wrapper = $wrapper;
        $this->directory = $directory;
    }

    /**
     * Returns the AnsibleWrapper object that likely instantiated this class.
     *
     * @return \AnsibleWrapper\AnsibleWrapper
     */
    public function getWrapper()
    {
        return $this->wrapper;
    }

    /**
     * Gets the path to the directory containing the working copy.
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Gets the output captured by the last run Ansible commnd(s).
     *
     * @return string
     */
    public function getOutput()
    {
        $output = $this->output;
        $this->output = '';
        return $output;
    }

    /**
     * Clears the stored output captured by the last run Ansible command(s).
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     */
    public function clearOutput()
    {
        $this->output = '';
        return $this;
    }

    /**
     * Manually sets the cloned flag.
     *
     * @param boolean $cloned
     *   Whether the repository is cloned into the directory or not.
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     */
    public function setCloned($cloned)
    {
        $this->cloned = (bool) $cloned;
        return $this;
    }

    /**
     * Checks whether a repository has already been cloned to this directory.
     *
     * If the flag is not set, test if it looks like we're at a ansible directory.
     *
     * @return boolean
     */
    public function isCloned()
    {
        if (!isset($this->cloned)) {
            $ansibleDir = $this->directory;
            if (is_dir($ansibleDir . '/.ansible')) {
                $ansibleDir .= '/.ansible';
            };
            $this->cloned = (is_dir($ansibleDir . '/objects') && is_dir($ansibleDir . '/refs') && is_file($ansibleDir . '/HEAD'));
        }
        return $this->cloned;
    }

    /**
     * Runs a Ansible command and captures the output.
     *
     * @param array $args
     *   The arguments passed to the command method.
     * @param boolean $setDirectory
     *   Set the working directory, defaults to true.
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     *
     * @throws \AnsibleWrapper\AnsibleException
     *
     * @see AnsibleWrapper::run()
     */
    public function run($args, $setDirectory = true)
    {
        $command = call_user_func_array(array('AnsibleWrapper\AnsibleCommand', 'getInstance'), $args);
        if ($setDirectory) {
            $command->setDirectory($this->directory);
        }
        $this->output .= $this->wrapper->run($command);
        return $this;
    }

    /**
     * @defgroup command_helpers Ansible Command Helpers
     *
     * Helper methods that wrap common Ansible commands.
     *
     * @{
     */

    /**
     * Returns the output of a `ansible status -s` command.
     *
     * @return string
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function getStatus()
    {
        return $this->wrapper->ansible('status -s', $this->directory);
    }

    /**
     * Returns true if there are changes to commit.
     *
     * @return bool
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function hasChanges()
    {
        $output = $this->getStatus();
        return !empty($output);
    }

    /**
     * Returns a AnsibleBranches object containing information on the repository's
     * branches.
     *
     * @return AnsibleBranches
     */
    public function getBranches()
    {
        return new AnsibleBranches($this);
    }

    /**
     * Helper method that pushes a tag to a repository.
     *
     * This is synonymous with `ansible push origin tag v1.2.3`.
     *
     * @param string $tag
     *   The tag being pushed.
     * @param string $repository
     *   The destination of the push operation, which is either a URL or name of
     *   the remote. Defaults to "origin".
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @see AnsibleWorkingCopy::push()
     */
    public function pushTag($tag, $repository = 'origin', array $options = array())
    {
        return $this->push($repository, 'tag', $tag, $options);
    }

    /**
     * Helper method that pushes all tags to a repository.
     *
     * This is synonymous with `ansible push --tags origin`.
     *
     * @param string $repository
     *   The destination of the push operation, which is either a URL or name of
     *   the remote. Defaults to "origin".
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @see AnsibleWorkingCopy::push()
     */
    public function pushTags($repository = 'origin', array $options = array())
    {
        $options['tags'] = true;
        return $this->push($repository, $options);
    }

    /**
     * Fetches all remotes.
     *
     * This is synonymous with `ansible fetch --all`.
     *
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @see AnsibleWorkingCopy::fetch()
     */
    public function fetchAll(array $options = array())
    {
        $options['all'] = true;
        return $this->fetch($options);
    }

    /**
     * Create a new branch and check it out.
     *
     * This is synonymous with `ansible checkout -b`.
     *
     * @param string $branch
     *   The new branch being created.
     *
     * @see AnsibleWorkingCopy::checkout()
     */
    public function checkoutNewBranch($branch, array $options = array())
    {
        $options['b'] = true;
        return $this->checkout($branch, $options);
    }

    /**
     * @} End of "defgroup command_helpers".
     */

    /**
     * @defgroup commands Ansible Commands
     *
     * All methods in this group correspond with Ansible commands, for example
     * "ansible add", "ansible commit", "ansible push", etc.
     *
     * @{
     */

    /**
     * Executes a `ansible add` command.
     *
     * Add file contents to the index.
     *
     * @code
     * $ansible->add('some/file.txt');
     * @endcode
     *
     * @param string $filepattern
     *   Files to add content from. Fileglobs (e.g.  *.c) can be given to add
     *   all matching files. Also a leading directory name (e.g.  dir to add
     *   dir/file1 and dir/file2) can be given to add all files in the
     *   directory, recursively.
     * @param array $options
     *   An optional array of command line options.
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function add($filepattern, array $options = array())
    {
        $args = array(
            'add',
            $filepattern,
            $options,
        );
        return $this->run($args);
    }

    /**
     * Executes a `ansible apply` command.
     *
     * Apply a patch to files and/or to the index
     *
     * @code
     * $ansible->apply('the/file/to/read/the/patch/from');
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return AnsibleWorkingCopy
     *
     * @throws AnsibleException
     */
    public function apply()
    {
        $args = func_get_args();
        array_unshift($args, 'apply');
        return $this->run($args);
    }

    /**
     * Executes a `ansible bisect` command.
     *
     * Find by binary search the change that introduced a bug.
     *
     * @code
     * $ansible->bisect('good', '2.6.13-rc2');
     * $ansible->bisect('view', array('stat' => true));
     * @endcode
     *
     * @param string $sub_command
     *   The subcommand passed to `ansible bisect`.
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function bisect($sub_command)
    {
        $args = func_get_args();
        $args[0] = 'bisect ' . ProcessUtils::escapeArgument($sub_command);
        return $this->run($args);
    }

    /**
     * Executes a `ansible branch` command.
     *
     * List, create, or delete branches.
     *
     * @code
     * $ansible->branch('my2.6.14', 'v2.6.14');
     * $ansible->branch('origin/html', 'origin/man', array('d' => true, 'r' => 'origin/todo'));
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function branch()
    {
        $args = func_get_args();
        array_unshift($args, 'branch');
        return $this->run($args);
    }

    /**
     * Executes a `ansible checkout` command.
     *
     * Checkout a branch or paths to the working tree.
     *
     * @code
     * $ansible->checkout('new-branch', array('b' => true));
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function checkout()
    {
        $args = func_get_args();
        array_unshift($args, 'checkout');
        return $this->run($args);
    }

    /**
     * Executes a `ansible clone` command.
     *
     * Clone a repository into a new directory. Use AnsibleWorkingCopy::clone()
     * instead for more readable code.
     *
     * @code
     * $ansible->clone('ansible://ansiblehub.com/cpliakas/ansible-wrapper.ansible');
     * @endcode
     *
     * @param string $repository
     *   The Ansible URL of the repository being cloned.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @param string $repository
     *   The URL of the repository being cloned.
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function cloneRepository($repository, $options = array())
    {
        $args = array(
            'clone',
            $repository,
            $this->directory,
            $options,
        );
        return $this->run($args, false);
    }

    /**
     * Executes a `ansible commit` command.
     *
     * Record changes to the repository. If only one argument is passed, it is
     * assumed to be the commit message. Therefore `$ansible->commit('Message');`
     * yields a `ansible commit -am "Message"` command.
     *
     * @code
     * $ansible->commit('My commit message');
     * $ansible->commit('Makefile', array('m' => 'My commit message'));
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function commit()
    {
        $args = func_get_args();
        if (isset($args[0]) && is_string($args[0]) && !isset($args[1])) {
            $args[0] = array(
                'm' => $args[0],
                'a' => true,
            );
        }
        array_unshift($args, 'commit');
        return $this->run($args);
    }

    /**
     * Executes a `ansible config` command.
     *
     * Get and set repository options.
     *
     * @code
     * $ansible->config('user.email', 'opensource@chrispliakas.com');
     * $ansible->config('user.name', 'Chris Pliakas');
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function config()
    {
        $args = func_get_args();
        array_unshift($args, 'config');
        return $this->run($args);
    }

    /**
     * Executes a `ansible diff` command.
     *
     * Show changes between commits, commit and working tree, etc.
     *
     * @code
     * $ansible->diff();
     * $ansible->diff('topic', 'master');
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function diff()
    {
        $args = func_get_args();
        array_unshift($args, 'diff');
        return $this->run($args);
    }

    /**
     * Executes a `ansible fetch` command.
     *
     * Download objects and refs from another repository.
     *
     * @code
     * $ansible->fetch('origin');
     * $ansible->fetch(array('all' => true));
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function fetch()
    {
        $args = func_get_args();
        array_unshift($args, 'fetch');
        return $this->run($args);
    }

    /**
     * Executes a `ansible grep` command.
     *
     * Print lines matching a pattern.
     *
     * @code
     * $ansible->grep('time_t', '--', '*.[ch]');
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function grep()
    {
        $args = func_get_args();
        array_unshift($args, 'grep');
        return $this->run($args);
    }

    /**
     * Executes a `ansible init` command.
     *
     * Create an empty ansible repository or reinitialize an existing one.
     *
     * @code
     * $ansible->init(array('bare' => true));
     * @endcode
     *
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function init(array $options = array())
    {
        $args = array(
            'init',
            $this->directory,
            $options,
        );
        return $this->run($args, false);
    }

    /**
     * Executes a `ansible log` command.
     *
     * Show commit logs.
     *
     * @code
     * $ansible->log(array('no-merges' => true));
     * $ansible->log('v2.6.12..', 'include/scsi', 'drivers/scsi');
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function log()
    {
        $args = func_get_args();
        array_unshift($args, 'log');
        return $this->run($args);
    }

    /**
     * Executes a `ansible merge` command.
     *
     * Join two or more development histories together.
     *
     * @code
     * $ansible->merge('fixes', 'enhancements');
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function merge()
    {
        $args = func_get_args();
        array_unshift($args, 'merge');
        return $this->run($args);
    }

    /**
     * Executes a `ansible mv` command.
     *
     * Move or rename a file, a directory, or a symlink.
     *
     * @code
     * $ansible->mv('orig.txt', 'dest.txt');
     * @endcode
     *
     * @param string $source
     *   The file / directory being moved.
     * @param string $destination
     *   The target file / directory that the source is being move to.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function mv($source, $destination, array $options = array())
    {
        $args = array(
            'mv',
            $source,
            $destination,
            $options,
        );
        return $this->run($args);
    }

    /**
     * Executes a `ansible pull` command.
     *
     * Fetch from and merge with another repository or a local branch.
     *
     * @code
     * $ansible->pull('upstream', 'master');
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function pull()
    {
        $args = func_get_args();
        array_unshift($args, 'pull');
        return $this->run($args);
    }

    /**
     * Executes a `ansible push` command.
     *
     * Update remote refs along with associated objects.
     *
     * @code
     * $ansible->push('upstream', 'master');
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function push()
    {
        $args = func_get_args();
        array_unshift($args, 'push');
        return $this->run($args);
    }

    /**
     * Executes a `ansible rebase` command.
     *
     * Forward-port local commits to the updated upstream head.
     *
     * @code
     * $ansible->rebase('subsystem@{1}', array('onto' => 'subsystem'));
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function rebase()
    {
        $args = func_get_args();
        array_unshift($args, 'rebase');
        return $this->run($args);
    }

    /**
     * Executes a `ansible remote` command.
     *
     * Manage the set of repositories ("remotes") whose branches you track.
     *
     * @code
     * $ansible->remote('add', 'upstream', 'ansible://ansiblehub.com/cpliakas/ansible-wrapper.ansible');
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function remote()
    {
        $args = func_get_args();
        array_unshift($args, 'remote');
        return $this->run($args);
    }

    /**
     * Executes a `ansible reset` command.
     *
     * Reset current HEAD to the specified state.
     *
     * @code
     * $ansible->reset(array('hard' => true));
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function reset()
    {
        $args = func_get_args();
        array_unshift($args, 'reset');
        return $this->run($args);
    }

    /**
     * Executes a `ansible rm` command.
     *
     * Remove files from the working tree and from the index.
     *
     * @code
     * $ansible->rm('oldfile.txt');
     * @endcode
     *
     * @param string $filepattern
     *   Files to remove from version control. Fileglobs (e.g.  *.c) can be
     *   given to add all matching files. Also a leading directory name (e.g.
     *   dir to add dir/file1 and dir/file2) can be given to add all files in
     *   the directory, recursively.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function rm($filepattern, array $options = array())
    {
        $args = array(
            'rm',
            $filepattern,
            $options,
        );
        return $this->run($args);
    }

    /**
     * Executes a `ansible show` command.
     *
     * Show various types of objects.
     *
     * @code
     * $ansible->show('v1.0.0');
     * @endcode
     *
     * @param string $object
     *   The names of objects to show. For a more complete list of ways to spell
     *   object names, see "SPECIFYING REVISIONS" section in ansiblerevisions(7).
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function show($object, array $options = array())
    {
        $args = array('show', $object, $options);
        return $this->run($args);
    }

    /**
     * Executes a `ansible status` command.
     *
     * Show the working tree status.
     *
     * @code
     * $ansible->status(array('s' => true));
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function status()
    {
        $args = func_get_args();
        array_unshift($args, 'status');
        return $this->run($args);
    }

    /**
     * Executes a `ansible tag` command.
     *
     * Create, list, delete or verify a tag object signed with GPG.

     * @code
     * $ansible->tag('v1.0.0');
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function tag()
    {
        $args = func_get_args();
        array_unshift($args, 'tag');
        return $this->run($args);
    }

    /**
     * Executes a `ansible clean` command.
     *
     * Remove untracked files from the working tree
     *
     * @code
     * $ansible->clean('-d', '-f');
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function clean()
    {
        $args = func_get_args();
        array_unshift($args, 'clean');
        return $this->run($args);
    }

     /**
     * Executes a `ansible archive` command.
     *
     * Create an archive of files from a named tree
     *
     * @code
     * $ansible->archive('HEAD', array('o' => '/path/to/archive'));
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \AnsibleWrapper\AnsibleWorkingCopy
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function archive()
    {
        $args = func_get_args();
        array_unshift($args, 'archive');
        return $this->run($args);
    }

    /**
     * @} End of "defgroup command".
     */

    /**
     * Hackish, allows us to use "clone" as a method name.
     *
     * $throws \BadMethodCallException
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function __call($method, $args)
    {
        if ('clone' == $method) {
            return call_user_func_array(array($this, 'cloneRepository'), $args);
        } else {
            $class = get_called_class();
            $message = "Call to undefined method $class::$method()";
            throw new \BadMethodCallException($message);
        }
    }

    /**
     * Gets the output captured by the last run Ansible commnd(s).
     *
     * @return string
     *
     * @see AnsibleWorkingCopy::getOutput()
     */
    public function __toString()
    {
        return $this->getOutput();
    }
}
