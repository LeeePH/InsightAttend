<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaintenanceFormFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('maintenance_form_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('maintenance_form_template_id');
            $table->string('label');
            $table->string('field_key');
            $table->string('field_type');
            $table->text('field_options')->nullable();
            $table->string('validation_rules')->nullable();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('maintenance_form_template_id')
                ->references('id')
                ->on('maintenance_form_templates')
                ->onDelete('cascade');

            $table->unique(['maintenance_form_template_id', 'field_key'], 'maintenance_form_fields_template_field_key_unique');
            $table->index(['maintenance_form_template_id', 'sort_order'], 'maintenance_form_fields_template_sort_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('maintenance_form_fields', function (Blueprint $table) {
            $table->dropForeign(['maintenance_form_template_id']);
        });

        Schema::dropIfExists('maintenance_form_fields');
    }
}
