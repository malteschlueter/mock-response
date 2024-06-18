<?php

declare(strict_types=1);

namespace Deployer;

use Deployer\Exception\ConfigurationException;
use Deployer\Exception\Exception;

require \dirname(__DIR__) . '/vendor/autoload.php';
require 'recipe/symfony.php';

set('allow_anonymous_stats', false);

// Config

set('repository', 'git@github.com-auth2:leankoala-gmbh/Auth2.git');

// Environment vars
set('env', [
    'APP_ENV' => 'prod',
    'COMPOSER_ALLOW_SUPERUSER' => 1,
]);

set('writable_mode', 'chown');
set('writable_recursive', true);

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts

// <-- Staging
host('auth.stage.koalityengine.com(old)')
    ->setLabels([
        'environment' => 'staging',
    ])
    ->setHostname('162.55.54.216')
    ->setRemoteUser('root')
    ->setDeployPath('/var/www/auth.stage.koalityengine.com')
    ->set('http_user', 'www-data')
    ->set('branch', 'develop')
;

host('auth1.stage.koalityengine.com')
    ->setLabels([
        'environment' => 'staging',
        'allow-database-migration' => 'allow-database-migration',
    ])
    ->setHostname('116.203.143.233')
    ->setRemoteUser('root')
    ->setDeployPath('/var/www/auth.stage.koalityengine.com')
    ->set('http_user', 'www-data')
    ->set('branch', 'develop')
;

host('auth2.stage.koalityengine.com')
    ->setLabels([
        'environment' => 'staging',
    ])
    ->setHostname('128.140.108.62')
    ->setRemoteUser('root')
    ->setDeployPath('/var/www/auth.stage.koalityengine.com')
    ->set('http_user', 'www-data')
    ->set('branch', 'develop')
;
// --> Staging

// <-- Branch
host('%branch%.auth.branches.koalityengine.com')
    ->setLabels(['environment' => 'branch'])
    ->setRemoteUser('root')
    ->setDeployPath('/var/www/auth.branches.koalityengine.com/%branch%.auth.branches.koalityengine.com')
    ->set('path_to_defaults', '/var/www/auth.branches.koalityengine.com/_defaults')
    ->set('http_user', 'www-data')
    ->setHostname('deploy.auth.branches.koalityengine.com')
;
// --> Branch

// <-- Production
host('auth.koalityengine.com(old)')
    ->setLabels([
        'environment' => 'production',
    ])
    ->setHostname('116.203.154.116')
    ->setRemoteUser('root')
    ->setDeployPath('/var/www/auth.koalityengine.com')
    ->set('http_user', 'www-data')
;

host('auth1.koalityengine.com')
    ->setLabels([
        'environment' => 'production',
        'allow-database-migration' => 'allow-database-migration',
    ])
    ->setHostname('49.12.228.242')
    ->setRemoteUser('root')
    ->setDeployPath('/var/www/auth.koalityengine.com')
    ->set('http_user', 'www-data')
;
host('auth2.koalityengine.com')
    ->setLabels([
        'environment' => 'production',
    ])
    ->setHostname('159.69.18.154')
    ->setRemoteUser('root')
    ->setDeployPath('/var/www/auth.koalityengine.com')
    ->set('http_user', 'www-data')
;
// --> Production

// Variables
set('pre_release_base_uri_with_prefix', function () {
    $preReleaseBaseUri = get('pre_release_base_uri');

    if ($preReleaseBaseUri === null) {
        throw new ConfigurationException('The configuration "pre_release_base_uri" is required');
    }

    $preReleasePrefixPath = get('pre_release_prefix_path');
    if ($preReleasePrefixPath !== null) {
        $preReleaseBaseUri .= '/' . $preReleasePrefixPath;
    }

    return $preReleaseBaseUri;
});

set('keep_releases', function () {
    $environment = get('labels')['environment'] ?? null;

    switch ($environment) {
        case 'staging':
            return 5;
        case 'production':
            return 10;
        default:
            return 1;
    }
});

// Tasks

// If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Need to ensure directories like "var/cache" are writeable for HTTP User
before('deploy:symlink', 'deploy:writable');

task('deploy:symlink')->limit(1);

