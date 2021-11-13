<?php

declare(strict_types=1);

namespace Cadfael\Engine\Check\Database;

use Cadfael\Engine\Check;
use Cadfael\Engine\Entity\Database;
use Cadfael\Engine\Exception\MySQL\UnknownVersion;
use Cadfael\Engine\Report;

/**
 * Class RequirePrimaryKey
 * @package Cadfael\Engine\Check\Schema
 */
class RequirePrimaryKey implements Check
{
    public function supports($entity): bool
    {
        return $entity instanceof Database;
    }

    public function run($entity): ?Report
    {
        try {
            $version = $entity->getVersion();
        } catch (UnknownVersion $e) {
            return new Report(
                $this,
                $entity,
                Report::STATUS_CONCERN,
                [
                    $e->getMessage(),
                    "This makes us nervous."
                ]
            );
        }

        $variables = $entity->getVariables();
        $opt_name = 'sql_require_primary_key';
        $require_pk_disabled = (!isset($variables[$opt_name]) || $variables[$opt_name] !== 'ON');
        if (version_compare($entity->getVersion(), '8.0.13', '>=') && $require_pk_disabled) {
            return new Report(
                $this,
                $entity,
                Report::STATUS_WARNING,
                [
                   "You are running MySQL 8.0.13+ (MySQL " . $entity->getVersion() . ")"
                   .  " without sql_require_primary_key enabled.",
                    "Every table should have a primary key, so it's better to enforce it via configuration.",
                ]
            );
        }

        return new Report(
            $this,
            $entity,
            Report::STATUS_OK,
            [ "You have sql_require_primary_key enabled." ]
        );
    }

    /**
     * @codeCoverageIgnore
     */
    public function getReferenceUri(): string
    {
        return 'https://github.com/xsist10/cadfael/wiki/Force-Primary-Key-Requirement';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getName(): string
    {
        return 'Require Primary Key Configuration';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDescription(): string
    {
        return 'Ensure MySQL is configured to block the creation of tables without PRIMARY KEYs.';
    }
}
