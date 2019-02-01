<?php declare(strict_types=1);

namespace Cruxinator\OQGraphLaravel\Tests\Models;

use \Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    public $timestamps = false;

    protected $fillable = ['name', 'parent_id'];

    public static function resetActionsPerformed(): void
    {
        static::$actionsPerformed = 0;
    }
}
