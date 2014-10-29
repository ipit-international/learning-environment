<?php
/**
 * Created by PhpStorm.
 * User: SHARRISON
 * Date: 12/10/13
 * Time: 3:32 PM
 */

namespace OCA\FileRouter;


class TransmissionStates
{
    const PendingReview = 0;
    const Released = 1;
    const Rejected = 2;
    const AutoReleased = 3;
    const Deleted = 4;

    const Unknown = 99;

    static $states = array(
        self::PendingReview => 'Pending Review',
        self::Released => 'Released',
        self::Rejected => 'Rejected',
        self::AutoReleased => 'Automatically Released',
        self::Deleted => 'Deleted',
        self::Unknown => 'Unknown'

    );

    public static function getLabel($state)
    {
        if (array_key_exists($state, self::$states)) {
            return self::$states[$state];
        }
        return self::$states[self::Unknown];
    }
} 