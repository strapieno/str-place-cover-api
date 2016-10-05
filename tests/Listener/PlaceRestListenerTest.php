<?php
namespace Strapieno\PlaceCover\ApiTest\Listener;

use ImgMan\Apigility\Entity\ImageEntity;
use Strapieno\PlaceCover\Api\Listener\PlaceRestListener;
use Zend\Mvc\Controller\PluginManager;
use Zend\Mvc\Router\Http\TreeRouteStack;
use Zend\ServiceManager\ServiceManager;
use Zend\Uri\Http;
use ZF\Rest\ResourceEvent;

/**
 * Class PlaceRestListenerTest
 */
class PlaceRestListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $routeConfig = [
        'routes' => [
            'api-rest' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/api-rest',
                ],
                'child_routes' => [
                    'place' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/place[/:place_id]',
                        ],
                        'child_routes' => [
                            'cover' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/cover'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];

    public function testAttach()
    {
        $eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');
        $listener = new PlaceRestListener();
        $this->assertNull($listener->attach($eventManager));
    }

    public function testOnPostUpdate()
    {
        $listener = new PlaceRestListener();

        $resource = new ResourceEvent();
        $resource->setParam('id', 'test');
        $imageService = new  ImageEntity();
        $resource->setParam('image', $imageService);
        $imageService->setId('test');

        /** @var $route TreeRouteStack */
        $route = TreeRouteStack::factory($this->routeConfig);
        $route->setRequestUri(new Http('www.test.com'));

        $sm = new ServiceManager();
        $sm->setService('Router', $route);

        $abstractLocator = new PluginManager();
        $abstractLocator->setServiceLocator($sm);


        $image = $this->getMockBuilder('Strapieno\PlaceCover\ApiTest\Asset\Image')
            ->getMock();

        $resultSet = $this->getMockBuilder('Matryoshka\Model\ResultSet\HydratingResultSet')
            ->setMethods(['current'])
            ->getMock();

        $resultSet->method('current')
            ->willReturn($image);

        $model = $this->getMockBuilder('Strapieno\Place\Model\PlaceModel')
            ->disableOriginalConstructor()
            ->setMethods(['find'])
            ->getMock();

        $model->method('find')
            ->willReturn($resultSet);

        $listener->setPlaceModelService($model);
        $listener->setServiceLocator($abstractLocator);
        $this->assertSame($listener->onPostUpdate($resource), $imageService);

        $imageService->setSrc('test');
        $this->assertSame($listener->onPostUpdate($resource), $imageService);
    }

    public function testOnDeleteUpdate()
    {
        $listener = new PlaceRestListener();

        $resource = new ResourceEvent();
        $resource->setParam('id', 'test');
        $imageService = new  ImageEntity();


        $sm = new ServiceManager();
        $abstractLocator = new PluginManager();
        $abstractLocator->setServiceLocator($sm);


        $image = $this->getMockBuilder('Strapieno\PlaceCover\ApiTest\Asset\Image')
            ->getMock();

        $resultSet = $this->getMockBuilder('Matryoshka\Model\ResultSet\HydratingResultSet')
            ->setMethods(['current'])
            ->getMock();

        $resultSet->method('current')
            ->willReturn($image);

        $model = $this->getMockBuilder('Strapieno\Place\Model\PlaceModel')
            ->disableOriginalConstructor()
            ->setMethods(['find'])
            ->getMock();

        $model->method('find')
            ->willReturn($resultSet);

        $listener->setPlaceModelService($model);
        $listener->setServiceLocator($abstractLocator);
        $this->assertTrue($listener->onPostDelete($resource));
    }
}