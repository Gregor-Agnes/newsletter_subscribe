<?php

namespace Zwo3\NewsletterSubscribe\Event;



use Zwo3\NewsletterSubscribe\Domain\Model\Subscription;

/**
 *
 */
class SubscriptionConfirmedEvent
{
    /**
     * @var string
     */
    protected $controllerClassName;
    
    /**
     * @var string
     */
    protected $actionMethodName;
    
    /**
     * @var Subscription
     */
    protected $subscription;
    
    /**
     * @param string $controllerClassName
     * @param string $actionMethodName
     * @param Subscription $subscription
     */
    public function __construct(string $controllerClassName, string $actionMethodName, $subscription)
    {
        $this->controllerClassName = $controllerClassName;
        $this->actionMethodName = $actionMethodName;
        $this->subscription = $subscription;
    }
    
}