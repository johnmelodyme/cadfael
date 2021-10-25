<?php

declare(strict_types=1);

namespace Cadfael\Engine\Check\Table;

use Cadfael\Engine\Check;
use Cadfael\Engine\Entity\Column;
use Cadfael\Engine\Entity\Index;
use Cadfael\Engine\Entity\Table;
use Cadfael\Engine\Report;

class SaneInnoDbPrimaryKey implements Check
{

    public function supports($entity): bool
    {
        return $entity instanceof Table
            && !is_null($entity->information_schema)
            && $entity->information_schema->engine === 'InnoDB';
    }

    public function run($entity): ?Report
    {
        $primary_keys = $entity->getPrimaryKeys();
        // If the table doesn't have a PRIMARY KEY, we skip this test
        if (!count($primary_keys)) {
            return null;
        }

        // Find out the size of our primary key (bytes)
        $primary_key_size = array_reduce($primary_keys, function (?float $size, Column $column): float {
            return ($size ?? 0) + $column->getStorageByteSize();
        });

        // Get a count of all the indexes that are not the PRIMARY KEY
        $index_count = count(array_filter($entity->getIndexes(), function (Index $index) {
            return $index->getName() !== "PRIMARY KEY";
        }));

        // If our primary key is a sane size, or if we don't have any other indexes, we can exit now
        if ($primary_key_size <= 8 || $index_count == 0) {
            return new Report(
                $this,
                $entity,
                Report::STATUS_OK
            );
        }

        // If these is, lets work out if it's more space efficient to create a UNIQUE
        // KEY of the PRIMARY KEY and add an AUTO_INCREMENT field (4 bytes) instead.
        $current_cost = $primary_key_size + ($index_count * $primary_key_size);
        $unique_key_cost = 4 + ($primary_key_size + 4) + ($index_count * 4);


        // If the current cost is less than a unique constraint, then it's OK
        if ($current_cost <= $unique_key_cost) {
            return new Report(
                $this,
                $entity,
                Report::STATUS_OK
            );
        }

        return new Report(
            $this,
            $entity,
            Report::STATUS_WARNING,
            [
                "In InnoDB tables, the PRIMARY KEY is appended to other indexes.",
                "If the PRIMARY KEY is big, other indexes will use more space.",
                "Maybe turn your PRIMARY KEY into UNIQUE and add an auto_increment PRIMARY KEY."
            ]
        );
    }

    public function getReferenceUri(): string
    {
        return 'https://github.com/xsist10/cadfael/wiki/Sane-InnoDb-Primary-Key';
    }

    public function getName(): string
    {
        return 'Sane InnoDB PRIMARY KEY';
    }

    public function getDescription(): string
    {
        return "Due to how InnoDB uses PRIMARY KEYS, certain types or combinations should be avoided.";
    }
}
