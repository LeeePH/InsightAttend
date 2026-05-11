<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReplaceMaintenanceFormsWithCoursesTable extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('maintenance_form_fields')) {
            DB::table('maintenance_form_fields')->delete();
            Schema::drop('maintenance_form_fields');
        }

        if (Schema::hasTable('maintenance_form_templates')) {
            DB::table('maintenance_form_templates')->delete();
            Schema::drop('maintenance_form_templates');
        }

        Schema::enableForeignKeyConstraints();

        if (!Schema::hasTable('courses')) {
            Schema::create('courses', function (Blueprint $table) {
                $table->id();
                $table->string('code', 30)->unique();
                $table->string('name', 150);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('courses');

        if (!Schema::hasTable('maintenance_form_templates')) {
            Schema::create('maintenance_form_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('description')->nullable();
                $table->json('ui_settings')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('maintenance_form_fields')) {
            Schema::create('maintenance_form_fields', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('maintenance_form_template_id');
                $table->string('label');
                $table->string('field_key');
                $table->string('field_type');
                $table->text('field_options')->nullable();
                $table->string('placeholder')->nullable();
                $table->text('help_text')->nullable();
                $table->string('column_class')->nullable();
                $table->string('validation_rules')->nullable();
                $table->boolean('is_required')->default(false);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }
}
