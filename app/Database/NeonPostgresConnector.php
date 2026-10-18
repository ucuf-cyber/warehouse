<?php

namespace App\Database;

use Illuminate\Database\Connectors\PostgresConnector;

/**
 * Neon's pooled endpoint routes connections by TLS SNI. The libpq bundled
 * with the vercel-php runtime predates SNI support, so Neon rejects it with
 * "Endpoint ID is not specified" unless the endpoint id is passed explicitly
 * via the `options` DSN parameter (https://neon.tech/sni). Laravel's stock
 * PostgresConnector only forwards sslmode/sslcert/sslkey/sslrootcert into
 * the DSN, so a config-level `options` value (parsed from DATABASE_URL's
 * query string) is otherwise silently dropped — this adds it back.
 */
class NeonPostgresConnector extends PostgresConnector
{
    protected function addSslOptions($dsn, array $config)
    {
        $dsn = parent::addSslOptions($dsn, $config);

        if (! empty($config['endpoint'])) {
            $dsn .= ";options='endpoint={$config['endpoint']}'";
        }

        return $dsn;
    }
}
