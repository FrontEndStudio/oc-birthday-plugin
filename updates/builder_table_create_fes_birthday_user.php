<?php

namespace Fes\Birthday\updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class builder_table_create_fes_birthday_user extends Migration
{
    public function up()
    {
        Schema::create('fes_birthday_user', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->text('first_name');
            $table->text('middle_name')->nullable();
            $table->text('last_name');
            $table->date('birth_date');
            $table->integer('sort_order');
            $table->boolean('status')->default(1);
        });
    }

    public function down()
    {
        Schema::dropIfExists('fes_birthday_user');
    }
}
