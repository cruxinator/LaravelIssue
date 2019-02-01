<?php declare(strict_types=1);

namespace Cruxinator\OQGraphLaravel\Tests;

use Cruxinator\OQGraphLaravel\Tests\Models\Category;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected $rawData = ['categorys' => [
        ['id' => 1, 'name' => 'store', '_lft' => 1, '_rgt' => 20, 'parent_id' => null],
        ['id' => 2, 'name' => 'notebooks', '_lft' => 2, '_rgt' => 7, 'parent_id' => 1],
        ['id' => 3, 'name' => 'apple', '_lft' => 3, '_rgt' => 4, 'parent_id' => 2],
        ['id' => 4, 'name' => 'lenovo', '_lft' => 5, '_rgt' => 6, 'parent_id' => 2],
        ['id' => 5, 'name' => 'mobile', '_lft' => 8, '_rgt' => 19, 'parent_id' => 1],
        ['id' => 6, 'name' => 'nokia', '_lft' => 9, '_rgt' => 10, 'parent_id' => 5],
        ['id' => 7, 'name' => 'samsung', '_lft' => 11, '_rgt' => 14, 'parent_id' => 5],
        ['id' => 8, 'name' => 'galaxy', '_lft' => 12, '_rgt' => 13, 'parent_id' => 7],
        ['id' => 9, 'name' => 'sony', '_lft' => 15, '_rgt' => 16, 'parent_id' => 5],
        ['id' => 10, 'name' => 'lenovo', '_lft' => 17, '_rgt' => 18, 'parent_id' => 5],
        ['id' => 11, 'name' => 'store_2', '_lft' => 21, '_rgt' => 22, 'parent_id' => null],
    ]];

    protected function setUp(): void
    {
        parent::setUp();

        foreach ($this->rawData['categorys'] as $category) {
            Category::create(['id' => $category['id'], 'name' => $category['name'], 'parent_id' => $category['parent_id']]);
            if (null !== $category['parent_id']) {
                $insertQuery = "INSERT into oq_categoriesLinks (origid,destid) VALUES  ('" . $category['parent_id'] . "', '" . $category['id'] . "')";
                DB::statement($insertQuery);
            }
        }
    }

    public function findCategory($name, $withTrashed = false): Category
    {
        $q = new Category();
        $q = $withTrashed ? $q->withTrashed() : $q->newQuery();
        return $q->whereName($name)->first();
    }

    protected function startListening(): void
    {
        DB::enableQueryLog();
    }

    protected function fetchQuery()
    {
        $log = DB::getQueryLog();

        return end($log)['query'];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app->useEnvironmentPath(getcwd());
        $app->bootstrapWith([LoadEnvironmentVariables::class]);

        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => env('MARIADB_HOST', '127.0.0.1'),
            'port' => env('MARIADB_PORT', 3306),
            'database' => env('MARIADB_DATABASE', 'forge'),
            'username' => env('MARIADB_USERNAME', 'forge'),
            'password' => env('MARIADB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'strict' => false,
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    protected function assertQueryMatches($expected, $actual): void
    {
        $actual = preg_replace('/\s\s+/', ' ', $actual);
        $actual = str_replace(['\n', '\r'], '', $actual);
        $actual = trim($actual);

        $expected = preg_replace('/\s\s+/', ' ', $expected);
        $expected = str_replace(['\n', '\r'], '', $expected);
        $expected = trim($expected);
        $expected = '/' . $expected . '/';
        $expected = preg_quote($expected);

        if ('mysql' === $_ENV['type']) {
            $expected = str_replace(['"'], '`', $expected);
        }
        $this->assertRegExp($expected, $actual);
    }
}
