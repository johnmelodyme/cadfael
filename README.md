# Cadfael

![Build](https://github.com/xsist10/cadfael/workflows/build/badge.svg?branch=master)

Cadfael is static analysis tool to provide critiquing for MySQL databases.

## Documentation

All of the checks and the reasoning, considerations and remediations for them are documented in the [wiki](https://github.com/xsist10/cadfael/wiki).

## Installation

There are a couple options for installation depending on your personal preference.

### Phar

You can grab the phar file from the [most recent release](https://github.com/xsist10/cadfael/releases).
This ensures you won't have any dependency conflicts.

### Global

If you'd like Cadfael available anywhere on your system, you can install it globally.

```bash
composer global require cadfael/cadfael
```

Ensure that your global composer vendor bin folder is set in your path. You may need to add this to your `.bashrc` file.

```bash
export PATH=$PATH:~/.config/composer/vendor/bin
```

### Local

If you want to use it within a specific project you can install it with:

```bash
composer require cadfael/cadfael
```

The path to the executable will be in `./vendor/bin/`.

## Usage

```bash
cadfael run --host 127.0.0.1 --username root --port 3306 [database_to_scan]
```

### Advanced Usage

You can also include the `--performance_schema` flag if you wish to run checks against the [performance_schema](https://dev.mysql.com/doc/refman/8.0/en/performance-schema.html) schema which collect analytics about your server since the last time it was restarted. This is particularly useful if you want to see how your database is being used and detect issues related to queries, access of tables and heavy or badly optimized queries.

For meaningful results you *should* run this against the database that is being used in production otherwise you'll only be checking against the metrics collected in your development environment.
**BUT FIRST** always speak to your DBA/Security people first before run random tools from the internet against your production database.

### Output
```
Cadfael CLI Tool

Host: localhost:3306
User: [username]

What is the database password? 

MySQL Version: 8.0.27-0ubuntu0.21.10.1
Uptime: 3.6 days

Attempting to scan schema test
Tables Found: 6

.w..w.....w.....w.....w.....o............wo.......o........w
o.....wo.....w.o.o...o..

Checks passed: 67/84

> Empty table

Description: Empty tables add unnecessary cognitive load similar to dead code.
Reference: https://github.com/xsist10/cadfael/wiki/Empty-Table

+-----------------------------+---------+-----------------------------------------------------------------+
| Entity                      | Status  | Message                                                         |
+-----------------------------+---------+-----------------------------------------------------------------+
| table_empty                 | Warning | Table contains no records.                                      |
| table_with_index_prefix     | Warning | Table contains no records.                                      |
| table_with_large_text_index | Warning | Table contains no records.                                      |
| table_without_index_prefix  | Warning | Table contains no records.                                      |
| user                        | Concern | Table is empty but has allocated free space.                    |
|                             |         | This table is in a shared tablespace so this doesn't mean much. |
+-----------------------------+---------+-----------------------------------------------------------------+

> Index Prefix

Description: High cardinality indexes with text columns should consider using prefixes.
Reference: https://github.com/xsist10/cadfael/wiki/Index-Prefix

+-------------+---------+-----------------------------------------------------------------------------------------------------+
| Entity      | Status  | Message                                                                                             |
+-------------+---------+-----------------------------------------------------------------------------------------------------+
| users.email | Concern | Column `email` (length 255) has no index prefix and a cardinality ratio of 1.                       |
|             |         | Since the column has high cardinality, it's recommended that you limit the index by using a prefix. |
|             |         | This will reduce disk space usage and insert/update performance on this table.                      |
+-------------+---------+-----------------------------------------------------------------------------------------------------+

> Require Primary Key Configuration

Description: Ensure MySQL is configured to block the creation of tables without PRIMARY KEYs.
Reference: https://github.com/xsist10/cadfael/wiki/Force-Primary-Key-Requirement

+----------------+---------+--------------------------------------------------------------------------------------------------------+
| Entity         | Status  | Message                                                                                                |
+----------------+---------+--------------------------------------------------------------------------------------------------------+
| localhost:3306 | Warning | You are running MySQL 8.0.13+ (MySQL 8.0.27-0ubuntu0.21.10.1) without sql_require_primary_key enabled. |
|                |         | Every table should have a primary key, so it's better to enforce it via configuration.                 |
+----------------+---------+--------------------------------------------------------------------------------------------------------+

> Reserved Keywords

Description: Identifies all columns whose names match reserved keywords.
Reference: https://dev.mysql.com/doc/refman/8.0/en/keywords.html

+----------------------------------+---------+------------------------------------------------+
| Entity                           | Status  | Message                                        |
+----------------------------------+---------+------------------------------------------------+
| table_with_index_prefix.name     | Concern | `name` is a reserved keyword in MySQL 8.0.     |
| table_with_large_text_index.name | Concern | `name` is a reserved keyword in MySQL 8.0.     |
| table_without_index_prefix.name  | Concern | `name` is a reserved keyword in MySQL 8.0.     |
| user.name                        | Concern | `name` is a reserved keyword in MySQL 8.0.     |
| users.name                       | Concern | `name` is a reserved keyword in MySQL 8.0.     |
| users.password                   | Concern | `password` is a reserved keyword in MySQL 8.0. |
+----------------------------------+---------+------------------------------------------------+

> Sane AUTO_INCREMENT definition

Description: AUTO_INCREMENT definitions should follow some basic guidelines.
Reference: https://github.com/xsist10/cadfael/wiki/Sane-Auto-Increment

+-------------------------------+---------+------------------------------------------------+
| Entity                        | Status  | Message                                        |
+-------------------------------+---------+------------------------------------------------+
| table_with_index_prefix.id    | Warning | This field should be an unsigned integer type. |
| table_without_index_prefix.id | Warning | This field should be an unsigned integer type. |
| user.id                       | Warning | This field should be an unsigned integer type. |
| users.id                      | Warning | This field should be an unsigned integer type. |
+-------------------------------+---------+------------------------------------------------+

```

## Take it for a spin

Try testing Cadfael on a few open data sources

```
+-----------------------------+------+-----------+----------+--------------------------+
| Host                        | Port | Username  | Password | Schema                   |
+-----------------------------+------+-----------+----------+--------------------------+
| ensembldb.ensembl.org       | 5306 | anonymous |          | homo_sapiens_core_103_38 |
| mysql-rfam-public.ebi.ac.uk | 4497 | rfamro    |          | Rfam                     |
| mysql-db.1000genomes.org    | 4272 | anonymous |          | homo_sapiens_core_73_37  |
+-----------------------------+------+-----------+----------+--------------------------+
```

## Contributions

This project adopts the [Contributor Covenant Code of Conduct](CODE_OF_CONDUCT.md) for contributions.

Feel free to open an issue if you find any problems or have any suggestions or requests.
