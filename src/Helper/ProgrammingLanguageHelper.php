<?php

namespace App\Helper;

use App\Helper\ReservedWordsHelper\CppReservedWordsEnum;

class ProgrammingLanguageHelper
{
//    private string $programmingLanguage;
//    private array $reservedWords = [];

    public function __construct(string $programmingLanguage)
    {
//        $this->$programmingLanguage = $programmingLanguage;
    }

    public static function getReservedWords(string $programmingLanguage): array
    {
        switch ($programmingLanguage) {
            case 'cpp':
                $reservedWords = CppReservedWordsEnum::getAllValuesCpp();
                break;
            default:
                $reservedWords = [];
                break;
        }

        return $reservedWords;
    }

}