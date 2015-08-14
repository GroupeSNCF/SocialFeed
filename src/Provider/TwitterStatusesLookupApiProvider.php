<?php

/*
 * This file is part of the Social Feed Util.
 *
 * (c) LaNetscouade <contact@lanetscouade.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Lns\SocialFeed\Provider;

use Lns\SocialFeed\Client\ClientInterface;
use Lns\SocialFeed\Factory\PostFactoryInterface;
use Lns\SocialFeed\Model\Feed;
use Lns\SocialFeed\Model\ResultSet;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * TwitterStatusesLookupApiProvider.
 */
class TwitterStatusesLookupApiProvider extends AbstractProvider
{
    private $twitterApiClient;

    /**
     * __construct.
     *
     * @param ClientInterface      $twitterApiClient
     * @param PostFactoryInterface $postFactory
     */
    public function __construct(ClientInterface $twitterApiClient, PostFactoryInterface $postFactory)
    {
        $this->twitterApiClient = $twitterApiClient;
        $this->postFactory = $postFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function get(array $parameters = array())
    {
        $parameters = $this->resolveParameters($parameters);

        $response = $this->twitterApiClient
            ->get('/1.1/statuses/lookup.json?id='.implode($parameters['ids'], ','));

        $feed = new Feed();

        foreach ($response as $status) {
            $feed->addPost($this->postFactory->create($status));
        }

        return new ResultSet($feed, $parameters, array());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'twitter_status_lookup_api';
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptionResolver(OptionsResolver &$resolver)
    {
        $resolver->setRequired('ids');
    }
}
