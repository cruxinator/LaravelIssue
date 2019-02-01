<?php declare(strict_types=1);

use \Illuminate\Database\Schema\Blueprint;
use Cruxinator\OQGraphLaravel\OqNode;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateDatabase.
 */
class CreateDatabase extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('categories');

        Schema::create('categories', function (\Illuminate\Database\Schema\Blueprint $table): void {
            $table->bigIncrements('id')->unsigned();
            //$table->increments('id');
            $table->string('name');
            $table->softDeletes();
            $table->integer('parent_id')->nullable();
        });
        Schema::create('oq_categoriesLinks', function (Blueprint $table): void {
            $table->integer('origid')->unsigned();
            $table->integer('destid')->unsigned();
            $table->primary(['origid', 'destid']);
            $table->index('destid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('categories');
        OqNode::DropOqTable('oq_categories');
    }
}
