<?php

namespace timanthonyalexander\BaseApi\module\UriService;

use League\Uri\Uri;
use timanthonyalexander\BaseApi\module\EnvService\EnvService;

/**
 * This builds a URI from given parameters with default values
 */
class UriService
{
    private Uri $uri;

    private array $query = [];
    private string $queryString = '';
    private string $scheme = 'https';
    private string $host = 'example.com';
    private int $port = 443;
    private string $anchor = '';

    private const PROTOCOL_SEPARATOR = '://';
    private const LOCALPORT = 62002;
    private const LOCALPORTAPI = 62001;

    public function __construct(private string $path = '/')
    {
    }

    public function withScheme(string $scheme): self
    {
        $this->scheme = $scheme;
        return $this;
    }

    public function withPort(int $port): self
    {
        $this->port = $port;
        return $this;
    }

    public function withPath(string $path, string ...$replacements): self
    {
        $this->path = sprintf($path, ...$replacements);
        if (!str_starts_with($this->path, '/')) {
            $this->path = '/' . $this->path;
        }
        return $this;
    }

    public function withAnchor(string $anchor): self
    {
        $this->anchor = $anchor;
        return $this;
    }

    public function toEnv(string $env = 'dev'): self
    {
        $this->host = match ($env) {
            'dev'     => 'dev.baseapi-test.app',
            'integration' => 'int.example.com',
            'staging' => 'staging.example.com',
            default => 'example.com',
        };
        $this->port = match ($env) {
            'dev'     => self::LOCALPORT,
            default => 443,
        };

        return $this;
    }

    public function fromEnv(
        bool $frontend = true,
    ): self {
        $env = EnvService::getEnv();

        $this->scheme = match ($env) {
            'dev'     => 'http',
            default => 'https',
        };

        if ($frontend) {
            $this->host   = match ($env) {
                'dev'     => 'dev.baseapi-test.app',
                'integration' => 'int.example.com',
                'staging' => 'staging.example.com',
                default => 'example.com',
            };
            $this->port   = match ($env) {
                'dev'     => self::LOCALPORT,
                default => 443,
            };
        } else {
            $this->host   = match ($env) {
                'dev'     => 'dev.baseapi-test.app',
                'integration' => 'api.int.example.com',
                'staging' => 'api.staging.example.com',
                default => 'api.example.com',
            };
            $this->port   = match ($env) {
                'dev'     => self::LOCALPORTAPI,
                default => 443,
            };
        }

        return $this;
    }

    public function withHost(string $host): self
    {
        $this->host = $host;
        return $this;
    }

    public function withQuery(array $query): self
    {
        $this->query       = $query;
        $this->queryString = $this->buildQueryString(false);
        return $this;
    }

    public function withQueryString(string $queryString): self
    {
        $this->queryString = $queryString;
        $this->query       = $this->buildQueryArray();
        return $this;
    }

    public function build(): string
    {
        $this->uri = Uri::createFromString(
            $this->scheme . self::PROTOCOL_SEPARATOR . $this->host . $this->path
        );
        $this->uri = $this->uri->withPort($this->port);
        $this->uri = $this->uri->withQuery($this->getQueryString());

        if (!empty($this->anchor)) {
            $this->uri = $this->uri->withFragment($this->anchor);
        }

        return $this->uri->__toString();
    }

    /**
     * @return string
     */
    public function getQueryString(): string
    {
        return $this->queryString;
    }

    public function buildQueryString(bool $prefixQuestionMark = true): string
    {
        $queryString = '';
        foreach ($this->query as $key => $value) {
            $queryString .= $key . '=' . $value . '&';
        }
        $queryString = $prefixQuestionMark
            ? '?' . $queryString
            : $queryString;
        return rtrim($queryString, '&');
    }

    public function buildQueryArray(): array
    {
        $queryString = trim($this->getQueryString(), '?');
        $query       = [];
        parse_str($queryString, $query);
        return $query;
    }
}
