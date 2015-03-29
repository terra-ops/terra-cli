<?php

/**
 * A PHP wrapper around the Ansible command line utility.
 */

namespace AnsibleWrapper;

/**
 * Class that parses and returnes an array of branches.
 */
class AnsibleBranches implements \IteratorAggregate
{
    /**
     * The working copy that branches are being collected from.
     *
     * @var \AnsibleWrapper\AnsibleWorkingCopy
     */
    protected $ansible;

    /**
     * Constructs a AnsibleBranches object.
     *
     * @param \AnsibleWrapper\AnsibleWorkingCopy $ansible
     *   The working copy that branches are being collected from.
     *
     * @throws \AnsibleWrapper\AnsibleException
     */
    public function __construct(AnsibleWorkingCopy $ansible)
    {
        $this->ansible = clone $ansible;
        $output = (string) $ansible->branch(array('a' => true));
    }

    /**
     * Fetches the branches via the `ansible branch` command.
     *
     * @param boolean $onlyRemote
     *   Whether to fetch only remote branches, defaults to false which returns
     *   all branches.
     *
     * @return array
     */
    public function fetchBranches($onlyRemote = false)
    {
        $this->ansible->clearOutput();
        $options = ($onlyRemote) ? array('r' => true) : array('a' => true);
        $output = (string) $this->ansible->branch($options);
        $branches = preg_split("/\r\n|\n|\r/", rtrim($output));
        return array_map(array($this, 'trimBranch'), $branches);
    }

    /**
     * Strips unwanted characters from the branch.
     *
     * @param string $branch
     *   The raw branch returned in the output of the Ansible command.
     *
     * @return string
     *   The processed branch name.
     */
    public function trimBranch($branch)
    {
        return ltrim($branch, ' *');
    }

    /**
     * Implements \IteratorAggregate::getIterator().
     */
    public function getIterator()
    {
        $branches = $this->all();
        return new \ArrayIterator($branches);
    }

    /**
     * Returns all branches.
     *
     * @return array
     */
    public function all()
    {
        return $this->fetchBranches();
    }

    /**
     * Returns only remote branches.
     *
     * @return array
     */
    public function remote()
    {
        return $this->fetchBranches(true);
    }

    /**
     * Returns currently active branch (HEAD) of the working copy.
     *
     * @return array
     */
    public function head()
    {
        return (string) $this->ansible->run(array('rev-parse --abbrev-ref HEAD'));
    }
}
