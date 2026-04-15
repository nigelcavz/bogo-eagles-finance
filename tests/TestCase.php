<?php

namespace Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected string $compiledViewPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->compiledViewPath = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR . 'bogo-eagles-finance-testing'
            . DIRECTORY_SEPARATOR . 'views'
            . DIRECTORY_SEPARATOR . str_replace('.', '_', uniqid('phpunit_', true));

        File::ensureDirectoryExists($this->compiledViewPath);

        $this->app['config']->set('view.compiled', $this->compiledViewPath);
    }
}
