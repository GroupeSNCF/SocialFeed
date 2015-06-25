<?php

namespace spec\Lns\SocialFeed\Model;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use Lns\SocialFeed\Model\PostInterface;

class FeedSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Lns\SocialFeed\Model\Feed');
        $this->shouldImplement('\IteratorAggregate');
    }

    function it_should_be_possible_to_add_a_post_to_feed(PostInterface $post1, PostInterface $post2)
    {
        $post1->getIdentifier()->willReturn('id1');
        $post2->getIdentifier()->willReturn('id2');

        $this->addPost($post1)->shouldReturn($this);
        $this->addPost($post2)->shouldReturn($this);

        $this->getPost('id1')->shouldReturn($post1);
        $this->getPost('id2')->shouldReturn($post2);

        $iterator = $this->getIterator();
    }
}
