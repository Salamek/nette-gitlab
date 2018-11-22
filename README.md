# Nette Gitlab

This is a simple integration of gitlab into [Nette Framework](http://nette.org/)

## Instalation

The best way to install salamek/nette-gitlab is using  [Composer](http://getcomposer.org/):


```sh
$ composer require salamek/nette-gitlab:@dev
```

Then you have to register extension in `config.neon`.

```yaml
extensions:
	gitlab: Salamek\Gitlab\DI\GitlabExtension

gitlab:
    gitlabUrl: https://gitlab.com/api/v3
    gitlabToken: GITLAB_TOKEN
    projectPath: PATH_TO_YOUR_PROJECT
```