desc('Checks if target to deploy is allowed for host/environment');
task('koality:deploy:environment:check', function (): void {
    $environment = get('labels')['environment'] ?? null;

    if ($environment === null) {
        throw new ConfigurationException('The host configuration requires the label "environment"');
    }

    if (
        $environment === 'staging'
        && (
            (input()->hasOption('branch') && !empty(input()->getOption('branch')))
            || (input()->hasOption('tag') && !empty(input()->getOption('tag')))
        )
    ) {
        throw new ConfigurationException('It is not allowed to set a custom target for environment "' . $environment . '". Only default configuration is allowed.');
    }

    if (
        $environment === 'production'
        && (
            (input()->hasOption('branch') && !empty(input()->getOption('branch')))
            || !input()->hasOption('tag')
            || empty(input()->getOption('tag'))
        )
    ) {
        if (input()->hasOption('branch') && !empty(input()->getOption('branch'))) {
            throw new ConfigurationException('It is not allowed to set a branch for environment "' . $environment . '". Only release tags are allowed.');
        }

        if (!input()->hasOption('tag') || empty(input()->getOption('tag'))) {
            throw new ConfigurationException('The deployment for environment "' . $environment . '" requires a release tag to deploy.');
        }
    }

    if ($environment === 'branch') {
        if (!input()->hasOption('branch') || empty(input()->getOption('branch'))) {
            throw new ConfigurationException('The deployment for environment "' . $environment . '" requires a branch to deploy.');
        }

        if (input()->hasOption('tag') && !empty(input()->getOption('tag'))) {
            throw new ConfigurationException('It is not allowed to set a tag for environment "' . $environment . '". Only branches are allowed.');
        }
    }

    $preReleaseBaseUri = get('pre_release_base_uri');

    if ($preReleaseBaseUri !== null && str_ends_with($preReleaseBaseUri, '/')) {
        throw new ConfigurationException('Please remove suffixed "/" from "pre_release_base_uri"');
    }

    $preReleasePrefixPath = get('pre_release_prefix_path');
    if ($preReleasePrefixPath !== null) {
        if (str_starts_with($preReleasePrefixPath, '/')) {
            throw new ConfigurationException('Please remove prefixed "/" from "pre_release_prefix_path"');
        }

        if (str_ends_with($preReleasePrefixPath, '/')) {
            throw new ConfigurationException('Please remove suffixed "/" from "pre_release_prefix_path"');
        }
    }
});
before('deploy', 'koality:deploy:branch:prepare'); // Order is correct. Will be executed after "koality:deploy:environment:check"
before('deploy', 'koality:deploy:environment:check'); // Order is correct. Will be executed before "koality:deploy:branch:prepare"

task('deploy:info', function (): void {
    info('Deployer-Release: <fg=cyan>{{release_name}}</fg=cyan>');
    info('Host: <fg=cyan>{{hostname}}</fg=cyan>');
    info('Deploy path: <fg=cyan>{{deploy_path}}</fg=cyan>');

    $environment = get('labels')['environment'] ?? null;
    info(sprintf('Environment: <fg=cyan>%s</fg=cyan>', $environment));

    $fetchConfigurationSource = static function () {
        $configurationSource = '';

        if (!empty(get('branch')) || (input()->hasOption('branch') && !empty(input()->getOption('branch')))) {
            $configurationSource = 'Branch';
        }

        if (input()->hasOption('tag') && !empty(input()->getOption('tag'))) {
            $configurationSource = 'Tag';
        }

        return $configurationSource;
    };
    info(sprintf('%s: <fg=cyan>{{target}}</fg=cyan>', $fetchConfigurationSource()));

    output()->writeln('::notice title=Deploy to "' . $environment . '"::Host: ' . parse('{{hostname}}'));
});

desc('Truncate .env file to be sure no dev environment variables are set');
task('koality:deploy:replace-dot-env-file', function (): void {
    $environment = get('labels')['environment'] ?? null;

    if ($environment === 'production') {
        $release = get('target');
    } else {
        $release = get('release_revision');
    }

    $environmentVariables = [
        'SENTRY_RELEASE=' . $release,
        'SENTRY_SERVER_NAME=' . currentHost()->getHostname(),
        'DEPLOYMENT_ENV=' . $environment,
    ];

    cd('{{release_path}}');
    run('echo "' . implode("\n", $environmentVariables) . '" > .env');
});
after('deploy:update_code', 'koality:deploy:replace-dot-env-file');

desc('Compiles .env files to .env.local.php for improved performance');
task('koality:composer:dump-env-to-php-file', function (): void {
    cd('{{release_path}}');

    run('{{bin/composer}} dump-env prod');
});
after('deploy:vendors', 'koality:composer:dump-env-to-php-file');

require __DIR__ . '/deployer/test.php';

desc('Executes (or dumps) the SQL needed to update the database schema to match the current mapping metadata');
task('koality:doctrine:schema:update', function (): void {
    $environment = get('labels')['environment'] ?? null;

    $sqlDump = run('{{bin/console}} doctrine:schema:update --dump-sql --complete --no-ansi');

    if ($sqlDump === '') {
        return;
    }

    $sqlHash = sha1($sqlDump);

    $inputSqlHash = $_SERVER['KOALITY_SQL_HASH'] ?? null;

    if ($sqlHash === $inputSqlHash || \in_array($environment, ['staging', 'branch'], true)) {
        run('{{bin/console}} doctrine:schema:update --force --complete --no-ansi');

        return;
    }

    $moreInformation = 'See for further information https://github.com/leankoala-gmbh/KoalityEngineApp/blob/HEAD/docs/deployment.md#doctrineschemaupdate';

    $messageAboutSqlDump = implode("\n", [
        sprintf('The database is out of sync. Check following SQL statements and re-run manual deploy with hash: %s', $sqlHash),
        $sqlDump,
        $moreInformation,
    ]);

    output()->writeln(sprintf('::error title=doctrine:schema:update::%s', $messageAboutSqlDump));
    output()->writeln(sprintf('::error title=doctrine:schema:update::%s', $moreInformation));

    throw new Exception($messageAboutSqlDump);
})->select('allow-database-migration=allow-database-migration,environment=branch'); // Needs label allow-database-migration or environment "branch" to execute
after('koality:test', 'koality:doctrine:schema:update');

require __DIR__ . '/deployer/deploy-branch.php';
require __DIR__ . '/deployer/shutdown.php';
