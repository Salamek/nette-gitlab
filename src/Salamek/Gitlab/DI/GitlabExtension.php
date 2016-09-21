<?php

namespace Salamek\Gitlab\DI;

use Nette;
use Nette\DI\Compiler;
use Nette\DI\Configurator;

/**
 * Class TemplatedEmailExtension
 * @package Salamek\TemplatedEmail\DI
 */
class GitlabExtension extends Nette\DI\CompilerExtension
{

    public function loadConfiguration()
    {
        $config = $this->getConfig();
        $builder = $this->getContainerBuilder();


        $builder->addDefinition($this->prefix('gitlab'))
            ->setClass('Salamek\Gitlab\Gitlab', [$config['gitlabUrl'], $config['gitlabToken'], $config['projectPath']])
            ->addSetup('setGitlabUrl', [$config['gitlabUrl']])
            ->addSetup('setGitlabToken', [$config['gitlabToken']])
            ->addSetup('setProjectPath', [$config['projectPath']]);
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
            'gitlabUrl' => 'https://gitlab.com',
            'gitlabToken' => null,
            'projectPath' => null,
        ];

        return parent::getConfig($defaults, $expand);
    }
}
