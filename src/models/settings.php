<?php

namespace iamdangavin\vimeo\models;

use craft\base\Model;

class Settings extends Model
{
    public $vimeoAPIKey = '';
    public $vimeoAPICache = 8600;

    public function rules()
    {
        return [
            [['vimeoAPIKey', 'vimeoAPICache'], 'required'],
        ];
    }
}