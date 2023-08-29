<?php

declare(strict_types=1);

namespace scripts;

require_once __DIR__ . '/../vendor/autoload.php';

use ReflectionClass;
use timanthonyalexander\BaseApi\module\OpenApi\OpenApi;

$routes = json_decode(file_get_contents(__DIR__ . '/../config/routes.json'), true);

uksort(
    $routes,
    function ($a, $b) {
        return strcmp($a, $b);
    }
);

$frontendPath = __DIR__ . '/../../baseapi-frontend';

$controllerPath = __DIR__ . '/../src/controller';
$responsePath = __DIR__ . '/../src/response';

$routesFolder = $frontendPath . '/src/routes';
$routesFile = $routesFolder . '/routes-generated.ts';

// Clear the routes folder
/*
$files = glob($routesFolder . '/*');

foreach ($files as $file) {
    if (is_file($file)) {
        unlink($file);
    }
}
*/

$allEndpoints = [];

$controllerNames = [];

foreach ($routes as $route => $controllers) {
    foreach ($controllers as $controller) {
        // $controllerPath . '/' . $controller . '/' . $controller . 'Controller.php';
        $controllerFile = $controllerPath . '/' . $controller . '/' . $controller . 'Controller.php';
        $controllerFile = str_replace('/scripts/../', '/', $controllerFile);

        $responseFile = $responsePath . '/' . $controller . '/' . $controller . 'Response.php';

        if (!file_exists($controllerFile)) {
            echo 'Controller file not found: ' . $controllerFile . PHP_EOL;
            continue;
        }

        // Get the public string properties
        $reflection = new ReflectionClass('timanthonyalexander\BaseApi\\controller\\' . $controller . '\\' . $controller . 'Controller');
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        $responseProperties = [];

        if (file_exists($responseFile)) {
            $responseReflection = new ReflectionClass('timanthonyalexander\BaseApi\\response\\' . $controller . '\\' . $controller . 'Response');
            $responseProperties = $responseReflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        }

        $params = [];
        $arrayParams = [];
        $files = [];

        foreach ($properties as $property) {
            if ($property->getType()->getName() === 'string' || $property->getType()->getName() === '?string') {
                $params[] = $property->getName();
            }
            // If array, add to $files
            if ($property->getType()->getName() === 'array') {
                if (str_contains($property->getName(), 'file') || str_contains($property->getName(), 'image')) {
                    $files[] = $property->getName();
                } else {
                    $arrayParams[] = $property->getName();
                }
            }
        }

        $responseParams = [];

        foreach ($responseProperties as $property) {
            if ($property->getType()->getName() === 'string' || $property->getType()->getName() === '?string') {
                $responseParams[] = $property->getName() . ': string';
            } elseif ($property->getType()->getName() === 'int' || $property->getType()->getName() === '?int') {
                $responseParams[] = $property->getName() . ': number';
            } elseif ($property->getType()->getName() === 'bool' || $property->getType()->getName() === '?bool') {
                $responseParams[] = $property->getName() . ': boolean';
            } else {
                $responseParams[] = $property->getName() . ': any[]';
            }
        }

        $controllerFile = file_get_contents($controllerFile);

        $gets = [];
        $posts = [];
        $getArrays = [];
        $postArrays = [];

        $getAction = substr($controllerFile, strpos($controllerFile, 'public function getAction(): void') + strlen('public function getAction(): void'));

        $hasPost = false;

        if (str_contains($controllerFile, 'public function postAction(): void')) {
            $hasPost = true;
            $postAction = substr($controllerFile, strpos($controllerFile, 'public function postAction(): void') + strlen('public function postAction(): void'));
        }

        foreach ($params as $param) {
            if (str_contains($getAction, '$this->' . $param)) {
                $gets[] = $param;
            }

            if ($hasPost) {
                if (str_contains($postAction, '$this->' . $param)) {
                    $posts[] = $param;
                }
            }
        }

        foreach ($arrayParams as $param) {
            if (str_contains($getAction, '$this->' . $param)) {
                $getArrays[] = $param;
            }

            if ($hasPost) {
                if (str_contains($postAction, '$this->' . $param)) {
                    $postArrays[] = $param;
                }
            }
        }

        if (!in_array($controller, $controllerNames)) {
            $controllerNames[] = $controller;
        } else {
            $controllerNames[] = $controller . count(array_keys($controllerNames, $controller));
            $controller = $controller . count(array_keys($controllerNames, $controller));
        }

        $allEndpoints[] = [
            'route' => $route,
            'controller' => $controller,
            'params' => $params,
            'gets' => $gets,
            'posts' => $posts,
            'files' => $files,
            'hasPost' => $hasPost,
            'hasFiles' => count($files) > 0,
            'getArrays' => $getArrays,
            'postArrays' => $postArrays,
            'responseParams' => $responseParams,
        ];
    }
}

