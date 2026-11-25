<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dict_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('字典名称');
            $table->string('code', 100)->unique()->comment('字典编码');
            $table->tinyInteger('status')->default(1)->comment('状态：1启用 0停用');
            $table->string('remark', 255)->nullable()->comment('备注');
            $table->timestamps();
        });

        Schema::create('dict_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dict_type_id')->constrained('dict_types')->cascadeOnDelete();
            $table->string('label', 100)->comment('字典标签');
            $table->string('value', 100)->comment('字典键值');
            $table->string('color', 32)->default('teal')->comment('标签色调');
            $table->integer('sort')->default(0)->comment('排序（升序）');
            $table->tinyInteger('status')->default(1)->comment('状态：1启用 0停用');
            $table->string('remark', 255)->nullable()->comment('备注');
            $table->timestamps();

            $table->unique(['dict_type_id', 'value']);
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50)->default('general')->index()->comment('配置分组');
            $table->string('key', 100)->unique()->comment('配置键');
            $table->text('value')->nullable()->comment('配置值');
            $table->string('label', 100)->nullable()->comment('配置说明');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dict_items');
        Schema::dropIfExists('dict_types');
        Schema::dropIfExists('system_settings');
    }
};
