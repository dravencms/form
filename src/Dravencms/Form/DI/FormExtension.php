<?php declare(strict_types = 1);

namespace Dravencms\Form\DI;

use Nette;
use Dravencms\Form\Form;
use Nette\DI\CompilerExtension;
use Salamek\Cms\DI\CmsExtension;
/**
 * Class FormExtension
 * @package Dravencms\Form\DI
 */
class FormExtension extends CompilerExtension
{
    public function loadConfiguration(): void
    {
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('form'))
            ->setFactory(Form::class);

        if (class_exists(CmsExtension::class)) {
            $this->loadCmsComponents();
            $this->loadCmsModels();
        }

        $this->loadComponents();
        $this->loadModels();
        $this->loadConsole();
    }
    
    protected function loadCmsModels(): void
    {
        $builder = $this->getContainerBuilder();
        foreach ($this->loadFromFile(__DIR__ . '/cmsModels.neon') as $i => $command) {
            $cli = $builder->addDefinition($this->prefix('cmsModels.' . $i));
            if (is_string($command)) {
                $cli->setFactory($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }

    protected function loadCmsComponents(): void
    {
        $builder = $this->getContainerBuilder();
        foreach ($this->loadFromFile(__DIR__ . '/cmsComponents.neon') as $i => $command) {
            $cli = $builder->addFactoryDefinition($this->prefix('cmsComponent.' . $i))
                ->addTag(CmsExtension::TAG_COMPONENT);
            if (is_string($command)) {
                $cli->setImplement($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }

    protected function loadComponents(): void
    {
        $builder = $this->getContainerBuilder();
        foreach ($this->loadFromFile(__DIR__ . '/components.neon') as $i => $command) {
            $cli = $builder->addFactoryDefinition($this->prefix('components.' . $i));
            if (is_string($command)) {
                $cli->setImplement($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }

    protected function loadModels(): void
    {
        $builder = $this->getContainerBuilder();
        foreach ($this->loadFromFile(__DIR__ . '/models.neon') as $i => $command) {
            $cli = $builder->addDefinition($this->prefix('models.' . $i));
            if (is_string($command)) {
                $cli->setFactory($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }

    protected function loadConsole(): void
    {
        $builder = $this->getContainerBuilder();

        foreach ($this->loadFromFile(__DIR__ . '/console.neon') as $i => $command) {
            $cli = $builder->addDefinition($this->prefix('cli.' . $i))
                ->setAutowired(false);

            if (is_string($command)) {
                $cli->setFactory($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }
}
