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
use Lns\SocialFeed\Model\FacebookPost;
use Lns\SocialFeed\Model\Media;
use Lns\SocialFeed\Model\Reference;
use Lns\SocialFeed\Model\ReferenceType;

/**
 * FacebookPostFactory.
 */
class FacebookPostFactory implements PostFactoryInterface
{
    /**
     * create.
     *
     * @param array $data
     *
     * @return FacebookPost $post
     */
    public function create(array $data)
    {
        $from = $data['from'];

        $author = new Author();
        $author->setIdentifier($from['id']);
        $author->setName($from['name']);
        $author->setLink($from['link']);

        $media = new Media();
        $media->setUrl($from['picture']['data']['url']);
        $media->setLink($from['link']);
        $author->setProfilePicture($media);

        $data['message'] = isset($data['message']) ? $data['message'] : '';

        $post = new FacebookPost();
        $post
            ->setIdentifier($data['id'])
            ->setMessage($data['message'])
            ->setAuthor($author)
            ->setCreatedAt(new \DateTime($data['created_time']))
            ;

        if (isset($data['full_picture'])) {
            $media = new Media();
            $media->setUrl($data['full_picture']);
            $media->setLink($data['link']);
            $post->addMedia($media);
        }

        $this->addPostReferences($post, $data);

        return $post;
    }

    /**
     * addPostReferences.
     *
     * @param $post
     * @param $data
     */
    protected function addPostReferences(&$post, $data)
    {
        $typeMap = array(
            'user' => ReferenceType::USER,
            'page' => ReferenceType::PAGE,
            'group' => ReferenceType::GROUP,
            'application' => ReferenceType::APPLICATION,
            'event' => ReferenceType::EVENT,
        );

        if (!isset($data['message_tags'])) {
            return;
        }

        foreach ($data['message_tags'] as $messageTagGroup) {
            if (isset($messageTagGroup['offset'])) {
                $messageTag = $messageTagGroup;
                if (!isset($typeMap[$messageTag['type']])) {
                    continue;
                }
                $reference = new Reference();
                $reference
                    ->setIndices(array($messageTag['offset'], $messageTag['offset'] + $messageTag['length']))
                    //->setType($typeMap[$messageTag['type']])
                    ->setData($messageTag);

                $post->addReference($reference);
            } else {
                foreach ($messageTagGroup as $messageTag) {
                    if (!isset($typeMap[$messageTag['type']])) {
                        continue;
                    }
                    $reference = new Reference();
                    $reference
                        ->setIndices(array($messageTag['offset'], $messageTag['offset'] + $messageTag['length']))
                        ->setType($typeMap[$messageTag['type']])
                        ->setData($messageTag);

                    $post->addReference($reference);
                }
            }
        }
    }
}
