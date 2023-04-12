<?php

namespace App\Helper;

use Exception;
use Symfony\Component\HttpClient\HttpClient;

class CompilerHelper
{
    private string $submittedContent;
    private const CLIENT_ID = 'e5444e7ee16db3e9a3f7d83f231ee20e';
    private const CLIENT_SECRET = 'f3e4e9db2a8f500490b9abdffaf5a60d2e3fc5686bf71af4ee523e946d1948b9';
    private const LANGUAGE = 'c';
    private const VERSION_INDEX = '5';
    private array $data = [];
    private bool $compiledSuccessfully = false;

    public function __construct(string $submittedContent)
    {
        $this->submittedContent = $submittedContent;
    }

    public function makeApiCall(): array
    {
//        $arraySubmittedContent = explode("\r\n", $this->submittedContent);
        try {
            $client = HttpClient::create();
            $url = "https://api.jdoodle.com/v1/execute";
            $clientId = self::CLIENT_ID;
            $clientSecret = self::CLIENT_SECRET;
            $script = $this->submittedContent;
            $language = self::LANGUAGE;
            $versionIndex = self::VERSION_INDEX;

            $jsonData = array(
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
                'script' => $script,
                'language' => $language,
                'versionIndex' => $versionIndex
            );

            $response = $client->request('POST', $url, [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'json' => $jsonData
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode != 200) {
                throw new Exception("Please check your inputs: HTTP error code : " . $statusCode);
            }

            $this->data = $response->toArray();
            if (
                !(strpos($this->data['output'], 'error'))
                && $this->data['memory']
            ) {
                $this->compiledSuccessfully = true;
            }

        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return [$this->data['output'], $this->compiledSuccessfully];
    }

}