<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('users_id')->unsigned();
            $table->string('first_name',16);
            $table->string('last_name',16);
            $table->string('email',128)->unique();
            $table->string('password',16);
            $table->string('avatar'); 
            $table->integer('timezone_id')->unsigned();
            $table->integer('language_id')->unsigned(); //FK 
            $table->integer('availability_id')->unsigned();
            $table->text('why_i_volunteer');
            $table->string('employee_id',16); 
            $table->string('department',16);
            $table->string('manager_name',16); 
            $table->integer('city_id')->unsigned(); // FK cities id
            $table->integer('country_id')->unsigned();//FK countries id
            $table->text('profile_text');
            $table->string('linked_in_url',255);
//            $table->enum('status', ['0', '1'])->default(1);
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('timezone_id')->references('timezone_id')->on('timezones')->onDelete('CASCADE')->onUpdate('CASCADE');
            // cross database
            $table->foreign('language_id')->references('language_id')->on('languages')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreign('availability_id')->references('availability_id')->on('availabilities')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreign('city_id')->references('city_id')->on('cities')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreign('country_id')->references('country_id')->on('countries')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
