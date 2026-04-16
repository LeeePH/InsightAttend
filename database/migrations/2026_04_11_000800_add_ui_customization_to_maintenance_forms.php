<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUiCustomizationToMaintenanceForms extends Migration
{
    public function up()
    {
        Schema::table('maintenance_form_templates', function (Blueprint $table) {
            $table->json('ui_settings')->nullable()->after('description');
        });

        Schema::table('maintenance_form_fields', function (Blueprint $table) {
            $table->string('placeholder', 255)->nullable()->after('field_options');
            $table->text('help_text')->nullable()->after('placeholder');
            $table->string('column_class', 40)->nullable()->after('help_text');
        });
    }

    public function down()
    {
        Schema::table('maintenance_form_templates', function (Blueprint $table) {
            $table->dropColumn('ui_settings');
        });

        Schema::table('maintenance_form_fields', function (Blueprint $table) {
            $table->dropColumn(['placeholder', 'help_text', 'column_class']);
        });
    }
}
