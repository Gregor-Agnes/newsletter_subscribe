<?php
return [
    'frontend' => [
        'zwo3/newsletter-subscribe/spambot' => [
            'target' => \Zwo3\NewsletterSubscribe\Middleware\Spambot::class,
            'after' => [
                'typo3/cms-frontend/content-length-headers',
            ],
        ],
    ],
];