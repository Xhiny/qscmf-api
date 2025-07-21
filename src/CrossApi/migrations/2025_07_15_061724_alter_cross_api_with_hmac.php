<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCrossApiWithHmac extends Migration
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
        //
        Schema::table(\QscmfCrossApi\RegisterMethod::getTableName(), function (Blueprint $table) {
            $table->tinyInteger('status')->default(1)->comment('状态， 0禁用 1启用');
            $table->string('secret_key', 255)->default('')->comment('加密后的密钥');
            $table->timestamp('update_date', 4)->default(DB::raw('CURRENT_TIMESTAMP(4) ON UPDATE CURRENT_TIMESTAMP(4)'));
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table(\QscmfCrossApi\RegisterMethod::getTableName(), function (Blueprint $table) {
            $table->dropColumn(['status', 'secret_key', 'update_date']);
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
