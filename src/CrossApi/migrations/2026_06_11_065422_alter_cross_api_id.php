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
        $isPrimaryKey = DB::select(
            "SHOW KEYS FROM `{$table}` WHERE Key_name = 'PRIMARY' AND Column_name = 'id'"
        );
        if(empty($isPrimaryKey)){
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
