<?php

declare(strict_types=1);

namespace Cadfael\Engine\Entity;

use Cadfael\Engine\Entity;
use Doctrine\DBAL\Connection;

class Database implements Entity
{
    /**
     * @var Schema[]
     */
    private array $schemas;

    /**
     * @var string[]
     */
    private array $variables;

    /**
     * @var string[]
     */
    private array $status;

    /**
     * @var array<Account>
     */
    protected array $accounts;

    private Connection $connection;

    public function __construct(?Connection $connection)
    {
        if ($connection) {
            $this->setConnection($connection);
        }
    }

    public function getName(): string
    {
        return $this->getConnection()->getHost() . ':' . $this->getConnection()->getPort();
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function setConnection(Connection $connection): void
    {
        $this->connection = $connection;
    }

    /**
     * @return array<Account>
     */
    public function getAccounts(): array
    {
        return $this->accounts;
    }

    public function getAccount(string $username, string $host): ?Account
    {
        $accounts = array_filter($this->accounts, function ($account) use ($username, $host) {
            return $account->getName() === $username
                && $account->getHost() === $host;
        });

        if (count($accounts)) {
            return $accounts[0];
        }

        return null;
    }

    /**
     * @param Account ...$accounts
     */
    public function setAccounts(Account...$accounts): void
    {
        $this->accounts = $accounts;
    }

    /**
     * @param Account $account
     */
    public function addAccount(Account $account): void
    {
        $this->accounts[] = $account;
    }

    /**
     * @return string[]
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @param string[] $variables
     */
    public function setVariables(array $variables): void
    {
        $this->variables = $variables;
    }

    /**
     * @return string[]
     */
    public function getStatus(): array
    {
        return $this->status;
    }

    public function hasPerformanceSchema(): bool
    {
        // If we have a performance_schema = "ON" | "OFF" flag, use that
        if (!empty($this->variables['performance_schema'])) {
            return $this->variables['performance_schema'] === 'ON';
        }

        // Otherwise check to see if we have any keys in the variables that begin with performance_schema_*
        return count(array_filter(array_keys($this->variables), function ($key) {
            return strpos(strtolower($key), 'performance_schema_') === 0;
        })) > 0;
    }

    /**
     * @param string[] $status
     */
    public function setStatus(array $status): void
    {
        $this->status = $status;
    }

    /**
     * @return Schema[]
     */
    public function getSchemas(): array
    {
        return $this->schemas;
    }

    /**
     * @param Schema[] $schemas
     */
    public function setSchemas(array $schemas): void
    {
        array_walk($schemas, function (Schema $schema) {
            $schema->setDatabase($this);
        });
        $this->schemas = $schemas;
    }

    public function getVersion(): string
    {
        return $this->variables['version'];
    }


    public function isVirtual(): bool
    {
        return false;
    }
}
