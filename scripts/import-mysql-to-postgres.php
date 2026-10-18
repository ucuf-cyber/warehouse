<?php

declare(strict_types=1);

/**
 * Copies durable application data from the local MySQL database to PostgreSQL.
 *
 * Usage:
 *   php scripts/import-mysql-to-postgres.php C:\path\to\vercel-production.env
 */

if ($argc !== 2) {
    fwrite(STDERR, "Usage: php scripts/import-mysql-to-postgres.php <target-env-file>\n");
    exit(1);
}

/** @return array<string, string> */
function readEnvFile(string $path): array
{
    $values = [];

    foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        if (preg_match('/^([A-Z_][A-Z0-9_]*)=(.*)$/', $line, $matches) !== 1) {
            continue;
        }

        $value = trim($matches[2]);
        if (strlen($value) >= 2 && (($value[0] === '"' && str_ends_with($value, '"')) || ($value[0] === "'" && str_ends_with($value, "'")))) {
            $value = substr($value, 1, -1);
        }

        $values[$matches[1]] = $value;
    }

    return $values;
}

function quoteIdentifier(string $identifier): string
{
    return '"'.str_replace('"', '""', $identifier).'"';
}

$sourceEnv = readEnvFile(__DIR__.'/../.env');
$targetEnv = readEnvFile($argv[1]);

foreach (['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'] as $key) {
    if (!array_key_exists($key, $sourceEnv)) {
        throw new RuntimeException("Missing {$key} in .env.");
    }
}

foreach (['PGHOST', 'PGDATABASE', 'PGUSER', 'PGPASSWORD'] as $key) {
    if (!array_key_exists($key, $targetEnv)) {
        throw new RuntimeException("Missing {$key} in target environment file.");
    }
}

$source = new PDO(
    "mysql:host={$sourceEnv['DB_HOST']};port={$sourceEnv['DB_PORT']};dbname={$sourceEnv['DB_DATABASE']};charset=utf8mb4",
    $sourceEnv['DB_USERNAME'],
    $sourceEnv['DB_PASSWORD'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

$target = new PDO(
    "pgsql:host={$targetEnv['PGHOST']};port=5432;dbname={$targetEnv['PGDATABASE']};sslmode=require",
    $targetEnv['PGUSER'],
    $targetEnv['PGPASSWORD'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

$excluded = ['migrations', 'cache', 'cache_locks', 'sessions', 'jobs', 'job_batches', 'failed_jobs'];
$tables = $source->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$tables = array_values(array_filter($tables, static fn (string $table): bool => !in_array($table, $excluded, true)));

$dependencyStatement = $source->prepare(
    'SELECT TABLE_NAME, REFERENCED_TABLE_NAME
     FROM information_schema.KEY_COLUMN_USAGE
     WHERE TABLE_SCHEMA = ? AND REFERENCED_TABLE_NAME IS NOT NULL'
);
$dependencyStatement->execute([$sourceEnv['DB_DATABASE']]);

$dependencies = array_fill_keys($tables, []);
foreach ($dependencyStatement as $relationship) {
    $table = $relationship['TABLE_NAME'];
    $reference = $relationship['REFERENCED_TABLE_NAME'];

    if (isset($dependencies[$table]) && isset($dependencies[$reference])) {
        $dependencies[$table][] = $reference;
    }
}

$pending = array_fill_keys($tables, true);
$orderedTables = [];
while ($pending !== []) {
    $ready = array_keys(array_filter($pending, static function (bool $_, string $table) use ($dependencies, $pending): bool {
        return array_intersect($dependencies[$table], array_keys($pending)) === [];
    }, ARRAY_FILTER_USE_BOTH));

    if ($ready === []) {
        // A cyclic relation is uncommon in this application. Preserve a stable
        // order so the database reports the exact relation if one exists.
        $ready = [array_key_first($pending)];
    }

    foreach ($ready as $table) {
        $orderedTables[] = $table;
        unset($pending[$table]);
    }
}

$columnTypes = $target->prepare(
    'SELECT column_name, data_type
     FROM information_schema.columns
     WHERE table_schema = current_schema() AND table_name = ?'
);

foreach ($orderedTables as $table) {
    $columnTypes->execute([$table]);
    $types = $columnTypes->fetchAll(PDO::FETCH_KEY_PAIR);
    if ($types === []) {
        throw new RuntimeException("Target table {$table} does not exist.");
    }

    $rows = $source->query('SELECT * FROM `'.str_replace('`', '``', $table).'`');
    $count = 0;
    $columns = null;
    $batch = [];

    $insertBatch = static function (array $batch) use ($target, $table, $types, &$columns): void {
        if ($batch === []) {
            return;
        }

        $columns ??= array_keys($batch[0]);
        $values = [];
        $placeholders = [];
        foreach ($batch as $row) {
            $rowPlaceholders = [];
            foreach ($columns as $column) {
                $value = $row[$column];
                if ($value !== null && ($types[$column] ?? null) === 'boolean') {
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? ((int) $value === 1);
                }
                $values[] = $value;
                $rowPlaceholders[] = '?';
            }
            $placeholders[] = '('.implode(', ', $rowPlaceholders).')';
        }

        $statement = $target->prepare(
            'INSERT INTO '.quoteIdentifier($table)
            .' ('.implode(', ', array_map('quoteIdentifier', $columns)).')'
            .' VALUES '.implode(', ', $placeholders)
        );
        $statement->execute($values);
    };

    $target->beginTransaction();
    try {
        while ($row = $rows->fetch()) {
            $batch[] = $row;
            if (count($batch) === 250) {
                $insertBatch($batch);
                $count += count($batch);
                $batch = [];
            }
        }

        $insertBatch($batch);
        $count += count($batch);

        $target->commit();
    } catch (Throwable $exception) {
        $target->rollBack();
        throw new RuntimeException("Failed importing {$table}: {$exception->getMessage()}", previous: $exception);
    }

    if (in_array($types['id'] ?? null, ['smallint', 'integer', 'bigint'], true)) {
        $target->query(
            "SELECT setval(pg_get_serial_sequence('".str_replace("'", "''", $table)."', 'id'), COALESCE((SELECT MAX(id) FROM ".quoteIdentifier($table)."), 1), true)"
        );
    }

    fwrite(STDOUT, sprintf("Imported %d rows from %s\n", $count, $table));
}

fwrite(STDOUT, "Data import completed.\n");
