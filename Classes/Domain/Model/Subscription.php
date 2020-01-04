<?php

namespace Zwo3\Subscribe\Domain\Model;

use TYPO3\CMS\Extbase\Annotation\Validate;

/**
 * Class Subscription
 *
 * @package Zwo3\Subscribe\Domain\Model
 */
class Subscription extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @var string
     */
    protected $title;


    /**
     * @var string
     */
    protected $company;

    /**
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("EmailAddress")
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $email;

    /**
     * @var bool
     * @TYPO3\CMS\Extbase\Annotation\Validate("Boolean", options={"is": true})
     */
    protected $dataProtectionAccepted;


    /**
     * @var bool
     * @TYPO3\CMS\Extbase\Annotation\Validate("Boolean")
     */
    protected $moduleSysDmailHtml;

    /**
     * @var string
     */
    protected $subscriptionHash;

    /**
     * @var bool
     */
    protected $moduleSysDmailNewsletter;

    /**
     * @var bool
     */
    protected $hidden;   /**
     * @return mixed
     */

    /**
     * @var int
     */
    protected $crdate;

    /**
     * @return int
     */
    public function getCrdate(): ?int
    {
        return $this->crdate;
    }

    /**
     * @param int $crdate
     */
    public function setCrdate(int $crdate): void
    {
        $this->crdate = $crdate;
    }

    /**
     * @return bool
     */
    public function isModuleSysDmailHtml(): ?bool
    {
        return $this->moduleSysDmailHtml;
    }

    /**
     * @param bool $moduleSysDmailHtml
     */
    public function setModuleSysDmailHtml(bool $moduleSysDmailHtml): void
    {
        $this->moduleSysDmailHtml = $moduleSysDmailHtml;
    }


    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param mixed $company
     */
    public function setCompany($company): void
    {
        $this->company = $company;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * @param bool $hidden
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return bool
     */
    public function isDataProtectionAccepted()
    {
        return $this->dataProtectionAccepted;
    }

    /**
     * @param bool $dataProtectionAccepted
     */
    public function setDataProtectionAccepted($dataProtectionAccepted)
    {
        $this->dataProtectionAccepted = $dataProtectionAccepted;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getSubscriptionHash(): ?string
    {
        return $this->subscriptionHash;
    }

    /**
     * @param string $subscriptionHash
     */
    public function setSubscriptionHash(string $subscriptionHash): void
    {
        $this->subscriptionHash = $subscriptionHash;
    }

    /**
     * @return bool
     */
    public function isModuleSysDmailNewsletter(): ?bool
    {
        return $this->moduleSysDmailNewsletter;
    }

    /**
     * @param bool $moduleSysDmailNewsletter
     */
    public function setModuleSysDmailNewsletter(bool $moduleSysDmailNewsletter): void
    {
        $this->moduleSysDmailNewsletter = $moduleSysDmailNewsletter;
    }


}