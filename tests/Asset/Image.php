<?php
namespace Strapieno\PlaceCover\ApiTest\Asset;

use Matryoshka\Model\Object\ActiveRecord\ActiveRecordInterface;
use Strapieno\PlaceCover\Model\Entity\CoverAwareInterface;
use Strapieno\PlaceCover\Model\Entity\CoverAwareTrait;

/**
 * Class Image
 */
class Image implements CoverAwareInterface , ActiveRecordInterface
{
    use CoverAwareTrait;

    public function save()
    {
        // TODO: Implement save() method.
    }

    public function delete()
    {
        // TODO: Implement delete() method.
    }

    public function setId($id)
    {
        // TODO: Implement setId() method.
    }

    public function getId()
    {
        // TODO: Implement getId() method.
    }
}