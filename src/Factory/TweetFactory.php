<?php

/*
 * This file is part of the Social Feed Util.
 *
 * (c) LaNetscouade <contact@lanetscouade.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Lns\SocialFeed\Factory;

use Lns\SocialFeed\Model\Author;
use Lns\SocialFeed\Model\Media;
use Lns\SocialFeed\Model\Reference;
use Lns\SocialFeed\Model\ReferenceType;
use Lns\SocialFeed\Model\Tweet;

/**
 * TweetFactory.
 */
class TweetFactory implements PostFactoryInterface {

  /**
   * create.
   *
   * @param array $data
   *
   * @return Tweet $post
   */
  public function create(array $data) {
    $tweet = new Tweet();

    $media = new Media();
    $media->setUrl($data['user']['profile_image_url']);

    $author = new Author();
    $author->setProfilePicture($media);
    $author->setIdentifier($data['user']['id']);
    $author->setName($data['user']['name']);
    $author->setLink('https://twitter.com/' . $data['user']['screen_name']);
    $author->setUsername($data['user']['screen_name']);

    $tweet
        ->setFollowersCount($data['user']['followers_count'])
        ->setIdentifier($data['id'])
        ->setCreatedAt(new \DateTime($data['created_at']))
        ->setAuthor($author);

    // Use the correct message source.
    $retweet = isset($data['retweeted_status']);

    $source_tweet = $retweet ? $data['retweeted_status'] : $data;
    $extended = isset($source_tweet['full_text']);

    if ($extended) {
      $this->truncateTweetMessage($source_tweet['full_text'], $source_tweet['display_text_range']);
      $tweet->setMessage($source_tweet['full_text']);
      $this->addTweetReferences($tweet, $source_tweet, $source_tweet['display_text_range'][1]);
    }
    else {
      $tweet->setMessage($source_tweet['text']);
      $this->addTweetReferences($tweet, $source_tweet);
    }

    $this->addTweetMedias($tweet, $source_tweet);

    return $tweet;
  }

  /**
   * truncateTweetMessage.
   *
   * @param $message
   * @param $display_text_range
   */
  protected function truncateTweetMessage(&$message, $display_text_range = NULL) {
    $width = $display_text_range[1] - $display_text_range[0];
    $message = mb_strimwidth($message, $display_text_range[0], $width);
  }

  /**
   * addTweetMedias.
   *
   * @param $tweet
   * @param $data
   */
  protected function addTweetMedias(&$tweet, $data) {
    if (!isset($data['entities']['media'])) {
      if (!isset($data['retweeted_status']['entities']['media'])) {
        return;
      }
    }

    $media = isset($data['entities']['media']) ? $data['entities']['media'] : $data['retweeted_status']['entities']['media'];

    foreach ($media as $mediaData) {
      $media = new Media();
      $media->setUrl($mediaData['media_url']);
      $media->setLink($mediaData['expanded_url']);
      $tweet->addMedia($media);
    }
  }

  /**
   * addTweetReferences.
   *
   * @param $tweet
   * @param $data
   * @param $tweet_display_limit
   */
  protected function addTweetReferences(&$tweet, $data, $tweet_display_limit = FALSE) {
    $typeMap = array(
      'urls' => ReferenceType::URL,
      'user_mentions' => ReferenceType::USER,
      'hashtags' => ReferenceType::HASHTAG,
      'video' => ReferenceType::VIDEO,
      'media' => ReferenceType::MEDIA,
      'photo' => ReferenceType::MEDIA,
      'animated_gif' => ReferenceType::MEDIA,
    );

    foreach ($data['entities'] as $entityType => $entities) {
      foreach ($entities as $entity) {
        if ($tweet_display_limit && $entity['indices'][0] > $tweet_display_limit) {
          break;
        }

        $reference = new Reference();
        $reference
            ->setIndices($entity['indices'])
            ->setType($typeMap[$entityType])
            ->setData($entity);

        $tweet->addReference($reference);
      }
    }

    if (isset($data['extended_entities'])) {
      foreach ($data['extended_entities'] as $entities) {
        foreach ($entities as $entity) {
          if ($tweet_display_limit && $entity['indices'][0] > $tweet_display_limit) {
            break;
          }

          $reference = new Reference();
          $reference
              ->setIndices($entity['indices'])
              ->setType($typeMap[$entity['type']])
              ->setData($entity);

          $tweet->addReference($reference);
        }
      }
    }
  }

}
