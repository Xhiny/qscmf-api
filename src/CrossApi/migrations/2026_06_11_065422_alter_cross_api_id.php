<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCrossApiId extends Migration
{

    public function beforeCmmUp()
    {
        //
    }

    public function beforeCmmDown()
    {
        //
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = \QscmfCrossApi\RegisterMethod::getTableName();
        $isPrimaryKey = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes($table);
        $columns = $isPrimaryKey['primary']->getColumns();
        if(!in_array('id', $columns)){
            Schema::table($table, function (Blueprint $table) {
                $table->string('id', 50)->primary()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(\QscmfCrossApi\RegisterMethod::getTableName(), function (Blueprint $table) {
            $table->string('id', 50)->change();
        });
    }

    public function afterCmmUp()
    {
        //
    }

    public function afterCmmDown()
    {
        //
    }
}
