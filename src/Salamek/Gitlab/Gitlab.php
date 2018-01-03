<?php

namespace Salamek\Gitlab;

use Gitlab\Api\AbstractApi;
use Gitlab\Client;
use Gitlab\Model\Label;
use Nette;
use Gitlab\Model\Project;
use Gitlab\Model\Issue;
use Nette\Utils\Strings;
use Tracy\Debugger;

/**
 * Class TemplatedEmail
 * @package Salamek\TemplatedEmail
 */
class Gitlab
{
    use Nette\SmartObject;

    /** @var string */
    private $gitlabUrl;

    /** @var string */
    private $gitlabToken;

    /** @var string */
    private $projectName;

    /** @var Label[]|null */
    private $labels = null;

    /** @var null|string */
    private $projectId = null;

    /** @var null|Client */
    private $client = null;

    /**
     * Gitlab constructor.
     * @param string $gitlabUrl
     * @param string $gitlabToken
     * @param string $projectName
     */
    public function __construct($gitlabUrl, $gitlabToken, $projectName)
    {
        $this->setGitlabUrl($gitlabUrl);
        $this->gitlabToken = $gitlabToken;
        $this->projectName = $projectName;
    }

    /**
     * @param mixed $gitlabUrl
     */
    public function setGitlabUrl($gitlabUrl)
    {
        if (!Strings::endsWith($gitlabUrl, '/'))
        {
            $gitlabUrl = $gitlabUrl.'/';
        }
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
     * @param mixed $projectName
     */
    public function setProjectName($projectName)
    {
        $this->projectName = $projectName;
    }

    /**
     * @return Client|null
     */
    public function getClient()
    {
        if (is_null($this->client))
        {
            $this->client = new Client($this->gitlabUrl);
            $this->client->authenticate($this->gitlabToken, Client::AUTH_URL_TOKEN);
        }
        
        return $this->client;
    }

    /**
     * @return null|integer
     * @throws \Exception
     */
    public function getProjectId()
    {
        if (is_null($this->projectId))
        {
            foreach ($this->getClient()->api('projects')->accessible() AS $project)
            {
                if ($project['path_with_namespace'] == $this->projectName)
                {
                    $this->projectId = $project['id'];
                    break;
                }
            }

            if (is_null($this->projectId))
            {
                throw new \Exception(sprintf('Project %s was not found', $this->projectName));
            }

        }

        return $this->projectId;
    }

    public function getProject()
    {
        return new Project($this->getProjectId(), $this->getClient());
    }

    /**
     * @param $title
     * @param array $parameters
     */
    public function createIssue($title, array $parameters = [])
    {
        $project = $this->getProject();
        $project->createIssue($title, $parameters);
    }
    
    /**
     * @param int $page
     * @param int $perPage
     * @param array $params
     * @return Issue[]
     * @throws \Exception
     */
    public function getIssues($page = 1, $perPage = AbstractApi::PER_PAGE, array $params = [])
    {
        $issues =  $this->getClient()->api('issues')->all($this->getProjectId(), $page, $perPage, $params);
        $return = [];

        foreach ($issues AS $issue)
        {
            foreach($issue['labels'] AS &$label)
            {
                $label = $this->getLabels()[$label];
            }

            $return[] = Issue::fromArray($this->getClient(), $this->getProject(), $issue);
        }

        return $return;
    }

    /**
     * @return Label[]
     * @throws \Exception
     */
    public function getLabels()
    {
        if (is_null($this->labels))
        {
            $issues = $this->getClient()->api('projects')->labels($this->getProjectId());

            $return = [];
            foreach ($issues AS $issue)
            {
                $return[$issue['name']] = Label::fromArray($this->getClient(), $this->getProject(), $issue);
            }

            $this->labels = $return;
        }

        return $this->labels;
    }
    
    /**
     * @param $id
     * @return Issue
     */
    public function getIssue($id)
    {
        $data = $this->getClient()->api('issues')->show($this->getProjectId(), $id);

        foreach($data['labels'] AS &$label)
        {
            $label = $this->getLabels()[$label];
        }

        return Issue::fromArray($this->getClient(), $this->getProject(), $data);
    }

    /**
     * @return array
     */
    public function getIssuesStats()
    {
        $return = ['opened' => 0, 'closed' => 0, 'last' => []];
        $data = $this->getIssues();
        foreach ($data AS $row) {
            if ($row['state'] == 'opened') {
                $return['opened']++;
            } else {
                $return['closed']++;
            }
        }

        $return['last'] = array_shift($data);
        return $return;
    }
}
