<?php

namespace App\Libraries\Questionnaire;

class QuestionType
{
    const MULTIPLE_CHOICE = 'MULTIPLE_CHOICE';
    const CHECKBOXES = 'CHECKBOXES';
    const DROPDOWN = 'DROPDOWN';
    const TEXT_BOX = 'TEXT_BOX';
    const DATE_TIME = 'DATE_TIME';

    /**
     * @param string $type
     * @return bool
     */
    public static function checkType(string $type)
    {
        return defined("self::{$type}");
    }
}
