<?php

namespace Lns\SocialFeed\Source;

use Lns\SocialFeed\Model\Feed;

class TwitterSearchApi implements SourceInterface
{
    public function getFeed($query) {
        return new Feed();
    }
}