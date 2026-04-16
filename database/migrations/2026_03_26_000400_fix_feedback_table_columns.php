<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixFeedbackTableColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('feedback') && Schema::hasTable('feedbacks')) {
            Schema::rename('feedbacks', 'feedback');
        }

        if (!Schema::hasTable('feedback')) {
            Schema::create('feedback', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('emp_id')->nullable();
                $table->string('subject', 255)->nullable();
                $table->text('message')->nullable();
                $table->tinyInteger('status')->default(0);
                $table->timestamps();
            });
            return;
        }

        Schema::table('feedback', function (Blueprint $table) {
            if (!Schema::hasColumn('feedback', 'emp_id')) {
                $table->unsignedInteger('emp_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('feedback', 'subject')) {
                $table->string('subject', 255)->nullable()->after('emp_id');
            }
            if (!Schema::hasColumn('feedback', 'message')) {
                $table->text('message')->nullable()->after('subject');
            }
            if (!Schema::hasColumn('feedback', 'status')) {
                $table->tinyInteger('status')->default(0)->after('message');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Intentionally left blank to avoid destructive schema changes.
    }
}
