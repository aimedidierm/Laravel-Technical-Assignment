<?php

namespace App\Console\Commands;

use App\Attributes\ApiEndpoint;
use App\Attributes\ApiRequestBody;
use App\Attributes\ApiResponse;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use Illuminate\Console\Command;
use ReflectionClass;

class GenerateOpenApi extends Command
{
    protected $signature = 'openapi:generate';
    protected $description = 'Generate OpenAPI v3 specification from PHP 8 attributes';

    private array $controllers = [
        AuthController::class,
        EmployeeController::class,
        AttendanceController::class,
    ];

    public function handle(): void
    {
        $spec = [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'Employee Management API',
                'version' => '1.0.0',
                'description' => 'RESTful API for employee and attendance management.',
            ],
            'servers' => [['url' => '/api']],
            'paths' => [],
        ];

        foreach ($this->controllers as $class) {
            foreach ((new ReflectionClass($class))->getMethods() as $method) {
                $endpointAttrs = $method->getAttributes(ApiEndpoint::class);
                if (empty($endpointAttrs)) continue;

                $endpoint = $endpointAttrs[0]->newInstance();
                $httpMethod = strtolower($endpoint->method);
                $path = str_replace('/api', '', $endpoint->path);

                $operation = [
                    'summary' => $endpoint->summary,
                    'tags' => $endpoint->tags,
                    'responses' => $this->resolveResponses($method),
                ];

                if ($endpoint->description !== '') {
                    $operation['description'] = $endpoint->description;
                }

                $bodyAttrs = $method->getAttributes(ApiRequestBody::class);
                if (!empty($bodyAttrs)) {
                    $body = $bodyAttrs[0]->newInstance();
                    $operation['requestBody'] = [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => array_map(
                                        fn(string $type) => ['type' => $type === 'email' ? 'string' : $type],
                                        $body->properties
                                    ),
                                    'required' => $body->required,
                                ],
                            ],
                        ],
                    ];
                }

                $spec['paths'][$path][$httpMethod] = $operation;
            }
        }

        $json = json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $outputPath = base_path('docs/openapi.json');

        @mkdir(dirname($outputPath), 0755, true);
        file_put_contents($outputPath, $json);

        $this->info("OpenAPI spec generated at docs/openapi.json ({$this->countEndpoints($spec)} endpoints).");
    }

    private function resolveResponses(\ReflectionMethod $method): array
    {
        $responses = [];

        foreach ($method->getAttributes(ApiResponse::class) as $attr) {
            $response = $attr->newInstance();
            $responses[(string) $response->status] = ['description' => $response->description];
        }

        return $responses ?: ['200' => ['description' => 'Success']];
    }

    private function countEndpoints(array $spec): int
    {
        return array_sum(array_map(fn(array $methods) => count($methods), $spec['paths']));
    }
}
