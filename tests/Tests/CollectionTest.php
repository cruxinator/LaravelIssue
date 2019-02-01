<?php declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: alex
 * Date: 30/01/19
 * Time: 12:48 AM
 */

namespace Cruxinator\OQGraphLaravel\Tests\Tests;

use Cruxinator\OQGraphLaravel\Tests\Models\Category;
use Cruxinator\OQGraphLaravel\Tests\TestCase;

class CollectionTest extends TestCase
{
    public function testIssueFetching(): void
    {
        $this->addWithoutRootMacro();
        $this->setupGlobalScopes();
        $dbQuery = "select *, (select GROUP_CONCAT(_p.origid) from `oq_categoriesLinks` as `_p` where `categories`.`deleted_at` is null and `_p`.`destid` = `categories`.`id` group by `_p`.`destid`) as `oqLinksParentIds`, (select GROUP_CONCAT(_c.destid) from `oq_categoriesLinks` as `_c` where `categories`.`deleted_at` is null and `_c`.`origid` = `categories`.`id` group by `_c`.`origid`) as `oqLinksChildIds` from `categories` where `categories`.`deleted_at` is null having `oqLinksParentIds` != ''";
        $data = \DB::select(\DB::raw($dbQuery));
        $this->assertSame(9, count($data));
        $this->startListening();
        $catWithoutRoot = Category::withoutRoot()->get();
        // Trim the '' off the end of the query and replace with a ? place holder
        $this->assertQueryMatches(substr($dbQuery, 0, -2) . '?', $this->fetchQuery());
        // how could this return different data?
        $this->assertSame(9, count($catWithoutRoot));

        $tree = $catWithoutRoot->toTree();
        $this->assertSame(2, count($tree));
    }

    private function addWithoutRootMacro(): void
    {
        \Illuminate\Database\Eloquent\Builder::macro('withoutRoot', function () {
            $this->query->having('oqLinksParentIds', '!=', null);
            return $this;
        });
    }

    private function setupGlobalScopes(): void
    {
        $newQuery = Category::query();
        Category::addGlobalScope('parentids', function ($builder) use ($newQuery): void {
            $alias = '_d';
            if (null === $builder->getQuery()->columns) {
                $builder->getQuery()->columns = ['*'];
            }
            $newQuery = $newQuery->toBase()
                ->selectRaw('GROUP_CONCAT(_p.origid)')
                ->from('oq_categoriesLinks as _p')
                ->whereColumn('_p.destid', '=', 'categories.id')
                ->groupBy('_p.destid');
            $builder->selectSub($newQuery, 'oqLinksParentIds');
        });
        Category::addGlobalScope('childids', function ($builder) use ($newQuery): void {
            if (null === $builder->getQuery()->columns) {
                $builder->getQuery()->columns = ['*'];
            }
            $newQuery = $newQuery->toBase()
                ->selectRaw('GROUP_CONCAT(_c.destid)')
                ->from('oq_categoriesLinks as _c')
                ->whereColumn('_c.origid', '=', 'categories.id')
                ->groupBy('_c.origid');
            $builder->selectSub($newQuery, 'oqLinksChildIds');
        });
    }
}
