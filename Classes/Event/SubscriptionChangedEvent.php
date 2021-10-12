<?php

namespace Zwo3\NewsletterSubscribe\Event;



use Zwo3\NewsletterSubscribe\Domain\Model\Subscription;

/**
 *
 */
class SubscriptionChangedEvent
{
    /**
     * @var string
     */
    public $controllerClassName;
    
    /**
     * @var string
     */
    public $actionMethodName;
    
    /**
     * @var Subscription
     */
    public $subscription;
    
    /**
     * @var string
     */
    public $type;
    
    /**
     * @param string $controllerClassName
     * @param string $actionMethodName
     * @param Subscription $subscription
     * @param string $type
     */
    public function __construct(string $controllerClassName, string $actionMethodName, Subscription $subscription, string $type)
    {
        $this->controllerClassName = $controllerClassName;
        $this->actionMethodName = $actionMethodName;
        $this->subscription = $subscription;
        $this->type = $type;
    }
    
}