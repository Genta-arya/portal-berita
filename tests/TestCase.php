<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        $driver = $app['config']->get('database.default');
        $database = $app['config']->get('database.connections.'.$driver.'.database');
        if (! (($driver === 'sqlite' && $database === ':memory:') || ($driver === 'mysql' && str_ends_with((string) $database, '_test')))) {
            throw new \RuntimeException('Pengujian hanya boleh menggunakan SQLite :memory: atau database MySQL berakhiran _test.');
        }

        return $app;
    }
}
