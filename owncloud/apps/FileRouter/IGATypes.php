<?php
/**
 * Created by PhpStorm.
 * User: SHARRISON
 * Date: 12/10/13
 * Time: 3:39 PM
 */

namespace OCA\FileRouter;


class IGATypes
{
    const ModelOneOptionOne = 1;
    const ModelOneOptionTwo = 2;
    const ModelTwo = 22;
    const Unknown = 99;

    static $options = array(
        self::ModelOneOptionOne => 'Model 1 Option 1',
        self::ModelOneOptionTwo => 'Model 1 Option 2',
        self::ModelTwo => 'Model Two',
        self::Unknown => 'Unknown'
    );

    public static function getLabel($igaType)
    {
        if (array_key_exists($igaType, self::$options)) {
            return self::$options[$igaType];
        }
        return self::$options[self::Unknown];
    }
} 