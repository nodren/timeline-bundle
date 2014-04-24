<?php

namespace Spy\TimelineBundle\Tests\Units\ResolveComponent;

require_once __DIR__ . "/../../../vendor/autoload.php";

use atoum\AtoumBundle\Test\Units\Test;
use Spy\TimelineBundle\ResolveComponent\DoctrineComponentDataResolver as TestedModel;
use Spy\Timeline\ResolveComponent\TestHelper\User;

/**
 * Test file for Spy\TimelineBundle\ResolveComponent\DoctrineComponentDataResolver
 *
 * @author Michiel Boeckaert <boeckaert@gmail.com>
 */
class DoctrineComponentDataResolver extends Test
{
    public function testObjectManagedByDoctrine()
    {
        $object = new User(5);

        $this->if($this->mockClass('\Doctrine\Common\Persistence\ManagerRegistry', '\Mock'))
            ->and($this->mockGenerator->orphanize('__construct'))
            ->and($this->mockGenerator->shuntParentClassCalls())
            ->and($this->mockClass('\Doctrine\Common\Persistence\ObjectManager', '\Mock'))
            ->and($this->mockClass('Doctrine\Common\Persistence\Mapping\ClassMetadata', '\Mock'))
            ->and($classMetadata = new \Mock\ClassMetadata())
            ->and($managerRegistry = new \Mock\ManagerRegistry())
            ->and($objectManager = new \Mock\ObjectManager())
            ->and($this->calling($managerRegistry)->getManagerForClass = function() use ($objectManager) {
                return $objectManager;
            })
            ->and($this->calling($objectManager)->getClassMetadata = function() use ($classMetadata) {
                return $classMetadata;
            })
            ->and($this->calling($classMetadata)->getIdentifier = function() { return array('id');

            })
            ->and($this->calling($classMetadata)->getName= function () {
                return 'Spy\Timeline\ResolveComponent\TestHelper\User';

            })
            ->and($resolver = new TestedModel())
            ->and($resolver->addRegistry($managerRegistry))
            ->when($result = $resolver->resolveComponentData($object))
            ->then(
                $this->mock($managerRegistry)->call('getManagerForClass')->withArguments('Spy\Timeline\ResolveComponent\TestHelper\User')->exactly(1)
                ->and($this->mock($objectManager)->call('getClassMetadata')->withArguments('Spy\Timeline\ResolveComponent\TestHelper\User')->exactly(1))
                ->and($this->mock($classMetadata)->call('getIdentifier')->exactly(1))
                ->and($this->string($result->getIdentifier())->isEqualTo(5))
                ->and($this->string($result->getModel())->isEqualTo('Spy\Timeline\ResolveComponent\TestHelper\User'))
            )
            ;
    }
}
