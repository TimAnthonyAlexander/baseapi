# Example response for default setup

## /ping.json GET

```
{
    "status": 200,
    "responseMessage": null,
    "route": "\/ping.json",
    "userState": {
        "userModel": {
            "id": "guest",
            "name": "",
            "description": "New to baseapi",
            "email": "",
            "role": "guest",
            "isVerified": false,
            "language": "english",
            "image": "default.png",
            "views": 0,
            "cli": false,
            "redirect": "\/home",
            "username": "guest"
        },
        "isLogin": false
    },
    "data": {
        "ping": "pong"
    },
    "responseTime": 0,
    "trace": [
        [
            [
                {
                    "file": "\/Users\/tim.alexander\/baseapi\/src\/module\/Api\/Api.php",
                    "line": 124,
                    "function": "__construct",
                    "class": "timanthonyalexander\\BaseApi\\controller\\Abstract\\AbstractController",
                    "type": "->"
                },
                {
                    "file": "\/Users\/tim.alexander\/baseapi\/src\/module\/Api\/Api.php",
                    "line": 74,
                    "function": "sendToController",
                    "class": "timanthonyalexander\\BaseApi\\module\\Api\\Api",
                    "type": "->"
                },
                {
                    "file": "\/Users\/tim.alexander\/baseapi\/src\/module\/Page\/Page.php",
                    "line": 42,
                    "function": "send",
                    "class": "timanthonyalexander\\BaseApi\\module\\Api\\Api",
                    "type": "->"
                },
                {
                    "file": "\/Users\/tim.alexander\/baseapi\/src\/module\/Page\/Page.php",
                    "line": 33,
                    "function": "send",
                    "class": "timanthonyalexander\\BaseApi\\module\\Page\\Page",
                    "type": "->"
                },
                {
                    "file": "\/Users\/tim.alexander\/baseapi\/public\/index.php",
                    "line": 10,
                    "function": "route",
                    "class": "timanthonyalexander\\BaseApi\\module\\Page\\Page",
                    "type": "->"
                },
                {
                    "file": "\/Users\/tim.alexander\/baseapi\/router.php",
                    "line": 8,
                    "args": [
                        "\/Users\/tim.alexander\/baseapi\/public\/index.php"
                    ],
                    "function": "include"
                }
            ]
        ]
    ],
    "sessId": "n3kuhltob01ngl2segquam12iv",
    "params": {
        "PHPSESSID": "n3kuhltob01ngl2segquam12iv"
    },
    "profiler": {
        "timanthonyalexander\/BaseApi\/controller\/Abstract\/AbstractController": {
            "__construct()": {
                "start": 1693333533.76785,
                "calledBy": "timanthonyalexander\\BaseApi\\module\\Api\\Api::sendToController",
                "end": "controller",
                "stop": 1693333533.767918,
                "calls": 1,
                "total": 6.818771362304688e-5,
                "perCall": 6.818771362304688e-5,
                "percentage": 77.29729729729729
            },
            "__construct(execute)": {
                "start": 1693333533.767895,
                "calledBy": "timanthonyalexander\\BaseApi\\module\\Api\\Api::sendToController",
                "end": "controller",
                "stop": 1693333533.767915,
                "calls": 1,
                "total": 2.002716064453125e-5,
                "perCall": 2.002716064453125e-5,
                "percentage": 22.702702702702705
            }
        }
    },
    "queries": {},
    "retries": 0
}
```

## /ping.json POST pingMessage: Hello
```
{
    "status": 200,
    "responseMessage": null,
    "route": "\/ping.json",
    "userState": {
        "userModel": {
            "id": "guest",
            "name": "",
            "description": "New to baseapi",
            "email": "",
            "role": "guest",
            "isVerified": false,
            "language": "english",
            "image": "default.png",
            "views": 0,
            "cli": false,
            "redirect": "\/home",
            "username": "guest"
        },
        "isLogin": false
    },
    "data": {
        "ping": "Hello"
    },
    "responseTime": 0,
    "trace": [
        [
            [
                {
                    "file": "\/Users\/tim.alexander\/baseapi\/src\/module\/Api\/Api.php",
                    "line": 124,
                    "function": "__construct",
                    "class": "timanthonyalexander\\BaseApi\\controller\\Abstract\\AbstractController",
                    "type": "->"
                },
                {
                    "file": "\/Users\/tim.alexander\/baseapi\/src\/module\/Api\/Api.php",
                    "line": 74,
                    "function": "sendToController",
                    "class": "timanthonyalexander\\BaseApi\\module\\Api\\Api",
                    "type": "->"
                },
                {
                    "file": "\/Users\/tim.alexander\/baseapi\/src\/module\/Page\/Page.php",
                    "line": 42,
                    "function": "send",
                    "class": "timanthonyalexander\\BaseApi\\module\\Api\\Api",
                    "type": "->"
                },
                {
                    "file": "\/Users\/tim.alexander\/baseapi\/src\/module\/Page\/Page.php",
                    "line": 33,
                    "function": "send",
                    "class": "timanthonyalexander\\BaseApi\\module\\Page\\Page",
                    "type": "->"
                },
                {
                    "file": "\/Users\/tim.alexander\/baseapi\/public\/index.php",
                    "line": 10,
                    "function": "route",
                    "class": "timanthonyalexander\\BaseApi\\module\\Page\\Page",
                    "type": "->"
                },
                {
                    "file": "\/Users\/tim.alexander\/baseapi\/router.php",
                    "line": 8,
                    "args": [
                        "\/Users\/tim.alexander\/baseapi\/public\/index.php"
                    ],
                    "function": "include"
                }
            ]
        ]
    ],
    "sessId": "n3kuhltob01ngl2segquam12iv",
    "params": {
        "PHPSESSID": "n3kuhltob01ngl2segquam12iv"
    },
    "profiler": {
        "timanthonyalexander\/BaseApi\/controller\/Abstract\/AbstractController": {
            "__construct()": {
                "start": 1693333533.76785,
                "calledBy": "timanthonyalexander\\BaseApi\\module\\Api\\Api::sendToController",
                "end": "controller",
                "stop": 1693333533.767918,
                "calls": 1,
                "total": 6.818771362304688e-5,
                "perCall": 6.818771362304688e-5,
                "percentage": 77.29729729729729
            },
            "__construct(execute)": {
                "start": 1693333533.767895,
                "calledBy": "timanthonyalexander\\BaseApi\\module\\Api\\Api::sendToController",
                "end": "controller",
                "stop": 1693333533.767915,
                "calls": 1,
                "total": 2.002716064453125e-5,
                "perCall": 2.002716064453125e-5,
                "percentage": 22.702702702702705
            }
        }
    },
    "queries": {},
    "retries": 0
}
```

