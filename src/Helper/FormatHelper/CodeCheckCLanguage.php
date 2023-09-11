<?php

namespace App\Helper\FormatHelper;

class CodeCheckCLanguage
{

    public static function checkSubmittedRequirementsAnswers(array $requirements, string $submittedCode): array
    {
        foreach ($requirements as $key => $requirement) {
            if ($key == 1) {
                [$isValid, $errors] = self::validateFirstRequirement($requirement, $submittedCode);
                $req['isValid'] = $isValid;
                $req['errors'] = $errors;

                $requirements[$key][] = $req;
            }
        }

        return [$requirements];
    }

    private static function validateFirstRequirement(array $requirement, string $submittedCode)
    {
        [$structName, $attributesArray] = self::getStructDetails($requirement);
        [$isValid, $errors] = self::isValidUserStruct($structName, $attributesArray, $submittedCode);
//        if ($isValid) {
//            dd('must check the creation of objects');
//        } else {
//        }
        return [$isValid, $errors];

    }

    private static function getStructDetails(array $requirement)
    {
        $structName = '';
        $splitRequirement = explode(" ", $requirement['content']);
        foreach ($splitRequirement as $key => $value) {
            if ($value == 'structure') {
                $structName = $splitRequirement[$key + 1];
            }
        }
        $splitAttributes = explode(', ', explode(': ', $requirement['content'])[1]);
        $attributesArray = [];
        foreach ($splitAttributes as $k => $val) {
            $attr = explode(' ', $val);
            $attribute['attributeName'] = trim($attr[0]);
            $attr[1] = str_replace('(', '', $attr[1]);
            $attr[1] = str_replace(')', '', $attr[1]);
            $attr[1] = str_replace('.', '', $attr[1]);
            $attribute['dataType'] = trim($attr[1]);

            $attributesArray[$k] = $attribute;
        }

        return [$structName, $attributesArray];
    }

    private static function isValidUserStruct(mixed $structName, array $attributesArray, string $submittedCode)
    {
        $errors = [];
        $isValid = false;
        $attributesFound = 0;
        $attrNames = '';
        $attributesNoRequired = count($attributesArray);
        $searchForStructureNamePosition = strpos($submittedCode, 'struct ' . $structName . ' {');
        $searchForStructurePos = strpos($submittedCode, 'struct ' . $structName . '{');
        $enclosingStruct = strpos($submittedCode, '}');
        if ($searchForStructureNamePosition || $searchForStructurePos) {
            $submittedStruct = substr(
                $submittedCode,
                $searchForStructureNamePosition,
                $enclosingStruct - $searchForStructureNamePosition + 2
            );
            if ($submittedStruct) {
                $tmpArray = explode("\r\n", $submittedStruct);
                foreach ($tmpArray as $k => $value) {
                    $lineArraySubmitted = explode(' ', trim($value));
                    foreach ($attributesArray as $key => $attribute) {
                        if (
                            (in_array($attribute['attributeName'], $lineArraySubmitted)
                            || in_array($attribute['attributeName'] . ';', $lineArraySubmitted))
                            && in_array($attribute['dataType'], $lineArraySubmitted)
                        ) {
                            $attributesFound++;
                            $attrNames .= ' ' .  $attribute['dataType'] . ' ' .  $attribute['attributeName'] . ',';
                        }
                    }
                }
                if ($attributesFound == $attributesNoRequired) {
                    $isValid = true;
//                    return ([true, []]);
                } else {
                    $requiredAttributes = '';
                    foreach ($attributesArray as $k => $value) {
                        $requiredAttributes .= implode(' ', $value) . ', ';
                    }
                    $isValid = false;
                    $errors[] = 'Required attributes names and/or data types are invalid!'
                        . ' Required attributes: ' . $requiredAttributes
                        . ' Found attributes: ' . $attrNames . '( only ' . $attributesFound . ' were correct)';

//                    return ([false, $errors]);
                }

            }
        } else {
            $foundStructNamePosition = strpos($submittedCode, 'struct ');
            $delimiter = strpos($submittedCode, ' {');
            $foundStructName = explode("\r\n", substr($submittedCode, $foundStructNamePosition, $delimiter))[0];

            $errors[1] = 'Struct name could not be identified. Required name: ' . $structName
                . ', user submitted struct name: ' . $foundStructName;

        }

        return [$isValid, $errors];
    }
}