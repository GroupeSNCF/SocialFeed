<?php

/*
 * This file is part of the Social Feed Util.
 *
 * (c) LaNetscouade <contact@lanetscouade.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Lns\SocialFeed\Formatter;

use Lns\SocialFeed\Model\Reference;
use Lns\SocialFeed\Model\ReferenceType;

/**
 * FacebookMessageFormatter.
 */
class FacebookMessageFormatter extends AbstractMessageFormatter
{
    /**
     * formatMessagePart.
     *
     * @param $messagePart
     */
    protected function formatMessagePart($messagePart)
    {
        $reference = $messagePart['reference'];

        if (!$reference) {
            return $this->autoLink($messagePart['text']);
        }

        $data = $reference->getData();

        switch ($reference->getType()) {
        case ReferenceType::PAGE:
        case ReferenceType::GROUP:
        case ReferenceType::USER:
            return $this->createLinkString('https://www.facebook.com/'.$data['id'], $messagePart['text']);
            break;
        case ReferenceType::EVENT:
            return $this->createLinkString('https://www.facebook.com/events/'.$data['id'], $messagePart['text']);
            break;
        default:
            // Display raw text if no formatter is found.
            return $messagePart['text'];
            break;
        }
    }

    /**
     * autoLink.
     *
     * @param $message
     */
    protected function autoLink($message)
    {
        $message = parent::autoLink($message);

        return preg_replace_callback('/#(\w+)/u', function ($matches) {
            return $this->createLinkString('https://www.facebook.com/hashtag/'.$matches[1], $matches[0]);
        }, $message);
    }
}
