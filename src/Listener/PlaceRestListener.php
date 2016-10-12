<?php
namespace Strapieno\PlaceCover\Api\Listener;

use ImgMan\Image\SrcAwareInterface;
use Matryoshka\Model\Object\ActiveRecord\ActiveRecordInterface;
use Matryoshka\Model\Object\IdentityAwareInterface;
use Matryoshka\Model\Wrapper\Mongo\Criteria\ActiveRecord\ActiveRecordCriteria;
use Strapieno\Place\Model\PlaceModelAwareInterface;
use Strapieno\Place\Model\PlaceModelAwareTrait;
use Strapieno\PlaceCover\Model\Entity\CoverAwareInterface;
use Strapieno\User\Model\Entity\UserInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\Router\Http\RouteInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\Rest\ResourceEvent;

/**
 * Class PlaceRestListener
 */
class PlaceRestListener implements ListenerAggregateInterface,
    ServiceLocatorAwareInterface,
    PlaceModelAwareInterface
{
    use ListenerAggregateTrait;
    use ServiceLocatorAwareTrait;
    use PlaceModelAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('update', [$this, 'onPostUpdate']);
        $this->listeners[] = $events->attach('delete', [$this, 'onPostDelete']);
    }

    /**
     * @param ResourceEvent $e
     * @return mixed
     */
    public function onPostUpdate(ResourceEvent $e)
    {
        $serviceLocator = $this->getServiceLocator();
        if ($serviceLocator instanceof AbstractPluginManager) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }

        $id  = $e->getParam('id');
        $place = $this->getPlaceFromId($id);

        $image = $e->getParam('image');

        if ($place instanceof CoverAwareInterface && $place instanceof ActiveRecordInterface) {

            $place->setCover($this->getUrlFromImage($image, $serviceLocator));
            $place->save();
            $e->setParam('place', $place);
        }

        return $image;
    }

    /**
     * @param ResourceEvent $e
     * @return bool
     */
    public function onPostDelete(ResourceEvent $e)
    {

        $id  = $e->getParam('id');
        $place = $this->getPlaceFromId($id);

        if ($place instanceof CoverAwareInterface && $place instanceof ActiveRecordInterface) {

            $place->setCover(null);
            $place->save();
            $e->setParam('place', $place);
        }

        return true;
    }

    /**
     * @param $id
     * @return UserInterface|null
     */
    protected function getPlaceFromId($id)
    {
        return $this->getPlaceModelService()->find((new ActiveRecordCriteria())->setId($id))->current();

    }

    /**
     * @param IdentityAwareInterface $image
     * @param $serviceLocator
     * @return string
     */
    protected function getUrlFromImage(IdentityAwareInterface $image, ServiceLocatorInterface $serviceLocator)
    {
        $now = new \DateTime();
        if ($image instanceof SrcAwareInterface && $image->getSrc()) {

            return $image->getSrc(). '?lastUpdate=' . $now->getTimestamp();
        }

        /** @var $router RouteInterface */
        $router = $serviceLocator->get('Router');
        $url = $router->assemble(
            ['place_id' => $image->getId()],
            ['name' => 'api-rest/place/cover', 'force_canonical' => true]
        );

        return $url . '?lastUpdate=' . $now->getTimestamp();
    }
}