$openApi = new OpenApi($allEndpoints);
$openApi->generate();
$openApi->save();

// The routes.ts file should look like this:
// export const routes = {
// ping: \'/pingroute&getParam1=1&getParam2=2\',

$routesFileContent = 'export const routes = {' . PHP_EOL;

foreach ($allEndpoints as $endpoint) {
    $route = $endpoint['route'];
    // Add get params
    if (count($endpoint['gets']) > 0) {
        $route .= '?';
        foreach ($endpoint['gets'] as $get) {
            $route .= $get . '={' . $get . '}&';
        }
        $route = substr($route, 0, -1);
    }
    $routesFileContent .= '    ' . $endpoint['controller'] . ': \'' . $route . '\',' . PHP_EOL;
}

// Add foreach endpoint a post route without the params
foreach ($allEndpoints as $endpoint) {
    if ($endpoint['hasPost']) {
        $route = $endpoint['route'];
        $routesFileContent .= '    ' . $endpoint['controller'] . 'Post: \'' . $route . '\',' . PHP_EOL;
    }
}

$routesFileContent .= '};' . PHP_EOL;

file_put_contents($routesFile, $routesFileContent);

// The controller.ts file should look like this:
// import { routes } from \'./routes-generated\';
// export default function PingEndpointGet(getParam1: string, getParam2: string) {
// return GetWithHook(routes.ping, { getParam1: params.getParam1, getParam2: params.getParam2 });
// }
// export default function PingEndpointPost(postParam1: string, postParam2: string) {
// return PostWithHook(routes.ping, { postParam1: params.postParam1, postParam2: params.postParam2 });
// }
// export default function PingEndpointGetPromise(getParam1: string, getParam2: string) {
// return GetWithPromise(routes.ping, { getParam1: params.getParam1, getParam2: params.getParam2 });
// }
// export default function PingEndpointPostPromise(postParam1: string, postParam2: string) {
// return PostWithPromise(routes.ping, { postParam1: params.postParam1, postParam2: params.postParam2 });
// }
// export default function PingEndpointWithFilesPromise(getParam1: string, getParam2: string, fileParam: Array<File>) {
//
//    return PostWithPromiseWithMultipleFiles(
//        routes.ping,
//        {
//            getParam1: getParam1,
//            getParam2: getParam2,
//        },
//        fileParam,
//   )
//   }

