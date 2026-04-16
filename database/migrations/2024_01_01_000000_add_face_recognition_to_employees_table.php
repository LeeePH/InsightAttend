<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFaceRecognitionToEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->text('face_descriptor')->nullable()->after('pin_code');
            $table->string('face_image')->nullable()->after('face_descriptor');
            $table->boolean('face_registered')->default(false)->after('face_image');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['face_descriptor', 'face_image', 'face_registered']);
        });
    }
}
