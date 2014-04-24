<?php

namespace Spy\TimelineBundle\Driver\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use Spy\Timeline\Model\ActionInterface;
use Spy\Timeline\ResultBuilder\ResultBuilderInterface;
use Spy\Timeline\Driver\AbstractActionManager as BaseActionManager;
use Spy\Timeline\Model\ComponentInterface;
use Spy\Timeline\ResolveComponent\ValueObject\ResolvedComponentData;

/**
 * The abstract action manager for doctrine.
 *
 * @author Stephane PY <py.stephane1@gmail.com>
 */
abstract class AbstractActionManager extends BaseActionManager
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ResultBuilderInterface
     */
    protected $resultBuilder;

    /**
     * @var string
     */
    protected $actionClass;

    /**
     * @var string
     */
    protected $componentClass;

    /**
     * @var string
     */
    protected $actionComponentClass;

    /**
     * @param ObjectManager          $objectManager        objectManager
     * @param ResultBuilderInterface $resultBuilder        resultBuilder
     * @param string                 $actionClass          actionClass
     * @param string                 $componentClass       componentClass
     * @param string                 $actionComponentClass actionComponentClass
     */
    public function __construct(ObjectManager $objectManager, ResultBuilderInterface $resultBuilder, $actionClass, $componentClass, $actionComponentClass)
    {
        $this->objectManager        = $objectManager;
        $this->resultBuilder        = $resultBuilder;
        $this->actionClass          = $actionClass;
        $this->componentClass       = $componentClass;
        $this->actionComponentClass = $actionComponentClass;
        $this->registries           = array();
    }

    /**
     * {@inheritdoc}
     */
    public function updateAction(ActionInterface $action)
    {
        $this->objectManager->persist($action);
        $this->objectManager->flush();

        $this->deployActionDependOnDelivery($action);
    }

    /**
     * {@inheritdoc}
     */
    public function createComponent($model, $identifier = null, $flush = true)
    {
        $resolvedComponentData = $this->resolveModelAndIdentifier($model, $identifier);

        return $this->createComponentFromResolvedComponentData($resolvedComponentData, $flush);
    }

    /**
     * {@inheritdoc}
     */
    public function flushComponents()
    {
        $this->objectManager->flush();
    }

    /**
     * @param mixed $model
     * @param mixed $identifier
     *
     * @return ResolvedComponentData
     */
    protected function resolveModelAndIdentifier($model, $identifier)
    {
        return $this->getComponentDataResolver()->resolveComponentData($model, $identifier);
    }

    /**
     * Creates a component from a resolved model and identifier and optionally stores it to the storage engine.
     *
     * @param ResolvedComponentData $resolved The resolved component data
     * @param boolean               $flush    Whether to flush or not, defaults to true
     *
     * @return ComponentInterface The newly created and populated component
     */
    protected function createComponentFromResolvedComponentData(ResolvedComponentData $resolved, $flush = true)
    {
        $component = $this->getComponentFromResolvedComponentData($resolved);

        $this->objectManager->persist($component);

        if ($flush) {
            $this->flushComponents();
        }

        return $component;
    }

    /**
     * Creates a new component object from the resolved data.
     *
     * @param ResolvedComponentData $resolved The resolved component data
     *
     * @return ComponentInterface The newly created and populated component
     */
    private function getComponentFromResolvedComponentData(ResolvedComponentData $resolved)
    {
        /** @var $component ComponentInterface */
        $component = new $this->componentClass();
        $component->setModel($resolved->getModel());
        $component->setData($resolved->getData());
        $component->setIdentifier($resolved->getIdentifier());

        return $component;
    }
}
