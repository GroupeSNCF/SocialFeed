<?php

/*
 * This file is part of the Social Feed Util.
 *
 * (c) LaNetscouade <contact@lanetscouade.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Lns\SocialFeed\Model;

use CommerceGuys\Enum\AbstractEnum;

/**
 * ReferenceType.
 */
class ReferenceType extends AbstractEnum
{
    const URL = 'url';
    const USER = 'user';
    const PAGE = 'page';
    const GROUP = 'group';
    const HASHTAG = 'hashtag';
    const VIDEO = 'video';
    const MEDIA = 'media';
    const APPLICATION = 'application';
    const EVENT = 'event';
}
