<?php

namespace App\Helper\SubmittedCodeCHelper;

class StructuredRequirements
{

    public static function getStructuredRequirements($contentFile): array
    {
        $structuredRequirements = [];
        $splitContent = explode("\r\n\r\n", $contentFile);

        $structuredRequirements['baseRequirement'] = $splitContent[0];

        $requirements = [];
        foreach ($splitContent as $key => $requirement) {
            if ($key != 0) {
                $score = substr($requirement, -4, 1);
                $requirements[$key] = [
                    'content' => $requirement,
                    'score' => $score
                ];
            }
        }
        $structuredRequirements['requirements'] = $requirements;

        return $structuredRequirements;
    }
}