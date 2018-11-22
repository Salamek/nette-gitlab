<?php

namespace Salamek\Gitlab\DI;

use Nette;
use Nette\DI\Compiler;
use Nette\DI\Configurator;

/**
 * Class GitlabExtension
 * @package Salamek\Gitlab\DI
 */
class GitlabExtension extends Nette\DI\CompilerExtension
{

    public function loadConfiguration()
    {
        $config = $this->getConfig();
        $builder = $this->getContainerBuilder();


        $builder->addDefinition($this->prefix('gitlab'))
            ->setClass('Salamek\Gitlab\Gitlab', [$config['gitlabUrl'], $config['gitlabToken'], $config['projectName']])
            ->addSetup('setGitlabUrl', [$config['gitlabUrl']])
            ->addSetup('setGitlabToken', [$config['gitlabToken']])
            ->addSetup('setProjectName', [$config['projectName']]);
    }


    /**
     * @param Configurator $config
     * @param string $extensionName
     */
    public static function register(Configurator $config, $extensionName = 'templatedEmailExtension')
    {
        $config->onCompile[] = function (Configurator $config, Compiler $compiler) use ($extensionName) {
            $compiler->addExtension($extensionName, new GitlabExtension());
        };
    }


    /**
     * {@inheritdoc}
     */
    public function getConfig(array $defaults = [], $expand = true)
    {
        $defaults = [
            'gitlabUrl' => 'https://gitlab.com/api/v3',
            'gitlabToken' => null,
            'projectName' => null,
        ];

        return parent::getConfig($defaults, $expand);
    }
}
