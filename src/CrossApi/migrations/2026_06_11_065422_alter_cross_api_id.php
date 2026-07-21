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
        // 仅给 id 列补主键约束。用 Blueprint::primary() 让 Laravel 按当前驱动
        // (MySqlGrammar / PostgresGrammar) 生成对应 SQL，避免原生 SQL 跨库不兼容。
        // Laravel 8.83 的 Schema::Builder 没有 hasIndex，故用 try/catch 容忍主键已存在。
        try {
            Schema::table(\QscmfCrossApi\RegisterMethod::getTableName(), function (Blueprint $table) {
                $table->primary('id');
            });
        } catch (\Throwable $e) {
            // 主键已存在（MySQL: 1068 Duplicate key / PostgreSQL: 42P16 relation already exists）
            // 或重复执行该迁移时，安全忽略。
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // dropPrimary() 省略参数：MySQL 生成 DROP PRIMARY KEY（主键恒名 PRIMARY）；
        // PostgreSQL 生成 DROP CONSTRAINT {table}_pkey，正好匹配 up 时 PG 自动命名的约束，
        // 故无需显式命名——显式命名反而会导致 PG 回滚时找不到约束。
        Schema::table(\QscmfCrossApi\RegisterMethod::getTableName(), function (Blueprint $table) {
            $table->dropPrimary();
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
