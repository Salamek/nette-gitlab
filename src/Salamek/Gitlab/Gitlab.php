<?php

namespace Salamek\Gitlab;


/**
 * Class TemplatedEmail
 * @package Salamek\TemplatedEmail
 */
class Gitlab extends Nette\Object
{
    private $gitlabUrl;

    private $gitlabToken;

    private $projectPath;

    public function __construct($gitlabUrl, $gitlabToken, $projectPath)
    {
        $this->gitlabUrl = $gitlabUrl;
        $this->gitlabToken = $gitlabToken;
        $this->projectPath = $projectPath;
    }

    /**
     * @param mixed $gitlabUrl
     */
    public function setGitlabUrl($gitlabUrl)
    {
        $this->gitlabUrl = $gitlabUrl;
    }

    /**
     * @param mixed $gitlabToken
     */
    public function setGitlabToken($gitlabToken)
    {
        $this->gitlabToken = $gitlabToken;
    }

    /**
     * @param mixed $projectPath
     */
    public function setProjectPath($projectPath)
    {
        $this->projectPath = $projectPath;
    }

    

    private function connect()
    {
        $client = new \Gitlab\Client($this->gitlabUrl);
        $client->authenticate($this->gitlabToken, \Gitlab\Client::AUTH_URL_TOKEN);
    }

}