foreach ($allEndpoints as $endpoint) {
    $controllerfile = __DIR__ . '/../../baseapi-frontend/src/routes/' . $endpoint['controller'] . '.ts';

    if (count($endpoint['responseParams']) > 0) {
        $responseFile = __DIR__ . '/../../baseapi-frontend/src/responses/' . $endpoint['controller'] . '.ts';
        $responseFileContent = 'interface ' . $endpoint['controller'] . 'Response {' . PHP_EOL;

        // Add some params for example:
        // status: number;
        // responseMessage: string;
        // route: string;
        // userState: {
        // userModel: {
        // id: number;
        // name: string;
        // email: string;
        // description: string;
        // image: string;
        // language: string;
        // role: string;
        // lastonline: string;
        // },
        // isLogin: boolean;
        // }
        // data: {
        //  Here the response params
        // }
        // responseTime: number;
        // trace: any;
        // sessId: string;
        // params: any;
        // profiler: any;
        // queries: any;
        // ab: string;
        // retries: number;

        $responseFileContent .= '    status: number;' . PHP_EOL;
        $responseFileContent .= '    responseMessage: string;' . PHP_EOL;
        $responseFileContent .= '    route: string;' . PHP_EOL;
        $responseFileContent .= '    userState: {' . PHP_EOL;
        $responseFileContent .= '        userModel: {' . PHP_EOL;
        $responseFileContent .= '            id: number;' . PHP_EOL;
        $responseFileContent .= '            name: string;' . PHP_EOL;
        $responseFileContent .= '            email: string;' . PHP_EOL;
        $responseFileContent .= '            description: string;' . PHP_EOL;
        $responseFileContent .= '            image: string;' . PHP_EOL;
        $responseFileContent .= '            language: string;' . PHP_EOL;
        $responseFileContent .= '            role: string;' . PHP_EOL;
        $responseFileContent .= '            lastonline: string;' . PHP_EOL;
        $responseFileContent .= '        };' . PHP_EOL;
        $responseFileContent .= '        isLogin: boolean;' . PHP_EOL;
        $responseFileContent .= '    };' . PHP_EOL;
        $responseFileContent .= '    data: {' . PHP_EOL;

        foreach ($endpoint['responseParams'] as $responseParam) {
            $responseFileContent .= '        ' . $responseParam . ';' . PHP_EOL;
        }

        $responseFileContent .= '    };' . PHP_EOL;
        $responseFileContent .= '    responseTime: number;' . PHP_EOL;
        $responseFileContent .= '    trace: any;' . PHP_EOL;
        $responseFileContent .= '    sessId: string;' . PHP_EOL;
        $responseFileContent .= '    params: any;' . PHP_EOL;
        $responseFileContent .= '    profiler: any;' . PHP_EOL;
        $responseFileContent .= '    queries: any;' . PHP_EOL;
        $responseFileContent .= '    ab: string;' . PHP_EOL;
        $responseFileContent .= '    retries: number;' . PHP_EOL;

        $responseFileContent .= '}' . PHP_EOL . PHP_EOL;

        $responseFileContent .= 'export default ' . $endpoint['controller'] . 'Response;' . PHP_EOL;

        file_put_contents($responseFile, $responseFileContent);
    }

    $controllerFileContent = 'import { routes } from \'./routes-generated\';' . PHP_EOL . PHP_EOL;
    // Import import { GetWithHook } from \'../modules/api/client\';

    $possibleFunctions = [
        'GetWithHook',
        'PostWithHook',
        'GetWithPromise',
        'PostWithPromise',
        'PostWithPromiseWithMultipleFiles',
    ];

    $neededFunctions = [];

    $neededFunctions[] = 'GetWithHook';
    $neededFunctions[] = 'GetWithPromise';

    if ($endpoint['hasPost']) {
        $neededFunctions[] = 'PostWithPromise';
    }

    if ($endpoint['hasFiles']) {
        $neededFunctions[] = 'PostWithPromiseWithMultipleFiles';
    }

    $neededFunctionsString = implode(', ', $neededFunctions);

    $controllerFileContent .= 'import { ' . $neededFunctionsString . ' } from \'../modules/api/client\';' . PHP_EOL . PHP_EOL;

    // Get
    $controllerFileContent .= 'export function ' . $endpoint['controller'] . '2EndpointGetHook(' . PHP_EOL;

    if (count($endpoint['gets']) > 0) {
        foreach ($endpoint['gets'] as $get) {
            $controllerFileContent .= '    ';
            $controllerFileContent .= 'param_';
            $controllerFileContent .= $get . ': string, ' . PHP_EOL;
        }
    }

    if (count($endpoint['getArrays']) > 0) {
        foreach ($endpoint['getArrays'] as $getArray) {
            $controllerFileContent .= '    ';
            $controllerFileContent .= 'param_';
            $controllerFileContent .= $getArray . ': Array<string>, ' . PHP_EOL;
        }
    }

    $controllerFileContent .= ') {' . PHP_EOL;

    // If getarrays are present, create object \'data = {}\' and add all !null getarrays to it
    if (count($endpoint['getArrays']) > 0) {
        $controllerFileContent .= '    const data: any = {};' . PHP_EOL;
        foreach ($endpoint['getArrays'] as $getArray) {
            $controllerFileContent .= '    ';
            $controllerFileContent .= 'if (param_' . $getArray . ' !== null) {' . PHP_EOL;
            $controllerFileContent .= '        ';
            $controllerFileContent .= 'data.' . $getArray . ' = param_' . $getArray . ';' . PHP_EOL;
            $controllerFileContent .= '    }' . PHP_EOL;
        }
    }

    $controllerFileContent .= '    return GetWithHook(routes.' . $endpoint['controller'] . ', {' . PHP_EOL;

    // If getarrays are present, add ...data to the beginning of the return
    if (count($endpoint['getArrays']) > 0) {
        $controllerFileContent .= '        ...data,' . PHP_EOL;
    }

    if (count($endpoint['gets']) > 0) {
        foreach ($endpoint['gets'] as $get) {
            $controllerFileContent .= '        ';
            $controllerFileContent .= $get . ': param_' . $get . ', ' . PHP_EOL;
        }
    }

    $controllerFileContent .= '    ';
    $controllerFileContent .= '});' . PHP_EOL;

    $controllerFileContent .= '}' . PHP_EOL . PHP_EOL;

    // Get Promise
    $controllerFileContent .= 'export function ' . $endpoint['controller'] . '2EndpointGetPromise(' . PHP_EOL;

    if (count($endpoint['gets']) > 0) {
        foreach ($endpoint['gets'] as $get) {
            $controllerFileContent .= '    ';
            $controllerFileContent .= 'param_';
            $controllerFileContent .= $get . ': string, ' . PHP_EOL;
        }
    }

    $controllerFileContent .= ') {' . PHP_EOL;

    $controllerFileContent .= '    return GetWithPromise(routes.' . $endpoint['controller'] . ', {' . PHP_EOL;

    if (count($endpoint['gets']) > 0) {
        foreach ($endpoint['gets'] as $get) {
            $controllerFileContent .= '        ';
            $controllerFileContent .= $get . ': param_' . $get . ', ' . PHP_EOL;
        }
    }

    $controllerFileContent .= '    ';
    $controllerFileContent .= '});' . PHP_EOL;

    $controllerFileContent .= '}' . PHP_EOL . PHP_EOL;

    // Post Promise
    if ($endpoint['hasPost']) {
        $controllerFileContent .= 'export function ' . $endpoint['controller'] . '2EndpointPostPromise(' . PHP_EOL;

        if (count($endpoint['posts']) > 0) {
            foreach ($endpoint['posts'] as $post) {
                $controllerFileContent .= '    ';
                $controllerFileContent .= 'param_';
                $controllerFileContent .= $post . ': string, ' . PHP_EOL;
            }
        }

        if (count($endpoint['postArrays']) > 0) {
            foreach ($endpoint['postArrays'] as $postArray) {
                $controllerFileContent .= '    ';
                $controllerFileContent .= 'param_';
                $controllerFileContent .= $postArray . ': Array<string>, ' . PHP_EOL;
            }
        }

        $controllerFileContent .= ') {' . PHP_EOL;

        // If postarrays are present, create object \'data = {}\' and add all !null postarrays to it
        if (count($endpoint['postArrays']) > 0) {
            $controllerFileContent .= '    const data: any = {};' . PHP_EOL;
            foreach ($endpoint['postArrays'] as $postArray) {
                $controllerFileContent .= '    ';
                $controllerFileContent .= 'if (param_' . $postArray . ' !== null) {' . PHP_EOL;
                $controllerFileContent .= '        ';
                $controllerFileContent .= 'data.' . $postArray . ' = param_' . $postArray . ';' . PHP_EOL;
                $controllerFileContent .= '    }' . PHP_EOL;
            }
        }

        $controllerFileContent .= '    return PostWithPromise(routes.' . $endpoint['controller'] . 'Post, {' . PHP_EOL;

        // If postarrays are present, add ...data to the beginning of the return
        if (count($endpoint['postArrays']) > 0) {
            $controllerFileContent .= '        ...data,' . PHP_EOL;
        }

        if (count($endpoint['posts']) > 0) {
            foreach ($endpoint['posts'] as $post) {
                $controllerFileContent .= '        ';
                $controllerFileContent .= $post . ': param_' . $post . ', ' . PHP_EOL;
            }
        }

        $controllerFileContent .= '    ';
        $controllerFileContent .= '});' . PHP_EOL;

        $controllerFileContent .= '}' . PHP_EOL . PHP_EOL;
    }

    // Post Promise with files
    if ($endpoint['hasPost'] && $endpoint['hasFiles']) {
        $controllerFileContent .= 'export function ' . $endpoint['controller'] . '2EndpointWithFilesPromise(' . PHP_EOL;

        if (count($endpoint['posts']) > 0) {
            foreach ($endpoint['posts'] as $post) {
                $controllerFileContent .= '    ';
                $controllerFileContent .= 'param_';
                $controllerFileContent .= $post . ': string, ' . PHP_EOL;
            }
        }

        if (count($endpoint['postArrays']) > 0) {
            foreach ($endpoint['postArrays'] as $postArray) {
                $controllerFileContent .= '    ';
                $controllerFileContent .= 'param_';
                $controllerFileContent .= $postArray . ': Array<string>, ' . PHP_EOL;
            }
        }

        if (count($endpoint['files']) > 0) {
            foreach ($endpoint['files'] as $file) {
                $controllerFileContent .= '    ';
                $controllerFileContent .= 'param_';
                $controllerFileContent .= $file . ': Array<File>, ' . PHP_EOL;
            }
        }

        $controllerFileContent .= ') {' . PHP_EOL;

        // If postarrays are present, create object \'data = {}\' and add all !null postarrays to it
        if (count($endpoint['postArrays']) > 0) {
            $controllerFileContent .= '    const data: any = {};' . PHP_EOL;
            foreach ($endpoint['postArrays'] as $postArray) {
                $controllerFileContent .= '    ';
                $controllerFileContent .= 'if (param_' . $postArray . ' !== null) {' . PHP_EOL;
                $controllerFileContent .= '        ';
                $controllerFileContent .= 'data.' . $postArray . ' = param_' . $postArray . ';' . PHP_EOL;
                $controllerFileContent .= '    }' . PHP_EOL;
            }
        }

        $controllerFileContent .= '    return PostWithPromiseWithMultipleFiles(routes.' . $endpoint['controller'] . 'Post, {' . PHP_EOL;

        // If postarrays are present, add ...data to the beginning of the return
        if (count($endpoint['postArrays']) > 0) {
            $controllerFileContent .= '        ...data,' . PHP_EOL;
        }

        if (count($endpoint['posts']) > 0) {
            foreach ($endpoint['posts'] as $post) {
                $controllerFileContent .= '        ';
                $controllerFileContent .= $post . ': param_' . $post . ', ' . PHP_EOL;
            }
        }

        $controllerFileContent .= '    ' . '}, ' . PHP_EOL;

        if (count($endpoint['files']) > 0) {
            foreach ($endpoint['files'] as $file) {
                $controllerFileContent .= '        ';
                $controllerFileContent .= 'param_' . $file . ', ' . PHP_EOL;
            }
        }

        $controllerFileContent .= '    ';
        $controllerFileContent .= ');' . PHP_EOL;

        $controllerFileContent .= '}' . PHP_EOL . PHP_EOL;
    }

    file_put_contents($controllerfile, $controllerFileContent);
}

shell_exec('cd ' . $frontendPath . ' && npm run fix');
