<?php

declare(strict_types=1);

namespace timanthonyalexander\BaseApi\module\OpenApi;

class OpenApi
{
    private array $openApiDocumentation = [];

    public function __construct(
        private readonly array $endpoints = []
    ) {
    }

    public function generate(): void
    {
        $openapi = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'baseapi API',
                'description' => 'This is the openapi definition of the baseapi API',
                'version' => '1.0.0',
            ],
            'paths' => [],
        ];

        foreach ($this->endpoints as $endpoint) {
            $pathItem = [];

            if ($endpoint['hasPost']) {
                $pathItem['post'] = [
                    'summary' => $endpoint['controller'],
                    'description' => $endpoint['route'],
                    'requestBody' => [
                        'content' => [
                            'multipart/form-data' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [],
                ];

                foreach ($endpoint['posts'] as $postParam) {
                    $pathItem['post']['requestBody']['content']['multipart/form-data']['schema']['properties'][$postParam] = [
                        'type' => 'string',
                    ];
                }

                foreach ($endpoint['postArrays'] as $postArray) {
                    $pathItem['post']['requestBody']['content']['multipart/form-data']['schema']['properties'][$postArray] = [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string',
                        ],
                    ];
                }

                $files = $endpoint['files'];
                $fileProperties = [];

                foreach ($files as $file) {
                    $fileProperties[$file] = [
                        'type' => 'string',
                        'format' => 'binary',
                    ];
                }

                if ($endpoint['hasFiles']) {
                    $pathItem['post']['requestBody']['content']['multipart/form-data'] = [
                        'schema' => [
                            'type' => 'object',
                            'properties' => $fileProperties,
                        ],
                    ];
                }
            }

            if (!empty($endpoint['gets'])) {
                $pathItem['get'] = [
                    'summary' => $endpoint['controller'],
                    'description' => $endpoint['route'],
                    'parameters' => [],
                    'responses' => [/* Your responses here */],
                ];

                foreach ($endpoint['gets'] as $getParam) {
                    $pathItem['get']['parameters'][] = [
                        'name' => $getParam,
                        'in' => 'query',
                        'required' => false,
                        'schema' => [
                            'type' => 'string',
                        ],
                    ];
                }

                foreach ($endpoint['getArrays'] as $getArray) {
                    $pathItem['get']['parameters'][] = [
                        'name' => $getArray,
                        'in' => 'query',
                        'required' => false,
                        'schema' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'string',
                            ],
                        ],
                    ];
                }
            }

            $openapi['paths'][$endpoint['route']] = $pathItem;
        }

        $this->openApiDocumentation = $openapi;
    }

    public function save(): void
    {
        file_put_contents(
            __DIR__ . '/../../../docs/openapi.json',
            json_encode($this->openApiDocumentation, JSON_PRETTY_PRINT)
        );
    }
}
