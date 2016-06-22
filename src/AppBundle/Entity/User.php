<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Traits\ImageTrait;
use AppBundle\Entity\Traits\RemovedAtTrait;
use Doctrine\ORM\Mapping as ORM;
use Sonata\UserBundle\Entity\BaseUser as BaseUser;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Class User
 *
 * @category Entity
 * @package  AppBundle\Entity
 * @author   David Romaní <david@flux.cat>
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @ORM\Table(name="admin_user")
 * @Gedmo\SoftDeleteable(fieldName="removedAt", timeAware=false)
 * @Vich\Uploadable
 */
class User extends BaseUser
{
    use ImageTrait;
    use RemovedAtTrait;

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="user", fileNameProperty="imageName")
     * @Assert\File(
     *     maxSize = "10M",
     *     mimeTypes = {"image/jpg", "image/jpeg", "image/png", "image/gif"}
     * )
     * @Assert\Image(minWidth = 320)
     */
    private $imageFile;

    /**
     * @var Customer
     *
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="contacts")
     */
    private $customer;

    /**
     *
     *
     * Methods
     *
     *
     */

    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Customer|null $customer
     *
     * @return User
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return string
     */
    public function fullContactInfoString()
    {
        return $this->getLastname() . ', ' . $this->getFirstname()  . ' · ' . $this->getEmail() . ($this->getPhone() ? ' · ' . $this->getPhone() : '');
    }

    /**
     * @return string
     */
    public function contactInfoString()
    {
        return $this->getLastname() . ', ' . $this->getFirstname();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getUsername() ? $this->getFullname() : '---';
    }
}
